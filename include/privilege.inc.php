<?php

$pri['普通用户']=0;
$pri['管理用户']=1;
$pri['超级用户']=2;
$pri['查看代码']=21;
$pri['修改题目']=11;
$pri['查看题目']=12;
$pri['查看数据']=13;
$pri['测试题目']=14;
$pri['修改页面']=15;
$pri['查看页面']=16;
$pri['查看比赛']=31;
$pri['修改比赛']=32;
$pri['拥有比赛']=33;
$pri['发表评论']=51;
$pri['管理评论']=52;
$pri['上传数据']=33;
$pri['查看数据']=34;
$pri['修改用户']=41;
$pri['查看用户']=45;
$pri['生成等级']=43;
$pri['分组管理']=44;
$pri['分类管理']=42;
$pri['管理评测']=61;
$pri['修改权限']=72;
$pri['参数设置']=99;

function 有此权限($qx, $uid=0) {
    global $pri, $cfg, $_SESSION;
    if(!$qx) return true;
    if($qx == '普通用户' && $_SESSION['ID']) return true;
    if($qx == '可以管理')
        for($i=1;$i<100;$i++)
            if($_SESSION['pri'.$i]) return true;
    if($_SESSION['pri'.$pri[$qx]]) return true;
    if($_SESSION['pri'.$pri['超级用户']]) return true;
    if($pri[$qx]<60 && $_SESSION['pri'.$pri['管理用户']]) return true;
    return false;
}


?>
