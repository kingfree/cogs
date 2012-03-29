<?php
require_once("../../include/stdhead.php");
gethead(1,"admin","修改比赛场次");
?>

<a href="../settings.php?settings=comp">比赛基本管理</a>
<?php
	$q=new DataAccess();
if ($_GET[action]=='edit')
{
	$p=new DataAccess();
	$sql="select comptime.*,compbase.cname from comptime,compbase where comptime.cbid=compbase.cbid and comptime.ctid={$_GET[ctid]}";
	$cnt=$p->dosql($sql);
	$d=$p->rtnrlt(0);
}
else
{
	$d[starttime]=$d[endtime]=time();
	$d[cbid]=$_GET[cbid];
	$d['group']=0;
}
?>
<form id="form1" name="form1" method="post" action="doeditcomptime.php?action=<?php echo $_GET[action] ?>&ctid=<?php echo $_GET[ctid]; ?>">
<table width="100%" border="1"  bordercolor=#000000  cellspacing=0 cellpadding=4>
  <tr>
    <td>CTID</td>
    <td><?php echo $d[ctid] ?></td>
  </tr>
  <tr>
    <td>关联比赛</td>
    <td><select name="cbid" id="cbid" class="InputBox">
        <?php
		$sql="select cname,cbid from compbase";
		$c=$q->dosql($sql);
		for ($j=0;$j<$c;$j++)
		{
			$e=$q->rtnrlt($j);
?>
        <option value="<?php echo $e[cbid] ?>" <?php if($e[cbid]==$d[cbid]) echo 'selected="selected"' ?>>[<?php echo $e[cname] ?>]</option>
        <?php }?>
      </select>
      (比赛开始后请勿再改) </td>
  </tr>
  <tr>
    <td>开放分组</td>
    <td><select name="group" id="group" class="InputBox">
        <?php
		$sql="select * from groups order by gname";
		$c=$q->dosql($sql);
		for ($j=0;$j<$c;$j++)
		{
			$e=$q->rtnrlt($j);
?>
        <option value="<?php echo $e['gid'] ?>" <?php if($e['gid']==$d['group']) echo 'selected="selected"' ?>>[<?php echo $e['gname'] ?>]</option>
        <?php }?>
      </select>
      (比赛开始后请勿再改) </td>
  </tr>
  <tr>
    <td>开始时间</td>
    <td><input name="st_y" type="text" class="InputBox" id="st_y" value="<?php echo date('Y',$d[starttime]) ?>" size="4" />
      年
        <input name="st_m" type="text" class="InputBox" id="st_m" value="<?php echo date('m',$d[starttime]) ?>" size="2" />
        月
        <input name="st_d" type="text" class="InputBox" id="st_d" value="<?php echo date('d',$d[starttime]) ?>" size="2" />
        日
        <input name="st_h" type="text" class="InputBox" id="st_h" value="<?php echo date('H',$d[starttime]) ?>" size="2" />
        时
        <input name="st_i" type="text" class="InputBox" id="st_i" value="<?php echo date('i',$d[starttime]) ?>" size="2" />
        分
        <input name="st_s" type="text" class="InputBox" id="st_s" value="<?php echo date('s',$d[starttime]) ?>" size="2" />
      秒</td>
  </tr>
  <tr>
    <td>结束时间</td>
    <td><input name="et_y" type="text" class="InputBox" id="et_y" value="<?php echo date('Y',$d[endtime]) ?>" size="4" />
年
  <input name="et_m" type="text" class="InputBox" id="et_m" value="<?php echo date('m',$d[endtime]) ?>" size="2" />
月
<input name="et_d" type="text" class="InputBox" id="et_d" value="<?php echo date('d',$d[endtime]) ?>" size="2" />
日
<input name="et_h" type="text" class="InputBox" id="et_h" value="<?php echo date('H',$d[endtime]) ?>" size="2" />
时
<input name="et_i" type="text" class="InputBox" id="et_i" value="<?php echo date('i',$d[endtime]) ?>" size="2" />
分
<input name="et_s" type="text" class="InputBox" id="et_s" value="<?php echo date('s',$d[endtime]) ?>" size="2" />
秒</td>
  </tr>
  <tr>
    <td>公布成绩</td>
    <td>
      <input name="showscore" type="checkbox" id="showscore" value="1" <?php if ($d[showscore]) echo 'checked="checked"'?> /> （已经加入当比赛未开始时不显示成绩列表的功能）</td>
  </tr>
  <tr>
    <td>介绍</td>
    <td><textarea name="intro" cols="80" rows="10" class="TextArea" id="intro"><?php echo $d[intro] ?></textarea></td>
  </tr>
</table>
<p>
  <input type="submit" name="Submit" value="提交修改"  class="Button" />
</form>

<?php
	include_once("../../include/stdtail.php");
?>
