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

require_once __DIR__."/compiler.php";
require_once __DIR__."/checker.php";
require_once __DIR__."/testcase.php";

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
