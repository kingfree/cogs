<?php
class Compiler
{
    private $compiledir,$gds,$odir,$info;
    public $command,$compilemessage,$runtime,$exitcode,$s_detail,$s_score,$ac,$state,$srcname,$cmds,$memory,$avgmemory,$totaltime;
    public $wrongpoint, $nowjudge, $inputtext, $difftext;
    public $noindata, $noansdata;

    public function __construct($info)
    {
        global $SETTINGS;
        $this->compiledir=$info['compiledir'];
        $this->info=$info;
        $this->s_score=0;
        $this->wrongpoint=0;
        $this->noindata = 0;
        $this->noansdata = 0;
        $this->s_detail="";
        $this->ac=0;
        $this->odir='/'.substr(dirname($_SERVER['SCRIPT_FILENAME']),1);
        $this->avgmemory=$this->totaltime=0;
    }

    public function getgds()
    {
        $p=new DataAccess();
        $sql="select grid,address,memo from grader where enabled=1 order by priority desc";
        $cnt=$p->dosql($sql);
        for ($i=0;$i<$cnt;$i++)
        {
            $s=array();
            $d=$p->rtnrlt($i);
            $this->gds=$d['address'];
            $s['action']="state";

            $this->cmds=$tmp=httpsocket($this->gds,$s);

            $this->state=array_decode($tmp);
            $this->state['grid']=$d['grid'];
            $this->state['memo']=$d['memo'];
            if ($this->state['state']=="free")
                return true;
        }
        return false;
    }

    public function getdir()
    {
        chdir($this->compiledir);
        if (!file_exists($this->info['uid']))
        {
            mkdir($this->info['uid']);
            chmod($this->info['uid'],0775);
        }
        chdir($this->info['uid']);
        $this->srcname=$this->info['pname'].'.'.langnumtostr($this->info['language']);
    }

    public function getupload()
    {
        $this->srcname.='.'.time();
        if ($_FILES['file']['size']>102400 || $_FILES['file']['size']==0)
            return false;
        move_uploaded_file($_FILES['file']['tmp_name'],$this->srcname);
        chmod($this->srcname,0664);
        return true;
    }

    public function get_rejudge_src($src)
    {
        $this->srcname=$src;
    }

    public function compile()
    {
        $s['action']="compile";
        $s['pname']=$this->info['pname'];
        $s['uid']=$this->info['uid'];
        $s['language']=$this->info['language'];
        $s['src']=$this->info['pname'].'.'.langnumtostr($this->info['language']);

        $fp=fopen($this->srcname,"r");
        $s['code']=rfile($fp);
        fclose($fp);

        if ($this->info['mode']=="test")
            unlink($this->srcname);

        chdir($this->odir);
        $this->cmds=$tmp=httpsocket($this->gds,$s);

        $this->state=array_decode($tmp);

        $this->compilemessage=$this->state['msg'];

        if ($this->state['compilesucc'])
            return 1;
        else
            return 0; 
    }

    public function run($i)
    {
        $s['action']="grade";
        $s['grade']=$i;
        $s['uid']=$this->info['uid'];
        $s['pname']=$this->info['pname'];
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
            return judgetext('T');
        } else if ($this->state['memoryout']) {
            $this->s_detail.='M';
            return judgetext('M');
        } else if ($this->state['runerr']) {
            $this->s_detail.='E';
            return judgetext('E');
        } else if ($this->state['noreport']) {
            $this->s_detail.='R';
            return judgetext('R');
        } else if ($this->state['noindata'] || $this->state['noansdata']) {
            $this->s_detail.='D';
            return judgetext('D');
        } else if ($this->state['score']==0) {
            $this->s_detail.='W';
            return judgetext('W');
        } else if ($this->state['score']!=1) {
            $this->s_detail.='P';
            return judgetext('P');
        } else if ($this->state['score']==1) {
            $this->s_detail.='A';
            $this->ac++;
            return judgetext('A');
        }
    }

    public function getscore()
    {
        $this->s_score=(int)( (float)$this->s_score / (float)$this->info['datacnt'] *100);
        return $this->s_score;
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
        $s['action']="unlock";
        $s['uid']=$this->info['uid'];
        httpsocket($this->gds,$s);
    }

    public function lock()
    {
        $s['action']="lock";
        httpsocket($this->gds,$s);
    }

    public function writedb_single()
    {
        $p=new DataAccess();

        $ac=$this->ac==$this->info['datacnt']?1:0;

        if ($this->info['rejudge']==1)
        {
            $sql="update submit set result='{$this->s_detail}' ,score='{$this->s_score}' ,memory='{$this->avgmemory}' ,accepted='{$ac}' ,runtime='{$this->totaltime}' where sid='{$this->info['sid']}'";
            $p->dosql($sql);
        }
        else
        {
            $sql1="update userinfo set submited=submited+1";
            $sql="update problem set submitcnt=submitcnt+1";
            if ($ac)
            {
                $sql.=",lastacid={$this->info['uid']}";
                $sql2="select accepted from submit where pid='{$this->info['pid']}' and uid='{$this->info['uid']}' and accepted=1";
                $first=$p->dosql($sql2)==0;
                if ($first) //第一次AC
                {
                    $sql1.=" ,grade=grade+1";
                    $sql.=" ,acceptcnt=acceptcnt+1";
                }
            }
            $sql.=" where pid={$this->info['pid']}";
            $p->dosql($sql);
            $sql1.=" where uid='{$this->info['uid']}'";
            $p->dosql($sql1);
            $sql="insert into submit(pid,uid,lang,result,score,memory,accepted,subtime,IP,runtime,srcname) values({$this->info['pid']},{$this->info['uid']},{$this->info['language']},'{$this->s_detail}',{$this->s_score},{$this->avgmemory},{$ac},".time().",'{$_SERVER['REMOTE_ADDR']}',{$this->totaltime},'{$this->srcname}')";
            $p->dosql($sql);
        }
        if (mt_rand(0,10)==1)
        {
            echo "<p>已完成数据库校验。</p>";
            require("gen_rank.php");
        }
    }

    public function writedb_comp($csid)
    {
        $p=new DataAccess();
        $sql="update compscore set score={$this->s_score} ,result='{$this->s_detail}' where csid={$csid}";
        $p->dosql($sql);
    }

    private function fcomp($f1,$f2)
    {
        do
        {
            $d1=fgetc($f1);
            while ($d1==" " || $d1=="\n" || $d1=="\r")
                $d1=fgetc($f1);
            if ($d1===false) break;

            $d2=fgetc($f2);
            while ($d2==" " || $d2=="\n" || $d2=="\r")
                $d2=fgetc($f2);
            if ($d2===false) return false;
            if ($d1!=$d2) return false;
        } while (true);
        return true;
    }

};
?>
