<?php
// 网页端的一次判题，例 $job = new Judging($info)，随后 acquire、compile、run、unlock。
class Judging
{
    private $compiledir, $gds, $info, $job, $sourceDir;
    public $command,$compilemessage,$runtime,$exitcode,$s_detail,$s_score,$ac,$state,$filename,$srcname,$cmds,$memory,$avgmemory,$totaltime;
    public $wrongpoint, $nowjudge, $inputtext, $difftext;
    public $noindata, $noansdata;

    public function __construct($info)
    {
        global $SET;
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
                $claim = array_decode(httpsocket($this->gds, ['action'=>'lock', 'job'=>$this->job]));
                if (($claim['state'] ?? '') === 'successful') {
                    register_shutdown_function([$this, 'unlock']);
                    return true;
                }
            }
        }
        return false;
    }

    public function prepareSource()
    {
        $this->sourceDir = rtrim($this->compiledir, '/').'/'.(int)$this->info['uid'];
        if (!is_dir($this->sourceDir) && !mkdir($this->sourceDir, 0750, true) && !is_dir($this->sourceDir)) {
            throw new RuntimeException('不能创建提交目录。');
        }
        $this->filename = $this->info['pname'];
        $this->srcname = $this->filename.'.'.langnumtostr($this->info['language']);
    }

    public function upload(array $file): bool
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || $file['size'] < 1 || $file['size'] > 102400) return false;
        $this->srcname .= '.'.bin2hex(random_bytes(10));
        return move_uploaded_file($file['tmp_name'], $this->sourceDir.'/'.$this->srcname);
    }

    public function useSource($file)
    {
        $this->srcname = basename($file);
    }

    public function compile()
    {
        $s['action']="compile";
        $s['job']=$this->job;
        $s['pname']=$this->info['pname'];
        $s['uid']=$this->info['uid'];
        $s['language']=$this->info['language'];
        $s['src']=$this->info['pname'].'.'.langnumtostr($this->info['language']);

        $file = $this->sourceDir.'/'.$this->srcname;
        if (!is_file($file) || is_link($file)) { $this->compilemessage = '源文件不存在。'; return false; }
        $s['code'] = file_get_contents($file);
        if (($this->info['mode'] ?? '') === 'test') unlink($file);

        $this->cmds=$tmp=httpsocket($this->gds,$s);

        $this->state=array_decode($tmp);

        $this->compilemessage = $this->state['msg'] ?? '评测机未返回编译结果。';

        if (!empty($this->state['compilesucc']))
            return 1;
        else
            return 0;
    }

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

        $this->avgmemory+=$this->memory;
        $this->totaltime+=$this->runtime;

        $this->noindata = (int)$this->state['noindata'];
        $this->noansdata = (int)$this->state['noansdata'];
    }

    public function getresult()
    {
        $this->s_score+=$this->state['score'];
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

    public function getscore()
    {
        $this->s_score=(int)( (float)$this->s_score / (float)$this->info['datacnt'] *100);
        return (int)$this->s_score;
    }

    public function getmemory()
    {
        $this->avgmemory=(int) ( $this->avgmemory / $this->info['datacnt'] );
        return $this->avgmemory;
    }

    public function gettotaltime()
    {
        return $this->totaltime;
    }

    public function getthisscore()
    {
        return (int)( (float)$this->state['score'] / (float)$this->info['datacnt'] *100);
    }

    public function unlock()
    {
        $s['uid']=$this->info['uid'];
        if (!$this->job) return;
        $s['job']=$this->job;
        $this->job=null;
        $s['action']="unlock";
        httpsocket($this->gds,$s);
    }

    public function writedb_single()
    {

        $ac=$this->ac==$this->info['datacnt']?1:0;

        Submission::saveResult($this->info, $this->s_detail, $this->s_score, $this->avgmemory, $ac, (int)round($this->totaltime), $this->srcname);
    }

    public function writedb_comp($csid)
    {
        ContestEntry::saveScore($this->s_score, $this->s_detail, $this->avgmemory, $this->totaltime, $csid);
    }

}
