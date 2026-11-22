<?php
// 准备单组测试数据、运行程序并记录结果。
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
        $program = './'.$query['pname'];
        $execute = ['sh', '-c', 'ulimit -v "$1" && exec "$2"', 'cogs', (string)$memorylimit, $program];
        $streams = [0 => ['pipe','r'], 1 => ['file','/dev/null','w'], 2 => ['file','/dev/null','w']];
        $process = proc_open($execute, $streams, $pipes);
        if (!is_resource($process)) throw new RuntimeException('无法启动评测程序');
        fwrite($pipes[0], "1");
        fclose($pipes[0]);
        $start = hrtime(true);
        $tmp['memory'] = 0;
        setrunning(1);
        try {
            do {
                $status = proc_get_status($process);
                $tmp['rtime'] = (hrtime(true) - $start) / 1000000;
                if (!$status['running']) break;
                $usage = @file_get_contents('/proc/'.$status['pid'].'/status');
                if ($usage && preg_match('/^VmHWM:\s*(\d+)/m', $usage, $match)) {
                    $tmp['memory'] = max($tmp['memory'], (int)$match[1]);
                }
                if ($tmp['rtime'] > $query['timelimit']) {
                    proc_terminate($process, 9);
                    $tmp['timeout'] = true;
                    break;
                }
                usleep(1000);
            } while (true);
        } finally {
            setrunning(-1);
        }
        $tmp['exitcode'] = $status['exitcode'];
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
    if (isset($process) && is_resource($process)) proc_close($process);

    destroylink($i,$query);

    if (file_exists("{$query['pname']}.out"))
        rename("{$query['pname']}.out","{$query['pname']}{$i}.out");

    echo array_encode($tmp);
}
