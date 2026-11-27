<?php
// 由 contest/editcomptime.php?action=add 打开排期表单；选定已有题单后可单独指定本场时间和参赛分组。
require_once("../include/header.php");
gethead(1,"修改比赛","修改比赛场次");
$contests = Contest::choices();
$groups = UserGroup::all();
?>
<div class='container'>
<?php
if ($_GET['action']=='edit')
{
	$rounds = ContestRound::withTitle($_GET['ctid']);
	$cnt = count($rounds);
	$d=($rounds[0] ?? false);
}
else
{
    $now = time();
	$d['starttime']=mktime(19, 0, 0, date('m',$now), date('d',$now), date('Y',$now));
    $d['endtime']=mktime(22, 0, 0, date('m',$now), date('d',$now), date('Y',$now));
	$d['cbid']=$_GET['cbid'];
	$d['group']=0;
    $d['showscore']=1;
}
?>
<form method="post" action="doeditcomptime.php?action=<?=h($_GET['action'] ?? '')?>&ctid=<?=h($_GET['ctid'] ?? '')?>"><?=formTokenField()?>
<table class='table table-striped table-condensed table-bordered fiexd'>
  <tr>
    <td width='80px'>CTID</td>
    <td><?php echo $d['ctid'] ?></td>
  </tr>
  <tr>
    <td>关联比赛</td>
    <td><select name="cbid" id="cbid">
        <?php
		$qRows = $contests;
		$c = count($qRows);
		foreach ($qRows as $j => $e) {
?>
        <option value="<?php echo $e['cbid'] ?>" <?php if($e['cbid']==$d['cbid']) echo 'selected="selected"' ?>>[<?=h($e['cname'])?>]</option>
        <?php }?>
      </select>
      (比赛开始后请勿再改) </td>
  </tr>
  <tr>
    <td>开放分组</td>
    <td><select name="group" id="group" >
        <?php
		$qRows = $groups;
		$c = count($qRows);
		foreach ($qRows as $j => $e) {
?>
        <option value="<?php echo $e['gid'] ?>" <?php if($e['gid']==$d['group']) echo 'selected="selected"' ?>>[<?=h($e['gname'])?>]</option>
        <?php }?>
      </select>
      (比赛开始后请勿再改) </td>
  </tr>
  <tr>
    <td>开始时间</td>
    <td>
    <input name="st_y" type="text" class="span1" id="st_y" value="<?php echo date('Y',$d['starttime']) ?>" size="4" />
      年
        <input name="st_m" type="text" class="span1" id="st_m" value="<?php echo date('m',$d['starttime']) ?>" size="2" />
        月
        <input name="st_d" type="text" class="span1" id="st_d" value="<?php echo date('d',$d['starttime']) ?>" size="2" />
        日
        <input name="st_h" type="text" class="span1" id="st_h" value="<?php echo date('H',$d['starttime']) ?>" size="2" />
        时
        <input name="st_i" type="text" class="span1" id="st_i" value="<?php echo date('i',$d['starttime']) ?>" size="2" />
        分
        <input name="st_s" type="text" class="span1" id="st_s" value="<?php echo date('s',$d['starttime']) ?>" size="2" />
      秒
    </td>
  </tr>
  <tr>
    <td>结束时间</td>
    <td><input name="et_y" type="text" class="span1" id="et_y" value="<?php echo date('Y',$d['endtime']) ?>" size="4" />
年
  <input name="et_m" type="text" class="span1" id="et_m" value="<?php echo date('m',$d['endtime']) ?>" size="2" />
月
<input name="et_d" type="text" class="span1" id="et_d" value="<?php echo date('d',$d['endtime']) ?>" size="2" />
日
<input name="et_h" type="text" class="span1" id="et_h" value="<?php echo date('H',$d['endtime']) ?>" size="2" />
时
<input name="et_i" type="text" class="span1" id="et_i" value="<?php echo date('i',$d['endtime']) ?>" size="2" />
分
<input name="et_s" type="text" class="span1" id="et_s" value="<?php echo date('s',$d['endtime']) ?>" size="2" />
秒</td>
  </tr>
  <tr>
    <td>公布成绩</td>
    <td>
      <input name="showscore" type="checkbox" id="showscore" value="1" <?php if ($d['showscore']) echo 'checked="checked"'?> /> （比赛未开始时不显示成绩列表）</td>
  </tr>
  <tr>
    <td>介绍</td>
    <td><textarea name="intro" class='textarea'><?=h($d['intro'])?></textarea></td>
  </tr>
  <tr>
    <td></td>
    <td>
<button type="submit" class='btn btn-primary'>提交修改</button>
    </td>
  </tr>
</table>
</form>
</div>

<?php
	include_once("../include/footer.php");
?>
