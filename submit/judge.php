<?php
require_once("../include/header.php");
gethead(1,"sess","评测");
if (!$_POST['pid']) 异常("你来错地方了！");
$LIB->hlighter();
$LIB->func_socket();
$p=new DataAccess();
$sql="select * from problem where pid={$_POST['pid']}";
$p->dosql($sql);
$d=$p->rtnrlt(0);
if(!$d['submitable'] && !有此权限('查看题目'))
    异常("不可提交！",取路径("problem/index.php"));
$lang=langstrtonum($_POST['lang']);
$info=array();
$info['pid']=$_POST['pid'];
$info['sid']=$_POST['sid'];
$info['uid']=$_SESSION['ID'];
$info['language']=$lang;
$info['pname']=$d['filename'];
$info['datacnt']=$d['datacnt'];
$info['timelimit']=$d['timelimit'];
$ptitle = $d['probname'];
$info['memorylimit']=$d['memorylimit'];
$info['plugin']=$d['plugin'];
$info['compiledir']=$SET['dir_source'];
$info['mode']="normal";
if ($_POST['testmode']=='1' && 有此权限('测试题目'))
	$info['mode']="test";
if ($_POST['rejudge']==1) {
	$info['rejudge']=1;
	$sql="select * from submit where sid='{$info['sid']}'";
	$p->dosql($sql);
	$e=$p->rtnrlt(0);
	$info['uid']=$e['uid'];
	$src=$e['srcname'];
}
?>

<script language="javascript">
</script>

<div id='often' class='row-fluid'>
  <div class='span6'>
    <div id='grader' class='alert alert-info'>
    </div>
    <div id='running' class='alert alert-success'>
    </div>
  </div>
  <div class='span6'>
    <div id='result' class='alert'>
    </div>
  </div>
</div>

<div class='alert' class='row-fluid'>
</div>

<?php
include_once("../include/footer.php");
?>
