<?php
require_once("../include/header.php");
gethead(1,"","用户信息", $_GET['uid']);
$p=new DataAccess();
$q=new DataAccess();
?>
<div class='container-fluid'>
<div class='span4'>
<?php
$sql="select userinfo.*,groups.gname,groups.gid from userinfo,groups where userinfo.uid={$_GET['uid']} and userinfo.gbelong=groups.gid";
$cnt=$p->dosql($sql);
if(!$cnt) 异常("无此用户！");
$d=$p->rtnrlt(0);
?>
<table class='table table-striped table-condensed table-bordered fiexd'>
<tr>
<th width='60px'>用户编号</th>
<td><?=$d['uid'] ?></td>
</tr>
<tr>
<th>头像</th>
<td><?=gravatar::showImage($d['email'], 200);?></td>
</tr>
<tr>
<th>用户名称</th>
<td><?=$d['usr'] ?></td>
</tr>
<tr>
<th>用户昵称</th>
<td><?=$d['nickname'] ?></td>
</tr>
<tr>
<th>E-mail</th>
<td><?=$d['email'] ?></td>
</tr>
<tr>
<th>阅读权限</th>
<td><?=$d['readforce'] ?></td>
</tr>
<tr>
<th>所属分组</th>
<td><a href="../user/index.php?gid=<?=$d['gid'] ?>"><?=$d['gname'] ?></a></td>
</tr>
<tr>
<th>等级</th>
<td><?=$d['grade'] ?></td>
</tr>
<tr>
<th>注册时间</th>
<td><?=date('Y-m-d H:i:s', $d['regtime']) ?></td>
</tr>
<tr>
<th>个人介绍</th>
<td><?=nl2br(sp2n(htmlspecialchars($d['memo']))) ?></td>
</tr>
  <?php if(有此权限('查看用户') || $_SESSION['ID']==$d['uid']) { ?>
  <tr>
    <th>真实姓名</th>
    <td><?=$d['realname'] ?></td>
  </tr>
  <tr>
    <th>用户权限</th>
    <td>
  <?
    $sql="select privilege.* from privilege where uid={$d['uid']} order by pri asc";
	$cnt=$q->dosql($sql);
    if(!$cnt) echo array_search(0,$pri) . " ";
	for ($i=0;$i<$cnt;$i++) {
		$e=$q->rtnrlt($i);
        echo array_search($e['pri'],$pri) . " ";
    }
  ?>
    </td>
  </tr>
  <tr>
    <th>登录IP</th>
    <td><?=$d['lastip'] ?></td>
  </tr>
  <?php } ?>
</table>

<?php
$sql="select compbase.cbid,compbase.cname,compscore.subtime,comptime.ctid from compscore,comptime,compbase where compscore.uid={$_GET['uid']} and comptime.ctid=compscore.ctid and comptime.cbid=compbase.cbid order by comptime.endtime desc";
$cnt=$p->dosql($sql);
if ($cnt) {
?>
<table class='table table-striped table-condensed table-bordered fiexd'>
  <tr>
    <th>比赛名</th>
    <th>参加时间</th>
  </tr>
<?php
	$last = $j =0;
	for ($i=0;$i<$cnt;$i++) {
		$d=$p->rtnrlt($i);
		if ($last==$d['cbid']) continue;
		$last=$d['cbid'];
        $j++;
?>
    <tr>
    <td><b><?=$d['cname']?></b></td>
    <td><a href="../contest/comp.php?ctid=<?=$d['ctid']?>&uid=<?=$_GET['uid']?>" target="_blank"><?echo date("Y-m-d",$d['subtime'])?></a></td>
    </tr>
<?php
	}
?>
</table>
<?php
}
?>
</div>
<div class='span8'>
<a href="../submit/index.php?uid=<?=$_GET['uid']?>" target="_blank" class='btn'>查看全部提交记录</a>
<?php
$accnt=0;
$sql="select problem.pid,problem.probname,submit.accepted,submit.lang,submit.sid from submit,problem where submit.uid={$_GET['uid']} and submit.pid=problem.pid order by problem.pid asc, submit.score desc ";
$cnt=$p->dosql($sql);
if ($cnt) {
?>
<table class='table table-striped table-condensed table-bordered fiexd'>
<?php
$ppp=array();$j=0;
for ($i=0;$i<$cnt;$i++) {
    $d=$p->rtnrlt($i);
    if($ppp[$d['pid']]) continue;
    if($d['accepted']) $accnt++;
    $ppp[$d['pid']] = true;
    if($j % 4 == 0) echo "<tr>";
    $j++;
?>
<td>
<a href='../submit/code.php?id=<?=$d['sid']?>' target='_blank'>
<?=$STR[lng][$d['lang']]?>
<i class='icon-<?=($d['accepted']?"ok":"remove")?>'></i>
</a>
<a href="../problem/problem.php?pid=<?=$d['pid'] ?>" target="_blank">
<?=$d['probname'] ?>
</a>
</td>
<?php
	}
if($j%4) for($i=$j%4; $i<4; $i++) echo "<td>";
?>
</table>
<?php
}
?>
<p class='alert'>
通过了<b><?=$accnt ?></b>道题，一共提交了<b><?=$cnt ?></b>次，通过率为<b><?php printf("%.2lf",$cnt==0?0:$accnt / $cnt * 100) ?>%</b>。
</p>
</div>
</div>
<?php
include_once("../include/footer.php");
?>
