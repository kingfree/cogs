<?php
require_once("../include/header.php");
gethead(1,"修改比赛","修改比赛");
?>
<div class='row-fluid'>
<?php
if ($_GET['action']=='edit') {
	$pRows = Contest::byId($_GET['cbid']);
	$cnt = count($pRows);
	$d=($pRows[0] ?? false);
	$pbs=explode(":",$d['contains']);
	$pbs=array_flip($pbs);
}
?>
<form method="post" action="doeditcompbase.php?action=<?=h($_GET['action'] ?? '')?>&cbid=<?=$_GET['cbid']; ?>" class='form-inline'><?=formTokenField()?>
<table class='table table-striped table-condensed table-bordered fiexd'>
  <tr>
    <td width='80px'>CBID</td>
    <td><?=$d['cbid'] ?></td>
  </tr>
  <tr>
    <td>比赛名</td>
    <td>
      <input name="cname" type="text" value="<?=h($d['cname'])?>" /></td>
  </tr>
  <tr>
    <td>关联题目</td>
    <td>
	<ul class='nav nav-pills'>
	<?php
	$pRows = Problem::choices();

	foreach ($pRows as $i => $d) {
?>
<li style='width: 200px'>
<input name="cons[<?=$d['pid']?>]" type="checkbox" id="cons[<?=$d['pid']?>]" value="<?=$d['pid']?>" <?php if (is_numeric($pbs[$d['pid']])) echo 'checked="checked"' ?> />
<label for="cons[<?=$d['pid']?>]" class='<?php
if(is_numeric($pbs[$d['pid']])) echo "label label-success";
else if(!$d['submitable']) echo "label label-important";
?>'><?=$d['pid']?>.<?=shortname($d['probname']) ?></label>
</li>
<?php
	}
?>
</ul></td>
  </tr>
<tr><td></td><td>
<button type="submit" class='btn btn-primary'>提交修改</button>
</td></tr>
</table>
</form>
</div>
<?php
	include_once("../include/footer.php");
?>
