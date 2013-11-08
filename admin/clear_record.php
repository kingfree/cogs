<?php
require_once("../include/header.php");
gethead(1,"管理用户","清除记录");
?>

<pre>
<?
global $p;
$p=new DataAccess();

function check($f,$uid)
{
    global $p;
    $sql="select * from submit where uid={$uid} and srcname='{$f}'";
    $cnt=$p->dosql($sql);
    if ($cnt==0)
        return 1;
    else
        return 0;
}

function sdir($dir,$dname)
{
    $dh=opendir($dir);
    while ($file=readdir($dh)) 
    {
flush();
        if($file!="." && $file!="..") 
        {
            $fullpath=$dir."/".$file;
            if(!is_dir($fullpath)) 
            {
                if ($dname!="" && check($file,$dname))
                {
                    echo $fullpath;
                    echo "\n";
                    unlink($fullpath);
                }
            } 
            else 
            {
                sdir($fullpath,$file);
            }
        }
    }
    closedir($dh);
}
sdir($SET['dir_source'],"");
?>
</pre>
