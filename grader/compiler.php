<?php
// 将提交的源文件编译为评测程序。
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
    $compilecommand= "timeout -k 1 20 ".$compilecommand;
    $handle = popen($compilecommand, 'r');
    $tmp['msg']=rfile($handle);
    $status = pclose($handle);
    if ($status === 0 && (file_exists($query['pname']) || $query['language'] == 3))
        $tmp['compilesucc']=1;
    else
        $tmp['compilesucc']=0;
    echo array_encode($tmp);
}
