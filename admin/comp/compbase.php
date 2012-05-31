<script type="text/JavaScript">
function MM_jumpMenu(targ,selObj,restore){ //v3.0
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}
</script>
<a href="comp/editcompbase.php?action=add" class="btn btn-info pull-left">添加新比赛</a>
<?php
	$p=new DataAccess();
	$q=new DataAccess();
	$sql="select compbase.*,userinfo.nickname from compbase,userinfo where userinfo.uid=compbase.ouid order by compbase.cbid desc";
	
$cnt=$p->dosql($sql);
$st=检测页面($cnt, $_GET['page']);
?>
<table class='table table-striped table-condensed table-bordered fiexd'>
  <tr>
    <th width="80px">CBID</th>
    <th width="140px">比赛名</th>
    <th>包含题目</th>
    <th>关联场次</th>
    <th width="80px">组织者</th>
    <th width="60px">操作</th>
  </tr>
<?
for ($i=$st;$i<$cnt && $i<$st+$SET['style_pagesize'] ;$i++) {
		$d=$p->rtnrlt($i);
?>
  <tr>
    <td><?php echo $d['cbid'] ?></td>
    <td><?php echo $d['cname'] ?></td>
    <td>
        <select name="menu1" onchange="MM_jumpMenu('parent',this,0)" class="InputBox">
<?php
		$c=0;
		if ($d['contains']!="")
		{
			$pbs=explode(":",$d['contains']);
			foreach($pbs as $k=>$v)
			{
				$c++;
				$sql="select probname from problem where pid={$v}";
				$q->dosql($sql);
				$e=$q->rtnrlt(0);
		?>
			<option value="../problem/problem.php?pid=<?php echo $v ?>">[<?php echo $e[probname] ?>]</option>
<?php		}
	   }?>
<option selected="selected">[<?php echo $c ?>个题目]</option>	
</select> </td>
    <td>
        <select name="menu2" onchange="MM_jumpMenu('parent',this,0)" class="InputBox">
		<?php
		$sql="select starttime,ctid from comptime where cbid={$d[cbid]} order by starttime asc";
		$c=$q->dosql($sql);
		for ($j=0;$j<$c;$j++)
		{
			$e=$q->rtnrlt($j);
		?>
			<option value="comp/comptime.php?ctid=<?php echo $e[ctid] ?>"><?php echo date("在 Y-m-d H:i:s 的开始比赛",$e[starttime]) ?></option>
<?php }?>
<option selected="selected">[<?php echo $c ?>个场次]</option>
<option value="comp/editcomptime.php?action=add&cbid=<?php echo $d['cbid'] ?>">[添加新场次]</option>
		</select>
	</td>
    <td><?php echo "<a href='../user/detail.php?uid={$d['ouid']}' target='_blank'>{$d['nickname']}</a>" ?></td>
    <td><a href="comp/editcompbase.php?action=edit&cbid=<?php echo $d['cbid'] ?>">修改比赛</a></td>
  </tr>
<?php
	}
?>
</table>
<? 分页($cnt, $_GET['page'], '?settings=comp&'); ?>
