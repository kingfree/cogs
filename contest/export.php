<?php
require_once("../include/header.php");
gethead(0,"查看比赛","");
chdir($SET['dir_competition']);
$ctid=(int)$_GET['ctid'];
$dir="COGS_{$ctid}";
$src="tmp.zip";
exec("rm $src");
exec("rm -R $dir");
$md="mkdir {$dir}";
exec($md);

$p=new DataAccess();
$sql="select compscore.uid,userinfo.realname,userinfo.nickname,problem.filename from compscore,userinfo,problem where userinfo.uid=compscore.uid and compscore.pid=problem.pid and compscore.ctid={$_GET[ctid]} order by uid asc";
$cnt=$p->dosql($sql);
$tu=0;
for ($i=0;$i<$cnt;$i++) {
    $d=$p->rtnrlt($i);
    //if($d[uid]==$tu) continue;
    $tu=$d[uid];
    exec("mkdir -p {$dir}/{$tu}.{$d['realname']}_/");
    $mv="cp {$ctid}/{$tu}/{$d['filename']}.* {$dir}/{$tu}.{$d['realname']}_/";
    exec($mv);
}

exec("rm $src");
$zip="zip -r {$src} {$dir}";
exec($zip);
header("Content-type: application/zip");
header("Content-Disposition: attachment; filename=\"{$dir}.zip\"");
if(file_exists($dir)) exec("rm -R $dir");
@readfile($src);
?>

