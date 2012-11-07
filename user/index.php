<?php
require_once("../include/header.php");
gethead(1,"","用户列表");
?>
<?php
    $p=new DataAccess();
    $q=new DataAccess();
    $r=new DataAccess();
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
<div class='btn-group pull-left'>
<a href="grouplist.php" class='btn btn-success pull-left'><i class='icon-th-large icon-white'></i>用户分组列表</a>
<button class='btn btn-success dropdown-toggle' data-toggle='dropdown'><span class='caret'></span></button>
<ul id="group_now" class='dropdown-menu span4'>
<li>当前分组</li>
<li><a href=""><span class='label'><?=$d['gname'] ?></span><?=nl2br(sp2n(htmlspecialchars($d['memo']))) ?></a></li>
<?php if ($d['parent']) {
$sql2="select * from groups where gid={$d['parent']}";
$q->dosql($sql2);
$e=$q->rtnrlt(0);
?>
<li class='divider'></li>
<li>上级分组</li>
<li><a href="index.php?gid=<?=$e['gid'] ?>"><?=$e['gname'] ?></a></li>
<?php } ?>
<?php 
if ($subgroup!=array()) {
?>
<li class='divider'></li>
<li>下级分组</li>
<li><?php
foreach($subgroup as $value) {
$sql2="select * from groups where gid={$value}";
$q->dosql($sql2);
$e=$q->rtnrlt(0);
?>
<a href="index.php?gid=<?=$e['gid'] ?>"><?=$e['gname'] ?></a>
<?php 
}
?></li>
<?php } ?>
</ul>
</div>
<?php
    if ($_GET['key']!="")
        $sql.=" and (nickname like '%{$_GET[key]}%' or uid ='{$_GET[key]}' or usr like '%{$_GET[key]}%' or realname like '%{$_GET[key]}%')";
if($_GET['rank'])
$sql.=" order by {$_GET['rank']} {$_GET['order']}";
else
$sql.=" order by grade desc";

$cnt=$p->dosql($sql);
$st=检测页面($cnt, $_GET['page']);
?>
<form method="get" action="" class='form-search center'>
  <input name="gid" type="hidden" value="<?=$_GET['gid'] ?>" />
  <input name="order" type="hidden" value="<?=$_GET['order']=='asc'?'desc':'asc'?>" />
<div class='input-append pull-right'>
<input name="key" type="text" class='search-query input-medium' value='<?=$_GET['key']?>' placeholder='搜索用户'/>
<button type='submit' class='btn'><i class='icon icon-search'></i></button>
</div>
<div class='btn-group'>排序方法：
  <button name="rank" type="submit" value='uid' class='btn'>UID</button>
  <button name="rank" type="submit" value='nickname' class='btn'>昵称</button>
  <?php if(有此权限('查看用户')) { ?>
  <button name="rank" type="submit" value='realname' class='btn'>姓名</button>
  <? } ?>
  <button name="rank" type="submit" value='readforce' class='btn'>阅读</button>
  <button name="rank" type="submit" value='gbelong' class='btn'>分组</button>
  <button name="rank" type="submit" value='accepted' class='btn'>通过</button>
  <button name="rank" type="submit" value='accepted/submited' class='btn'>通过率</button>
  <button name="rank" type="submit" value='grade' class='btn'>等级</button>
</div>
</form>
<script language=javascript>
function okdel(name) {
    if(confirm("你确定要删除 "+name+" 的所有信息吗？\n删除之后将不可恢复！"))
        return true;
    return false;
}
</script>
<div class='row-fluid'>
<table id="userlist" class='table table-striped table-condensed table-bordered fiexd'>
<thead>
  <tr>
    <th style="width: 3ex;"></th>
    <th style="width: 3ex;">UID</th>
    <th>昵称</th>
    <? if(有此权限('查看用户')) { ?>
    <th class='admin' style="width: 4em;">姓名</th>
    <th style="width: 6ex;">阅读</th>
    <? } ?>
    <th>权限</th>
    <th>分组</th>
    <th style="width: 4ex;">通过</th>
    <th style="width: 6ex;">通过率</th>
    <th style="width: 6ex;">等级</th>
    <? if(有此权限('查看用户')) { ?><th class='admin' style="width: 8em;">IP</th><? } ?>
    <? if(有此权限('修改用户')) { ?><th class='admin' style="width: 4em;">操作</th><? } ?>
  </tr>
</thead>
<?php
    if (!$err) for ($i=$st;$i<$cnt && $i<$st+$SET['style_pagesize'] ;$i++) {
        $d=$p->rtnrlt($i);
?>
  <tr>
    <td><i><?=$i+1?></i></td>
    <td><?=$d['uid'] ?></td>
    <td><a href="detail.php?uid=<?=$d['uid'] ?>" title="<?=(sp2n(htmlspecialchars($d['memo'])))?>"><?=gravatar::showImage($d['email']);?><?=$d['nickname'] ?></a></td>
    <? if(有此权限('查看用户')) { ?>
    <td>
        <?=$d['realname'] ?></td>
    <td>
        <a href="dodelimg.php?email=<?=md5($d['email'])?>" title="重建头像缓存"><i class="icon icon-picture"></i></a>
    <?=$d['readforce'] ?>
    </td>
    <? } ?>
    <td><?
    $sql="select privilege.* from privilege where uid={$d['uid']} order by pri asc";
	$cnt1=$r->dosql($sql);
	for($i1=0;$i1<$cnt1;$i1++) {
		$e=$r->rtnrlt($i1);
        echo array_search($e['pri'],$pri) . " ";
    } ?></td>
    <td ><?="<a href='?gid={$d['gbelong']}'>{$d['gname']}</a>"; ?></td>
    <td ><?=$d['accepted'] ?></td>
    <td ><?=@round($d['accepted']/$d['submited']*100,1); ?>%</td>
    <td ><?=$d['grade'] ?></td>
    <? if(有此权限('查看用户')) { ?>
    <td>
    <a href="loginlog.php?uid=<?=$d['uid'] ?>"><?=$d['lastip'] ?></a>
    </td><? } ?>
    <? if(有此权限('修改用户')) { ?><td>
    <a href="edituser.php?uid=<?=$d['uid'] ?>" title="修改用户信息"><i class="icon icon-wrench"></i></a>
    <? if(有此权限('修改权限')) { ?>
    <a href='privilege.php?way=edit&uid=<?=$d['uid']?>' title="赋予权限"><i class="icon icon-book"></i></a>
    <? } ?>
    <a href="doedituser.php?uid=<?=$d['uid'] ?>&action=del" onclick="return okdel('<?=$d['nickname']?>')" title="删除用户账号"><i class="icon icon-remove"></i></a>
    </td><? } ?>
  </tr>
<?php
    }
?>
</table>
<? 分页($cnt, $_GET['page'], '?key='.$_GET['key'].'&sc'.$_GET['sc'].'&rank='.$_GET['rank'].'&order='.$_GET['order'].'&caid='.$_GET['caid'].'&'); ?>
</div>
<?php
include_once("../include/footer.php");
?>
