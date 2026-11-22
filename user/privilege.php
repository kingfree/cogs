<?php
require_once("../include/header.php");
gethead(1,"修改权限","权限列表");

$uid=(int) ($_POST['uid'] ? $_POST['uid'] : $_GET['uid']);
$priv=(int) ($_POST['pri'] ? $_POST['pri'] : $_GET['pri']);
$way = $_POST['way'] ?? $_GET['way'] ?? '';
if ($way !== '') requirePost();

?>
<div class='row-fluid'>
<form method=post><?=formTokenField()?>
	<b>为用户添加权限</b>
	用户编号：<input type='number' size=10 name="uid" value="<?=$uid?>" />
    <input type=hidden name=way value="ins" />
	用户权限：<select name="pri">
<?php
foreach ($pri as $key => $val) {
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
<?php
if($way=='ins' && $uid) {
    $privileges = Privilege::granted($uid, $priv);
    $cnt = count($privileges);
    if(!$cnt) {
        Privilege::grant($uid, $priv);

        HTML("<div class='alert alert-success'>用户 $uid 已添加权限 ".array_search($priv, $pri)." ！</div>");
    }
}

if($way=='del' && $uid) {
    Privilege::revoke($uid, $priv);

    HTML("<div class='alert alert-success'>用户 $uid 已删除权限 ".array_search($priv, $pri)." ！</div>");
}
?>
<?php
	$privileges = Privilege::holders();
	
	$cnt = count($privileges);
	$totalpage=(int)(($cnt-1)/$SET['style_pagesize'])+1;
	if (!isset($_GET['page'])) 
	{
		$_GET['page']=1;
		$st=0;
	} else {
		if ($_GET['page']<1 || $_GET['page']>$totalpage)
		{
			echo "页错误！";
			$err=1;
		}
		else
		$st=(($_GET['page']-1)*$SET['style_pagesize']);
	}
?>
<table class='table table-striped table-condensed table-bordered fiexd'>
  <tr>
    <th>用户</th>
    <th>权限</th>
    <th>可用</th>
    <th>操作</th>
  </tr>
<?php 	if (!$err)
	for ($i=$st;$i<$cnt && $i<$st+$SET['style_pagesize'] ;$i++)
	{
		$d=($privileges[$i] ?? false);
?>
  <tr>
    <td><a href='../user/detail.php?uid=<?=$d['uid']?>' target='_blank'><?=gravatar::showImage($d['email']);?><?=h($d['nickname'])?></a></td>
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
<?php
分页($cnt, ($_GET['page'] ?? 1));
?>
</div>
<?php
include_once("../include/footer.php");
?>
