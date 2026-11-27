<?php
/**
 * 站点侧的一次评测，负责租用评测机、发送源码和测试点、累计结果，再由入口决定写入哪张表。
 * submit/run.php 处理练习，contest/compile.php 处理交卷，两者复用同一套请求顺序。
 * 在入口组好 $info 后：$job = new Judging($info); $claimed = $job->acquire(0);
 * 只有 $claimed 为真才可调用 prepareSource() 和 compile()；每次 run($i) 后调用一次 getresult()。
 */
class Judging
{
    private $compiledir, $gds, $info, $job, $sourceDir;
    public $command,$compilemessage,$runtime,$exitcode,$s_detail,$s_score,$ac,$state,$filename,$srcname,$cmds,$memory,$avgmemory,$totaltime;
    public $wrongpoint, $nowjudge, $inputtext, $difftext;
    public $noindata, $noansdata;

    /** 为本次评测生成独立租约编号，初始化整题分数与资源统计。 */
    public function __construct($info)
    {
        global $SET;
        // 后续锁定、编译、评分和释放都携带同一个任务标识。
        $this->job = bin2hex(random_bytes(16));
        $this->compiledir=$info['compiledir'];
        $this->info=$info;
        $this->s_score=0;
        $this->wrongpoint=0;
        $this->noindata = 0;
        $this->noansdata = 0;
        $this->s_detail="";
        $this->ac=0;
        $this->avgmemory=$this->totaltime=0;
    }

    /**
     * 尝试取得一台评测机的独占租约。
     * @param int|string $judger 指定机位编号，传 0 时按优先级尝试启用的机器。
     * @return bool 远端接受本次租约时为 true；没有可用机器由入口提示稍后再试。
     */
    public function acquire($judger)
    {
        if($judger)
            $judges = Judge::enabledById($judger);
        else
            $judges = Judge::enabled();
        $cnt = count($judges);
        for ($i=0;$i<$cnt;$i++)
        {
            $s=array();
            $d=($judges[$i] ?? false);
            $this->gds=$d['address'];
            $s['action']="state";

            $this->cmds=$tmp=httpsocket($this->gds,$s);

            $this->state=array_decode($tmp);
            $this->state['grid']=$d['grid'];
            $this->state['memo']=$d['memo'];
            if (($this->state['state'] ?? '') === 'free') {
                // 看到空闲后仍需等待锁定成功，另一请求可能也选中了这台机器。
                $claim = array_decode(httpsocket($this->gds, ['action'=>'lock', 'job'=>$this->job]));
                if (($claim['state'] ?? '') === 'successful') {
                    // 页面提前结束也要尝试释放已取得的租约。
                    register_shutdown_function([$this, 'unlock']);
                    return true;
                }
            }
        }
        return false;
    }

    /** 定位当前选手的源码目录，按数据库题目文件名和语言确定评测使用的基础文件名。 */
    public function prepareSource()
    {
        $this->sourceDir = rtrim($this->compiledir, '/').'/'.(int)$this->info['uid'];
        if (!is_dir($this->sourceDir) && !mkdir($this->sourceDir, 0750, true) && !is_dir($this->sourceDir)) {
            throw new RuntimeException('不能创建提交目录。');
        }
        $this->filename = $this->info['pname'];
        $this->srcname = $this->filename.'.'.langnumtostr($this->info['language']);
    }

    /** 保存一次新的普通提交，加随机后缀保留提交历史；上传失败或超过 100 KiB 返回 false。 */
    public function upload(array $file): bool
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || $file['size'] < 1 || $file['size'] > 102400) return false;
        // 评测机使用固定题目文件名，本地另外保存带后缀的历史版本。
        $this->srcname .= '.'.bin2hex(random_bytes(10));
        return move_uploaded_file($file['tmp_name'], $this->sourceDir.'/'.$this->srcname);
    }

    /** 重判已经落盘的提交时，复用数据库记录的文件名，在已确定的选手目录下查找。 */
    public function useSource($file)
    {
        $this->srcname = basename($file);
    }

    /** 发送租约、题目语言和源码，失败详情保存在 compilemessage；成功后才可逐点运行。 */
    public function compile()
    {
        $s['action']="compile";
        $s['job']=$this->job;
        $s['pname']=$this->info['pname'];
        $s['uid']=$this->info['uid'];
        $s['language']=$this->info['language'];
        $s['src']=$this->info['pname'].'.'.langnumtostr($this->info['language']);

        // 数据库保存本地文件名；发给编译器的文件名由题目名和语言确定。
        $file = $this->sourceDir.'/'.$this->srcname;
        if (!is_file($file) || is_link($file)) { $this->compilemessage = '源文件不存在。'; return false; }
        $s['code'] = file_get_contents($file);
        // 试跑源码读入请求后即可清理，普通提交保留文件以支持查看和重判。
        if (($this->info['mode'] ?? '') === 'test') unlink($file);

        $this->cmds=$tmp=httpsocket($this->gds,$s);

        $this->state=array_decode($tmp);

        $this->compilemessage = $this->state['msg'] ?? '评测机未返回编译结果。';

        if (!empty($this->state['compilesucc']))
            return 1;
        else
            return 0;
    }

    /** 派发从 1 开始的测试点号，更新当前点状态及整题累计时长、内存。 */
    public function run($i)
    {
        $s['action']="grade";
        $s['job']=$this->job;
        $s['grade']=$i;
        $s['uid']=$this->info['uid'];
        $s['pname']=$this->info['pname'];
        $s['language']=$this->info['language'];
        $s['timelimit']=$this->info['timelimit'];
        $s['memorylimit']=$this->info['memorylimit'];
        $s['plugin']=$this->info['plugin'];

        $this->cmds=$tmp=httpsocket($this->gds,$s);
        $this->state=array_decode($tmp);
        $this->nowjudge = $i;

        $this->exitcode=$this->state['exitcode'];
        $this->runtime=$this->state['rtime'];
        $this->memory=$this->state['memory'];

        // 这里先累加所有点的内存，整题结束才除以点数。
        $this->avgmemory+=$this->memory;
        $this->totaltime+=$this->runtime;

        $this->noindata = (int)$this->state['noindata'];
        $this->noansdata = (int)$this->state['noansdata'];
    }

    /** 消费当前点一次，累积分数并追加一个判定字符，返回页面显示的判定标签。 */
    public function getresult()
    {
        $this->s_score+=$this->state['score'];
        // 保存首个零分点的输入和差异，后面的错点不覆盖提示。
        if($this->state['score'] == 0 && $this->wrongpoint == 0) {
            $this->wrongpoint = $this->nowjudge;
            $this->inputtext = $this->state['input'];
            $this->difftext = $this->state['diff'];
        }
        if ($this->state['timeout']) {
            $this->s_detail.='T';
            return 评测信息a('T');
        } else if ($this->state['memoryout']) {
            $this->s_detail.='M';
            return 评测信息a('M');
        } else if ($this->state['runerr']) {
            $this->s_detail.='E';
            return 评测信息a('E');
        } else if ($this->state['noreport']) {
            $this->s_detail.='R';
            return 评测信息a('R');
        } else if ($this->state['noindata'] || $this->state['noansdata']) {
            $this->s_detail.='D';
            return 评测信息a('D');
        } else if ($this->state['score']==0) {
            $this->s_detail.='W';
            return 评测信息a('W');
        } else if ($this->state['score']!=1) {
            $this->s_detail.='P';
            return 评测信息a('P');
        } else if ($this->state['score']==1) {
            $this->s_detail.='A';
            $this->ac++;
            return 评测信息a('A');
        }
    }

    /** 所有点结束后调用一次，将累计得分比例换成整题百分制整数并写回 s_score。 */
    public function getscore()
    {
        $this->s_score=(int)( (float)$this->s_score / (float)$this->info['datacnt'] *100);
        return (int)$this->s_score;
    }

    /** 所有点结束后调用一次，将累计内存换成每点平均 KiB 并写回 avgmemory。 */
    public function getmemory()
    {
        $this->avgmemory=(int) ( $this->avgmemory / $this->info['datacnt'] );
        return $this->avgmemory;
    }

    /** 返回所有已运行测试点的累计毫秒数。 */
    public function gettotaltime()
    {
        return $this->totaltime;
    }

    /** 供进度表显示单点对整题的整数分贡献，不改变累计得分。 */
    public function getthisscore()
    {
        return (int)( (float)$this->state['score'] / (float)$this->info['datacnt'] *100);
    }

    /** 归还这个请求取得的租约；显式释放后，PHP 结束时的清理回调可安全再次调用。 */
    public function unlock()
    {
        $s['uid']=$this->info['uid'];
        if (!$this->job) return;
        $s['job']=$this->job;
        // 先清空本地标识，避免主动释放和结束回调发送两次解锁。
        $this->job=null;
        $s['action']="unlock";
        httpsocket($this->gds,$s);
    }

    /** 保存普通练习或重判结果，只有全部测试点满分才记为通过。 */
    public function writedb_single()
    {

        $ac=$this->ac==$this->info['datacnt']?1:0;

        Submission::saveResult($this->info, $this->s_detail, $this->s_score, $this->avgmemory, $ac, (int)round($this->totaltime), $this->srcname);
    }

    /** 更新现有交卷的成绩；csid 来自管理员选择的比赛提交。 */
    public function writedb_comp($csid)
    {
        ContestEntry::saveScore($this->s_score, $this->s_detail, $this->avgmemory, $this->totaltime, $csid);
    }

}
