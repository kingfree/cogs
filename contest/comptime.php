<?php
require_once("../include/header.php");
gethead(1,"查看比赛","比赛场次评测");
$p=new DataAccess();
$q=new DataAccess();
$sql="select comptime.*,compbase.cname,groups.* from comptime,compbase,groups where comptime.cbid=compbase.cbid and comptime.ctid={$_GET[ctid]} and groups.gid=comptime.group";
$cnt=$p->dosql($sql);
if(!$cnt) 异常("未查询到记录！");
$d=$p->rtnrlt(0);
?>
<div class='container'>
<table class='table table-striped table-condensed table-bordered fiexd'>
  <tr>
    <td width="90px">CTID</td>
    <td><?php echo $d[ctid] ?></td>
    <td width="90px">关联比赛</td>
    <td><?php echo $d[cname] ?></td>
  </tr>
  <tr>
    <td>开始时间</td>
    <td><?php echo date('Y-m-d H:i:s', $d[starttime]) ?></td>
    <td>结束时间</td>
    <td><?php echo date('Y-m-d H:i:s', $d[endtime]) ?></td>
  </tr>
  <tr>
    <td>开放分组</td>
    <td><a href="../user/index.php?gid=<?php echo $d['gid'] ?>" target="_blank"><?php echo $d['gname'] ?></a></td>
    <td>公布成绩</td>
    <td><?php echo $d[showscore]?"是":"否" ?></td>
  </tr>
  <tr>
    <td>比赛状态</td>
    <td><?php
	 if (time()>$d[endtime]) echo "已结束"; else
	 if (time()<$d[endtime] && time()>$d[starttime]) echo "正在进行"; else
	 echo "还未开始"; 
	 ?></td>
    <td>阅读权限</td>
    <td><?php echo $d[readforce] ?></td>
  </tr>
  <tr>
    <td>场次介绍</td>
    <td><?php echo nl2br(sp2n(htmlspecialchars($d[intro]))) ?></td>
    <td>修改场次信息</td>
    <td><a href="editcomptime.php?action=edit&ctid=<?php echo $d[ctid] ?>">修改</a></td>
  </tr>
  <tr>
    <td>查看成绩</td>
    <td><a href="report.php?ctid=<?=$d['ctid']?>" target="_blank">查看</a></td>
    <td>导出代码</td>
    <td>
    <a href="export.php?ctid=<?=$d['ctid']?>" class='btn btn-mini'>导出选手比赛代码</a>
    </td>
  </tr>
</table>
<?php
$sql="select compscore.uid,userinfo.realname,userinfo.nickname from compscore,userinfo where userinfo.uid=compscore.uid and compscore.ctid={$_GET[ctid]} order by uid asc";
$cnt=$p->dosql($sql);
if ($cnt) {
?>
<form method="post" action="judge.php">
    选择评测机：<select name='judger' id='judger'>
    <option value=0 selected=selected>自动选择</option>
<?
    $sql="select grid,address,memo from grader where enabled=1 order by priority desc";
    $cnt1=$q->dosql($sql);
    for ($i=0;$i<$cnt1;$i++) {
        $e=$q->rtnrlt($i);
        echo "<option value={$e['grid']} >{$e['memo']}</option>";
    }
?>       
    </select>
  <input name="do" type="submit" id="do" class='btn' value="评测选定" />
  <input name="do" type="submit" id="do" class='btn' value="评测全部" />
  <input name="ctid" type="hidden" id="ctid" value="<?php echo $_GET['ctid'] ?>" />
</p>
<table class='table table-striped table-condensed table-bordered fiexd'>
  <tr>
    <th width='100px'>用户昵称</th>
    <th width='60px'>真实姓名</th>
    <th>提交记录</th>
  </tr>
<?php
	$tu=0;
	for ($i=0;$i<$cnt;$i++)
	{
		$d=$p->rtnrlt($i);
		if ($d[uid]==$tu) continue;
		$tu=$d[uid];
?>
  <tr>
    <td><a target="_blank" href="../user/detail.php?uid=<?php echo $d[uid] ?>"><?php echo $d[nickname] ?></a></td>
    <td><a target="_blank" href="../user/detail.php?uid=<?php echo $d[uid] ?>"><?php echo $d[realname] ?></a></td>
    <td>
	
<table class='table table-striped table-condensed table-bordered fiexd'>
	  <tr>
		<th width="60px">CSID</th>
		<th width="100px">题目名</th>
		<th width="50px">代码</th>
		<th width="160px">提交时间</th>
		<th width="60px">得分</th>
		<th>测试点</th>
	  </tr>
	<?php
	$sql="select compscore.csid,compscore.pid,compscore.lang,compscore.subtime,compscore.score,compscore.result,problem.probname from compscore,problem where problem.pid=compscore.pid and compscore.uid={$d[uid]} and compscore.ctid={$_GET[ctid]}";
	$c=$q->dosql($sql);
	if ($c) {
		for ($j=0;$j<$c;$j++) {
            $e=$q->rtnrlt($j);
	?>
        <tr>
        <td>
        <input name="doit[]" type="checkbox" id="doit[]" value="<?php echo $e[csid] ?>" />
        <input name="doall[]" type="hidden" id="doall[]" value="<?php echo $e[csid] ?>" />
        <?php echo $e[csid] ?></td>
        <td><a target="_blank" href="problem.php?ctid=<?=$_GET[ctid]?>&pid=<?php echo $e[pid] ?>&uid=<?=$d[uid]?>"><?php echo $e[probname] ?></a></td>
        <td><a href="code.php?csid=<?php echo $e[csid] ?>" target="_blank"><?php echo $STR[lang][$e[lang]] ?></a></td>
        <td><?php echo date("Y年m月d日 H:i:s",$e[subtime]) ?></td>
        <td><?php echo $e[score] ?></td>
        <td><?php echo 评测结果($e[result]) ?></td>
        </tr>
        <?php
        }
    }
    ?>
        </table>
        </td>
        <?php
    }
    ?>
  </tr>
</table>
</form>
<?php
}
?>
</div>
<?php
include_once("../include/footer.php");
?>
