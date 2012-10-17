<?php
require_once("../include/header.php");
gethead(8,"修改题目","");
过滤("cate");

$pid = (int)$_GET['pid'];
if($_GET[action]=="change") {
    if(有此权限("修改题目") && 有此权限("查看题目")) {
        $p=new DataAccess();
        $sql="update problem set submitable=1-submitable where pid={$pid}";
        $p->dosql($sql);
        提示("修改可用与否成功！", 取路径("problem/index.php"));
    } else
        异常("没有权限修改或查看题目！", 取路径("problem/index.php"));
}

if($_FILES['datafile']['size'] && !$_FILES['datafile']['error']) {
    chdir($cfg['testdata']);
    $dir="{$cfg['testdata']}/{$_POST['filename']}/";
    mkdir($dir);
    $cmd = "unzip -uo {$_FILES['datafile']['tmp_name']} -d\"{$cfg['testdata']}\"";
    exec($cmd);
    exec("chmod 777 *");
    $ff="<p>正在上传测试数据：<ul>";
    chdir($dir);
    $pname=$_POST['filename'];
    $count=(int)$_POST['datacnt'];
    if($data = (file_exists("data.txt"))) {
        $fp=fopen("data.txt","r");
        fscanf($fp,"%d\n",$start);
        $start = (int) $start - 1;
        fscanf($fp,"%s\n",$input);
        fscanf($fp,"%s\n",$answer);
        fclose($fp);
    }
    for($i=$count; $i>=1; $i--) {
        $now = (int) ($start + $i);
        rename(str_replace("#", "{$now}", $input), "{$_POST['filename']}{$i}.in");
        rename(str_replace("#", "{$now}", $answer), "{$_POST['filename']}{$i}.ans");
        if(!file_exists("{$_POST['filename']}{$i}.ans") && file_exists("{$_POST['filename']}{$i}.out"))
            rename("{$_POST['filename']}{$i}.out", "{$_POST['filename']}{$i}.ans");
        if(file_exists("{$_POST['filename']}{$i}.in") && file_exists("{$_POST['filename']}{$i}.ans"))
            $ff.="<li><span class='label label-success'>第 $i 个</span>测试点上传成功！</li>";
        else
            $ff.="<li><span class='label label-important'>第 $i 个</span>测试点上传失败！！</li>";
    }
    $ff.="</ul></p>";
}
if ($_POST[submitable]==1) $sub=1; else $sub=0;
if ($_REQUEST[action]=='add') {
	$p=new DataAccess();
	$sql="insert into problem(probname,filename,readforce,submitable,datacnt,timelimit,memorylimit,detail,addtime,addid,difficulty,plugin,`group`) values('{$_POST[probname]}','{$_POST[filename]}',{$_POST[readforce]},{$sub},{$_POST[datacnt]},{$_POST[timelimit]},{$_POST[memorylimit]},'".$_POST['detail']."',".time().",{$_SESSION[ID]},{$_POST[difficulty]},'{$_POST['plugin']}','{$_POST['group']}')";
	$p->dosql($sql);
	
	$sql="select pid from problem where filename='{$_POST['filename']}'";
	$p->dosql($sql);
	$d=$p->rtnrlt(0);
	$pid=$d['pid'];
	foreach($_POST[cate] as $k=>$v) {
		if ($v) {
			$sql="insert into tag(pid,caid) values({$pid},{$k})";
			$p->dosql($sql);
		}
	}
    提示("$ff 添加题目 $pid 成功！", 取路径("problem/problem.php?pid=$pid"));
} else if ($_REQUEST[action]=='edit') {
	$p=new DataAccess();
    $sql="select filename from problem where pid={$_REQUEST['pid']}";
    $cnt=$p->dosql($sql);
    if($cnt) {
        $d=$p->rtnrlt(0);
        if($d['filename'] != $_POST['filename']) {
            $sql="update problem set addtime=".time().", addid=".(int)$_SESSION['ID']." where pid={$_REQUEST['pid']}";
            $p->dosql($sql);
        }
    }
	$sql="update problem set probname='{$_POST['probname']}',filename='{$_POST[filename]}',readforce={$_POST[readforce]},submitable={$sub},datacnt={$_POST[datacnt]},timelimit={$_POST[timelimit]},memorylimit={$_POST[memorylimit]},detail='".($_POST['detail'])."',difficulty={$_POST[difficulty]},plugin='{$_POST['plugin']}',`group`='{$_POST['group']}' where pid={$_REQUEST['pid']}";
	$p->dosql($sql);
	foreach($_POST[cate] as $k=>$v) {
		$sql="select tid from tag where caid={$k} and pid={$_REQUEST[pid]}";
		$cnt=$p->dosql($sql);
		if (!$cnt) {
			if ($v)
				$sql="insert into tag(pid,caid) values({$_REQUEST[pid]},{$k})";
		} else {
			if (!$v)
				$sql="delete from tag where pid={$_REQUEST[pid]} and caid={$k}";
		}
		$p->dosql($sql);
	}
	$pid=$_REQUEST[pid];
    提示("$ff 修改题目 $pid 成功！", 取路径("problem/problem.php?pid=$pid"));
} else if ($_REQUEST[action]=='del') {
	$p=new DataAccess();
	$sql="delete from problem where pid={$_REQUEST['pid']}";
	$p->dosql($sql);
	$pid=0;
    提示("删除题目 $pid 成功！", 取路径("problem/index.php"));
}


?>
