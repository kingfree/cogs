<return><?php

global $compiledir,$datadir;

$compiledir=$cfg['cpldir'];
$datadir=$cfg['datdir'];

function write($str)
{
    $fp=fopen("mode.php","w");
    fputs($fp,$str,strlen($str));
    fclose($fp);
}

function getdir($query)
{
    global $compiledir,$datadir;
    chdir($compiledir);
    if (!file_exists($query['uid']))
    {
        mkdir($query['uid']);
        chmod($query['uid'],0775);
    }
    chdir($query['uid']);
}

function getcompilecommand($query)
{
    switch ($query['language'])
    {
        case 0:
            return "fpc {$query['src']} -So -XS -v0 -O1 -o\"{$query['pname']}\" 2>&1";
        case 1:
            return "gcc {$query['src']} -lm -w -O2 -static -o {$query['pname']} 2>&1";
        case 2:
            return "g++ {$query['src']} -lm -w -O2 -static -o {$query['pname']} 2>&1";
    }
}

function compile($query)
{
    global $compiledir,$datadir;
    getdir($query);

    $compilecommand=getcompilecommand($query);
    //file_put_contents("../tmp.txt", $compilecommand, FILE_APPEND);

    $query['code']=filter($query['code'],$query['language']);

    wfile($query['code'],$query['src']);

    if (file_exists($query['pname']))
        unlink($query['pname']);

    $compilecommand="timelimit 20 ".$compilecommand;

    $handle = popen($compilecommand, 'r');
    $tmp['msg']=rfile($handle);
    pclose($handle);

    if (file_exists($query['pname']))
        $tmp['compilesucc']=1;
    else
        $tmp['compilesucc']=0;

    echo array_encode($tmp);
}

function createlink($i,$query)
{
    global $compiledir,$datadir;
    if (file_exists("{$query['pname']}.in")) unlink("{$query['pname']}.in");
    $crlink="ln -s {$datadir}/{$query['pname']}/{$query['pname']}{$i}.in {$query['pname']}.in";
    exec($crlink);
}

function destroylink($i,$query)
{
    if (file_exists("{$query['pname']}.in")) unlink("{$query['pname']}.in");
}

function getmicrotime()
{
    list($usec, $sec) = explode(" ",microtime()); 
    return $sec.substr($usec,1);
}

function grade($query)
{
    global $compiledir,$datadir;
    $i=$query['grade'];
    $memorylimit=$query['memorylimit']*1024;
    getdir($query);
    createlink($i,$query);
    $tmp['noindata'] = $tmp['noansdata'] = 0;
    $tmp['input'] = file_get_contents("{$datadir}/{$query['pname']}/{$query['pname']}{$i}.in");

    $tmp['timeout']=false;
    $tmp['runerr']=false;
    $tmp['noreport']=false; 

    $execute="(ulimit -v {$memorylimit}; ./{$query['pname']})";

    $descriptorspec = array(0 => array("pipe", "r"),1 => array("pipe", "w"),2 => array("pipe", "w"));
    $process = proc_open($execute, $descriptorspec, $pipes);
    $status=proc_get_status($process);

    $pid=$status['pid']+2;
    exec("ps v -C {$query['pname']}",$mem);
    sscanf($mem[1],"%ld%s%s%s%s%ld%ld",&$ar[1],&$ar[2],&$ar[3],&$ar[4],&$ar[5],&$ar[6],&$ar[7]);
    fwrite($pipes[0],"1");
    fclose($pipes[0]);

    $tmp['memory']=$memory=$ar[7];  

    $time_start = getmicrotime();
    setrunning(1);
    for (;;)
    {
        $status=proc_get_status($process);
        if (!$status['running']) break;
        $time_now = getmicrotime();
        $tmp['rtime']=($time_now-$time_start)*950;
        if ($tmp['rtime']>$query['timelimit']) //ʱ,ɱ
        {
            proc_terminate($process);
            //posix_kill($pid,1);
            exec("killall {$query['pname']}");
            $tmp['timeout']=true;
            break;
        }
    }
    setrunning(-1);

    $tmp['exitcode']=$status['exitcode'];
    //137 MLE

    if ($tmp['exitcode']==137) 
        $tmp['memoryout']=true;

    if ($tmp['exitcode']!=0) 
        $tmp['runerr']=true;

    if (!file_exists("{$query['pname']}.out"))
        $tmp['noreport']=true;
    else {
        $fin=fopen("{$datadir}/{$query['pname']}/{$query['pname']}{$i}.in","r");
        $fans=fopen("{$datadir}/{$query['pname']}/{$query['pname']}{$i}.ans","r");
        $fout=fopen("{$query['pname']}.out","r");

        if ($query['plugin']!=0) {
            $tmp['score']=standard_compare($fans,$fout);
        } else if ($query['plugin']==0) {
            require("{$datadir}/{$query['pname']}/plugin.php");
            $tmp['score']=plugin_compare($fin,$fout,$fans);
        }
        fclose($fin);
        fclose($fout);
        fclose($fans);
    }
    $fians="{$datadir}/{$query['pname']}/{$query['pname']}{$i}.ans";
    $fiout="{$query['pname']}.out";
    $fistr="diff -b -U0 {$fiout} {$fians}";
    exec($fistr . " > tmp", $diff);
    if(file_exists("{$datadir}/{$query['pname']}/{$query['pname']}{$i}.in") == 0)
        $tmp['noindata'] = 1;
    if(file_exists("{$datadir}/{$query['pname']}/{$query['pname']}{$i}.ans") == 0)
        $tmp['noansdata'] = 1;
    if($tmp['timeout']==true || $tmp['runerr']==true || $tmp['noindata'] + $tmp['noansdata']) $tmp['score'] = 0;
    if($tmp['score'] < 1.0)
        $tmp['diff'] = file_get_contents("tmp");
    proc_close($process);

    destroylink($i,$query);

    if (file_exists("{$query['pname']}.out"))
        rename("{$query['pname']}.out","{$query['pname']}{$i}.out");

    echo array_encode($tmp);
}

$now=read();

switch ($query['action'])
{
    case "state":
        $tmp['state']=$now;
        $tmp['name']=$cfg['Name'];
        $tmp['ver']=$cfg['Ver'];
        $tmp['cnt']=read_cnt();
        $run=getrunning();
        if ($run<0)
        {
            running(0,'abs');
            $run=0;
        }
        if ($run==0)
        {
            write("free");
            $tmp['state']="free";
        }
        echo array_encode($tmp);
        break;
    case "lock":
        write("locked");
        $tmp['state']="successful";
        echo array_encode($tmp);
        break;
    case "unlock":
        write("free");
        write_cnt();
        chdir($compiledir);
        deldir($query['uid']);
        $tmp['state']="successful";
        echo array_encode($tmp);
        break;
    case "start":
        write("free");
        $tmp['state']="successful";
        echo array_encode($tmp);
        break;
    case "shutdown":
        write("closed");
        $tmp['state']="successful";
        echo array_encode($tmp);
        break;
    case "compile":
        compile($query);
        break;
    case "grade":
        grade($query);
        break;
}
?>
