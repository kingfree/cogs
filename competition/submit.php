<?php
require_once("../include/stdhead.php");

if(time() > $_POST['endtime'] && !$_SESSION['admin']) {
header("location: ../refresh.php?id=16");
exit;
}

$fname=$_POST[filename];
switch ($_POST[lang]) {
    case 'pas':
        $fname.=".pas";
        $nlang=0;
        break;
    case 'c':
        $fname.=".c";
        $nlang=1;
        break;
    case 'cpp':
        $fname.=".cpp";
        $nlang=2;
        break;
}

chdir($SET['dir_competition']);

if (!file_exists($_POST[ctid])) {
    mkdir($_POST[ctid]);
    chmod($_POST[ctid],0775);
}

chdir($_POST[ctid]);

if (!file_exists($_SESSION[ID])) {
    mkdir($_SESSION[ID]);
    chmod($_SESSION[ID],0775);
}
chdir($_SESSION[ID]);

if (file_exists("{$_POST[filename]}.pas")) unlink("{$_POST[filename]}.pas");
if (file_exists("{$_POST[filename]}.c"  )) unlink("{$_POST[filename]}.c");
if (file_exists("{$_POST[filename]}.cpp")) unlink("{$_POST[filename]}.cpp");
move_uploaded_file($_FILES['file']['tmp_name'],$fname);chmod($fname,0775);

$p=new DataAccess();
$sql="select csid from compscore where uid={$_SESSION[ID]} and pid={$_POST[pid]} and ctid={$_POST[ctid]}";
$cnt=$p->dosql($sql);
if ($cnt) {
    $sql="update compscore set subtime=".time().",lang={$nlang} where ctid={$_POST[ctid]} and uid={$_SESSION[ID]} and pid={$_POST[pid]}";
    $p->dosql($sql);
} else {
    $sql="insert into compscore(ctid,uid,pid,subtime,lang) values({$_POST[ctid]},{$_SESSION[ID]},{$_POST[pid]},".time().",{$nlang})";
    $p->dosql($sql);
}

header("location: ../refresh.php?id=16");
?>
