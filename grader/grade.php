<return><?php

global $compiledir,$datadir;

$compiledir=$cfg['cpldir'];
$datadir=$cfg['datdir'];

function getdir($query)
{
    global $compiledir,$datadir;
    chdir($compiledir);
    if (!file_exists($query['uid'])) {
        mkdir($query['uid']);
        chmod($query['uid'],0775);
    }
    chdir($query['uid']);
}

function getcompilecommand($query)
{
    switch ($query['language']) {
        case 0:
            return "fpc {$query['src']} -So -XS -v0 -O1 -o\"{$query['pname']}\" 2>&1";
        case 1:
            return "gcc {$query['src']} -lm -w -O2 -static -o {$query['pname']} 2>&1";
        case 2:
            return "g++ {$query['src']} -lm -w -O2 -static -o {$query['pname']} 2>&1";
        case 3:
            return "unzip -o {$query['src']} -d output/ 2>&1";
    }
}

function compile($query)
{
    global $compiledir,$datadir;
    getdir($query);
    $compilecommand=getcompilecommand($query);
    $query['code']=filter($query['code'],$query['language']);
    wfile($query['code'],$query['src']);
    if (file_exists($query['pname']))
        unlink($query['pname']);
    $compilecommand="timelimit 20 ".$compilecommand;
    $handle = popen($compilecommand, 'r');
    $tmp['msg']=rfile($handle);
    pclose($handle);
    if (file_exists($query['pname']) || $query['language'] == 3)
        $tmp['compilesucc']=1;
    else
        $tmp['compilesucc']=0;
    echo array_encode($tmp);
}

function createlink($i,$query)
{
    global $compiledir,$datadir;
    if (file_exists("{$query['pname']}.in")) unlink("{$query['pname']}.in");
    $crlink="cp -u {$datadir}/{$query['pname']}/{$query['pname']}{$i}.in {$query['pname']}.in";
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

function plugin($pname,$inn,$outn,$ansn) {
    global $compiledir,$datadir;
    $spj="{$datadir}/{$pname}/spj";
    $cena="{$datadir}/{$pname}/cena";
    if(!file_exists("spj") && !file_exists("cena")) {
        if(file_exists("$spj.cc"))
            exec("g++ -O2 -lm $spj.cc -o spj");
        else if(file_exists("$spj.cpp"))
            exec("g++ -O2 -lm $spj.cpp -o spj");
        else if(file_exists("$spj.c"))
            exec("gcc -lm $spj.c -o spj");
        else if(file_exists("$spj.pas"))
            exec("fpc $spj.pas -ospj && cp {$datadir}/{$pname}/spj .");
        else if(file_exists("$spj.pp"))
            exec("fpc $spj.pp -ospj && cp {$datadir}/{$pname}/spj .");
        else if(file_exists("$cena.cc"))
            exec("g++ -O2 -lm $cena.cc -o cena");
        else if(file_exists("$cena.cpp"))
            exec("g++ -O2 -lm $cena.cpp -o cena");
        else if(file_exists("$cena.c"))
            exec("gcc -lm $cena.c -o cena");
        else if(file_exists("$cena.pas"))
            exec("fpc $cena.pas -ocena && cp {$datadir}/{$pname}/cena .");
        else if(file_exists("$cena.pp"))
            exec("fpc $cena.pp -ocena && cp {$datadir}/{$pname}/cena .");
        else if(file_exists("{$datadir}/{$pname}/plugin.php")) {
            $fin=fopen($inn,"r");
            $fans=fopen($ansn,"r");
            $fout=fopen($outn,"r");
            require("{$datadir}/{$pname}/plugin.php");
            return plugin_compare($fin,$fout,$fans);
        }
    }
    if(file_exists("spj")) {
        $judge="./spj $inn $outn $ansn";
        exec($judge, $res, $score);
    } else if(file_exists("cena")) {
        $judge="./cena 100 $ansn";
        exec($judge);
        $fsc=fopen("score.log", "r");
        fscanf($fsc, "%d", $score);
        fclose($fsc);
    }
    if($score > 100 || $score < 0) $score = 0;
    return $score / 100.0;
}

function dataset($pname, $i) {
    global $compiledir,$datadir;
    $dir="{$datadir}/{$pname}/";
    $a=array();
    $a['input'] = "{$dir}{$pname}{$i}.in";
    $a['answer'] = "{$dir}{$pname}{$i}.ans";
    return $a;
}

function grade($query)
{
    global $compiledir,$datadir;
    $i=$query['grade'];
    $memorylimit=$query['memorylimit']*1024;
    getdir($query);
    createlink($i,$query);
    $data=dataset($query['pname'], $i);
    $tmp['noindata'] = $tmp['noansdata'] = 0;
    $tmp['input'] = file_get_contents($data['input']);
    $tmp['timeout']=false;
    $tmp['runerr']=false;
    $tmp['noreport']=false; 

    if($query['language'] == 3) {
        $cpcmd="mv output/{$query['pname']}{$i}.out {$query['pname']}.out";
        //if(file_exists("{$query['pname']}.out"))
        //    exec("rm {$query['pname']}.out");
        exec($cpcmd);
        exec("ls output/{$query['pname']}*.out > tmpp");
    } else {
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
            if ($tmp['rtime']>$query['timelimit'])
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
        if ($tmp['exitcode']==137) 
            $tmp['memoryout']=true;
        if ($tmp['exitcode']!=0) 
            $tmp['runerr']=true;
    }
    if (!file_exists("{$query['pname']}.out"))
        $tmp['noreport']=true;
    else {
        $inn=$data['input'];
        $ansn=$data['answer'];
        $outn="{$query['pname']}.out";
        if ($query['plugin'] == 0 || $query['plugin'] == 3) {
            $tmp['score']=plugin($query['pname'],$inn,$outn,$ansn);
        } else {
            $fin=fopen($inn,"r");
            $fans=fopen($ansn,"r");
            $fout=fopen($outn,"r");
            $tmp['score']=standard_compare($fans,$fout);
            fclose($fin);
            fclose($fout);
            fclose($fans);
        }
    }
    if(!file_exists($data['input'])) $tmp['noindata'] = 1;
    if(!file_exists($data['answer'])) $tmp['noansdata'] = 1;
    if($tmp['timeout']==true || $tmp['runerr']==true || $tmp['noindata'] + $tmp['noansdata']) $tmp['score'] = 0;
    if($tmp['score'] < 1.0) {
        $fians=$data['answer'];
        $fiout="{$query['pname']}.out";
        $fistr="diff -b -U0 {$fiout} {$fians}";
        exec($fistr . " > tmp", $diff);
        $tmp['diff'] = file_get_contents("tmp");
    }
    proc_close($process);

    destroylink($i,$query);

    if (file_exists("{$query['pname']}.out"))
        rename("{$query['pname']}.out","{$query['pname']}{$i}.out");

    echo array_encode($tmp);
}

$now=read();
$stt=read();

switch ($query['action']) {
    case "state":
        $tmp['state']=$now;
        $tmp['name']=$cfg['Name'];
        $tmp['ver']=$cfg['Ver'];
        $tmp['cnt']=read_cnt();
        $run=getrunning();
        if ($run<0) {
            running(0,'abs');
            $run=0;
        } else if ($run==0) {
            write("free");
            $tmp['state']="free";
        }
        echo array_encode($tmp);
        break;
    case "lock":
        do {
            write("locked");
            $stt=read();
        } while($stt != "locked");
        $tmp['state']="successful";
        echo array_encode($tmp);
        break;
    case "unlock":
        do {
            write("free");
            write_cnt();
            $run=getrunning();
        } while($run > 0);
        chdir($compiledir);
        deldir($query['uid']);
        $tmp['state']="successful";
        echo array_encode($tmp);
        break;
    case "start":
        do {
            write("free");
            $stt=read();
        } while($stt != "free");
        $tmp['state']="successful";
        echo array_encode($tmp);
        break;
    case "shutdown":
        do {
            write("closed");
            $stt=read();
        } while($stt != "closed");
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
