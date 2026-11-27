<?php
// 由 contest/editcomptime.php?action=add 打开排期表单；选定已有题单后可单独指定本场时间和参赛分组。
require_once(__DIR__.'/../include/header.php');
gethead(0, '修改比赛', ''); // 在加载管理表单前核对访问权限。
$contests = Contest::choices(); // 排期只能选择已经创建的题单。
$groups = UserGroup::all(); // 分组列表另加无需成员身份的公开组 0。

if (($_GET['action'] ?? '') === 'edit') {
    $round = ContestRound::withTitle((int)($_GET['ctid'] ?? 0))[0] ?? null; // 编辑继承原场的排期和公开设置。
    if (!$round) throw new DomainException('场次不存在。', 404);
} else {
    $round = ['ctid'=>'', 'cbid'=>(int)($_GET['cbid'] ?? 0), 'group'=>(int)($groups[0]['gid'] ?? 0), 'showscore'=>1, 'intro'=>'',
        'starttime'=>strtotime('today 19:00'), 'endtime'=>strtotime('today 22:00')]; // 新场次沿用站点时区的晚间三小时。
}
gethead(1, '', '修改比赛场次');
?>
<div class='container'>
<form method="post" action="doeditcomptime.php?action=<?=h($_GET['action'] ?? '')?>&ctid=<?=h($_GET['ctid'] ?? '')?>"><?=formTokenField()?>
<table class='table table-striped table-condensed table-bordered fiexd'>
  <tr>
    <td width='80px'>CTID</td>
    <td><?php echo $round['ctid'] ?></td>
  </tr>
  <tr>
    <td>关联比赛</td>
    <td><select name="cbid" id="cbid">
        <?php
		foreach ($contests as $contest) {
?>
        <option value="<?php echo $contest['cbid'] ?>" <?php if($contest['cbid']==$round['cbid']) echo 'selected="selected"' ?>>[<?=h($contest['cname'])?>]</option>
        <?php }?>
      </select>
      (比赛开始后请勿再改) </td>
  </tr>
  <tr>
    <td>开放分组</td>
    <td><select name="group" id="group" >
        <option value="0" <?=$round['group'] == 0 ? 'selected' : ''?>>[所有用户]</option>
        <?php
		foreach ($groups as $group) {
?>
        <option value="<?php echo $group['gid'] ?>" <?php if($group['gid']==$round['group']) echo 'selected="selected"' ?>>[<?=h($group['gname'])?>]</option>
        <?php }?>
      </select>
      (比赛开始后请勿再改) </td>
  </tr>
  <tr>
    <td>开始时间</td>
    <td>
    <input name="st_y" type="text" class="span1" id="st_y" value="<?php echo date('Y',$round['starttime']) ?>" size="4" />
      年
        <input name="st_m" type="text" class="span1" id="st_m" value="<?php echo date('m',$round['starttime']) ?>" size="2" />
        月
        <input name="st_d" type="text" class="span1" id="st_d" value="<?php echo date('d',$round['starttime']) ?>" size="2" />
        日
        <input name="st_h" type="text" class="span1" id="st_h" value="<?php echo date('H',$round['starttime']) ?>" size="2" />
        时
        <input name="st_i" type="text" class="span1" id="st_i" value="<?php echo date('i',$round['starttime']) ?>" size="2" />
        分
        <input name="st_s" type="text" class="span1" id="st_s" value="<?php echo date('s',$round['starttime']) ?>" size="2" />
      秒
    </td>
  </tr>
  <tr>
    <td>结束时间</td>
    <td><input name="et_y" type="text" class="span1" id="et_y" value="<?php echo date('Y',$round['endtime']) ?>" size="4" />
年
  <input name="et_m" type="text" class="span1" id="et_m" value="<?php echo date('m',$round['endtime']) ?>" size="2" />
月
<input name="et_d" type="text" class="span1" id="et_d" value="<?php echo date('d',$round['endtime']) ?>" size="2" />
日
<input name="et_h" type="text" class="span1" id="et_h" value="<?php echo date('H',$round['endtime']) ?>" size="2" />
时
<input name="et_i" type="text" class="span1" id="et_i" value="<?php echo date('i',$round['endtime']) ?>" size="2" />
分
<input name="et_s" type="text" class="span1" id="et_s" value="<?php echo date('s',$round['endtime']) ?>" size="2" />
秒</td>
  </tr>
  <tr>
    <td>公布成绩</td>
    <td>
      <input name="showscore" type="checkbox" id="showscore" value="1" <?php if ($round['showscore']) echo 'checked="checked"'?> /> （比赛未开始时不显示成绩列表）</td>
  </tr>
  <tr>
    <td>介绍</td>
    <td><textarea name="intro" class='textarea'><?=h($round['intro'])?></textarea></td>
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
	include_once(__DIR__.'/../include/footer.php');
?>
