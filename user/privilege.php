<?php
require_once("../include/header.php");
gethead(1,"","权限列表");
$p=new DataAccess();

$uid=(int) ($_POST['uid'] ? $_POST['uid'] : $_GET['uid']);
$priv=(int) ($_POST['pri'] ? $_POST['pri'] : $_GET['pri']);
$way=$_POST['way'] ? $_POST['way'] : $_GET['way'];

?>
<div class='container'>
<form method=post>
	<b>为用户添加权限</b>
	用户编号：<input type=text size=10 name="uid" value="<?=$uid?>" />
    <input type=hidden name=way value="ins" />
	用户权限：<select name="pri">
<?php
while(list($key, $val)=each($pri)) {
	if (isset($priv) && ($priv == $val)) {
		echo '<option value="'.$val.'" selected>'.$key.'</option>';
	} else {
		echo '<option value="'.$val.'">'.$key.'</option>';
	}
}
?></select>
<input type='hidden' name='do' value='do' />
<button type=submit class='btn btn-primary' >添加权限</button>
</form>
<?
if($way=='ins' && $uid) {
    $sql="select def from `privilege` where uid=$uid and pri=$priv limit 1";
    $cnt=$p->dosql($sql);
    if(!$cnt) {
        $sql="insert into `privilege`(uid, pri, def) values('$uid','$priv','1')";
        $cnt=$p->dosql($sql);
        HTML("<div class='alert alert-success'>用户 $uid 已添加权限 ".array_search($priv, $pri)." ！</div>");
    }
}

if($way=='del' && $uid) {
    $sql="delete from `privilege` where uid=$uid and pri=$priv";
    $cnt=$p->dosql($sql);
    HTML("<div class='alert alert-success'>用户 $uid 已删除权限 ".array_search($priv, $pri)." ！</div>");
}
?>
<?php
	$sql="select privilege.*,userinfo.email,userinfo.nickname from privilege,userinfo where userinfo.uid=privilege.uid order by uid,pri asc";
	
	$cnt=$p->dosql($sql);
	$totalpage=(int)(($cnt-1)/$SET['style_pagesize'])+1;
	if (!isset($_GET[page])) 
	{
		$_GET[page]=1;
		$st=0;
	} else {
		if ($_GET[page]<1 || $_GET[page]>$totalpage)
		{
			echo "页错误！";
			$err=1;
		}
		else
		$st=(($_GET[page]-1)*$SET['style_pagesize']);
	}
?>
<table class='table table-striped table-condensed table-bordered fiexd'>
  <tr>
    <th>用户</th>
    <th>权限</th>
    <th>可用</th>
    <th>操作</th>
  </tr>
<?	if (!$err)
	for ($i=$st;$i<$cnt && $i<$st+$SET['style_pagesize'] ;$i++)
	{
		$d=$p->rtnrlt($i);
?>
  <tr>
    <td><a href='../user/detail.php?uid=<?=$d['uid']?>' target='_blank'><?=gravatar::showImage($d['email']);?><?=$d['nickname']?></a></td>
    <td><?php echo array_search($d['pri'],$pri) ?></td>
    <td><?php if ($d['def']) echo "是"; else echo "否"; ?></td>
    <td>
    <a href='privilege.php?way=edit&uid=<?=$d['uid']?>'>编辑</a>
    <a href='privilege.php?way=del&uid=<?=$d['uid']?>&pri=<?=$d['pri']?>'>删除</a>
    </td>
<?php
	}
?>
</table>
<?
分页($cnt, $_GET['page']);
?>
</div>
<?php
include_once("../include/footer.php");
?>
