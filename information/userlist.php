<?php
require_once("../include/stdhead.php");
gethead(1,"","用户列表");
?>

<script type="text/JavaScript">
<!--
function MM_jumpMenu(targ,selObj,restore){ //v3.0
  eval(targ+".location='?key=<?=$_GET['key'] ?>&rank=<?=$_GET['rank'] ?>&page="+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}
//-->
</script>
<?php
    $p=new DataAccess();
    $q=new DataAccess();
    $sql="select userinfo.*,groups.gname from userinfo,groups where groups.gid=userinfo.gbelong ";

    if ($_GET['gid']=="")
        $_GET['gid']=0;
    $sql1="select * from groups where gid={$_GET['gid']}";
    $cnt=$p->dosql($sql1);
    $d=$p->rtnrlt(0);
    
    $subgroup=$LIB->getsubgroup($p,$_GET['gid']);
    
    $sql.=" and ( userinfo.gbelong={$_GET['gid']} ";
    
    foreach($subgroup as $value) {
        $sql.=" or userinfo.gbelong={$value}";
    }
    $sql.=") ";

?>
<table id="group_now">
  <tr>
    <th width="80px">当前分组</th>
    <td>[<?=$d['gname'] ?>] <?=nl2br(sp2n(htmlspecialchars($d['memo']))) ?></td>
  </tr>
<?php if ($d['parent']) {
    $sql2="select * from groups where gid={$d['parent']}";
    $q->dosql($sql2);
    $e=$q->rtnrlt(0);
?>
  <tr>
    <th>上级分组</th>
    <td>[<a href="userlist.php?gid=<?=$e['gid'] ?>"><?=$e['gname'] ?></a>]</td>
  </tr>
<?php } ?>
<?php 
if ($subgroup!=array()) {
?>
  <tr>
    <th>下级分组</th>
    <td><?php
    foreach($subgroup as $value) {
        $sql2="select * from groups where gid={$value}";
        $q->dosql($sql2);
        $e=$q->rtnrlt(0);
?>
        [<a href="userlist.php?gid=<?=$e['gid'] ?>"><?=$e['gname'] ?></a>]
<?php 
    }
?></td>
  </tr>
<?php } ?>
</table>
<?php
    if ($_GET['key']!="")
        $sql.=" and (nickname like '%{$_GET[key]}%' or uid ='{$_GET[key]}' or usr like '%{$_GET[key]}%' or realname like '%{$_GET[key]}%')";
    if($_GET['rank']=="按通过数量排序")
        $sql.=" order by accepted desc";
    else if($_GET['rank']=="按通过率排序")
        $sql.=" order by accepted/submited desc";
    else if($_GET['rank']=="按等级排序")
        $sql.=" order by grade desc";
    else if($_GET['rank']=="按昵称排序")
        $sql.=" order by nickname asc";
    else if($_GET['rank']=="按姓名排序")
        $sql.=" order by realname asc";
    else if($_GET['rank']=="按权限排序")
        $sql.=" order by admin desc";
    else if($_GET['rank']=="按阅读权限排序")
        $sql.=" order by readforce desc";
    else if($_GET['rank']=="按分组排序")
        $sql.=" order by gbelong asc";
    else
        $sql.=" order by uid asc";

$cnt=$p->dosql($sql);
$totalpage=(int)(($cnt-1)/$SET['style_pagesize'])+1;
if(!$_GET['page']) {
    $_GET['page']=1;
    $st=0;
} else {
    if ($_GET[page]<1 || $_GET[page]>$totalpage)
        异常("页面错误！");
    else
        $st=(($_GET[page]-1)*$SET['style_pagesize']);
}
?>
<form id="search_user" name="form1" method="get" action="">
  搜索用户
  <input name="key" type="text" id="key" value="<?=$_GET['key'] ?>" />
  <input name="sc" type="submit" id="sc" value="搜索"  class="LinkButton" />
  <input name="caid" type="hidden" id="caid" value="<?=$_GET['caid'] ?>" />
</form>
<form id="rank" action="" method="get" name="rank">
  <input name="rank" type="submit" id="rank_us" value="按昵称排序" />
  <?php if ($_SESSION['admin']>0) { ?>
  <input name="rank" type="submit" id="rank_rn" class="admin" value="按姓名排序" />
  <? } ?>
  <input name="rank" type="submit" id="rank_ad" value="按权限排序" />
  <input name="rank" type="submit" id="rank_rd" value="按阅读权限排序" />
  <input name="rank" type="submit" id="rank_gr" value="按分组排序" />
  <input name="rank" type="submit" id="rank_ac" value="按通过数量排序" />
  <input name="rank" type="submit" id="rank_lv" value="按通过率排序" />
  <input name="rank" type="submit" id="rank_gd" value="按等级排序" />
</form>
<? 分页($cnt, $_GET['page'], '?key='.$_GET['key'].'&sc'.$_GET['sc'].'&rank='.$_GET['rank'].'&caid='.$_GET['caid'].'&'); ?>
<script language=javascript>
function okdel(name) {
    if(confirm("你确定要删除 "+name+" 的所有信息吗？\n删除之后将不可恢复！"))
        return true;
    return false;
}
</script>
<table id="userlist">
  <tr>
    <th>名次</th>
    <th>UID</th>
    <th>昵称</th>
    <? if($_SESSION['admin']>0) { ?><th class=admin>姓名</th><? } ?>
    <th>权限</th>
    <th>阅读</th>
    <th>分组</th>
    <th>通过</th>
    <th>通过率</th>
    <th>等级</th>
    <? if($_SESSION['admin']>0) { ?><th class=admin>IP</th><? } ?>
    <? if($_SESSION['admin']>0) { ?><th class=admin>操作</th><? } ?>
  </tr>
<?php
    if (!$err) for ($i=$st;$i<$cnt && $i<$st+$SET['style_pagesize'] ;$i++) {
        $d=$p->rtnrlt($i);
?>
  <tr>
    <th><?=$i+1; ?></th>
    <td align=center><?=$d['uid'] ?></td>
    <td><a href="../user/detail.php?uid=<?=$d['uid'] ?>" target="_blank"><?=gravatar::showImage($d['email']);?><?=$d['nickname'] ?></a></td>
    <? if($_SESSION['admin']>0) { ?><td class=admin align=center><?=$d['realname'] ?></td><? } ?>
    <td align=center><?=$STR[adminn][$d['admin']] ?></td>
    <td align=center><?=$d['readforce'] ?></td>
    <td align=center><?="<a href='?gid={$d[gbelong]}'>{$d['gname']}</a>"; ?></td>
    <td align=center><?=$d['accepted'] ?></td>
    <td align=center><?=@round($d['accepted']/$d['submited']*100,2); ?>%</td>
    <td align=center><?=$d['grade'] ?></td>
    <? if($_SESSION['admin']>0) { ?><td class=admin align=right><?=$d['lastip'] ?></a></td><? } ?>
    <? if($_SESSION['admin']>0) { ?><td class=admin align=center>
    <a href="../admin/user/edituser.php?uid=<?=$d['uid'] ?>">修改</a>
    <a href="../admin/user/doedituser.php?uid=<?=$d['uid'] ?>&action=del" onclick="return okdel('<?=$d['nickname']?>')">删除</a>
    </td><? } ?>
  </tr>
<?php
    }
?>
</table>
<?php
    include_once("../include/stdtail.php");
?>
