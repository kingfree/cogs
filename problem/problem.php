<?php
require_once("../include/header.php");

$p=new DataAccess();
$pid = (int)$_GET['pid'];
$sql="select problem.*,groups.* from problem,groups where pid=".(int)$_GET[pid]." and groups.gid=problem.group limit 1";
$cnt=$p->dosql($sql);
$d=$p->rtnrlt(0);

$title = $d['probname'];
gethead(1,"",$pid.". ".$title);

$LIB->mathjax();

$q=new DataAccess();
$r=new DataAccess();

if($cnt) {
    if ($d[readforce]>$_SESSION[readforce]) 
        异常("没有阅读权限！", 取路径("problem/index.php"));
    if (!$d[submitable] && !有此权限('查看题目')) 
        异常("该题目不可提交！", 取路径("problem/index.php"));
    $subgroup=$LIB->getsubgroup($q,$d['gid']);
    $subgroup[0]=$d['gid'];
    $promise=false;
    foreach($subgroup as $value) {
        if ($value==(int)$_SESSION['group']) {
            $promise=true;
            break;
        }
    }
    if (!$promise && !有此权限('查看题目'))
        异常("没有阅读权限！", 取路径("problem/index.php"));
    $pid=$d[pid];
} else {
    异常("无此题目！！", 取路径("problem/index.php"));
}
?>

<div class='row-fluid'>
<div class='span4'>
<table class='table table-striped table-condensed table-bordered fiexd'>
<tr><th style="width: 5em;">题目名称</th>
<td><?=$d['pid']?>. <b><?=$d['probname']; ?></b></td></tr>
<tr><th>输入输出</th>
<td><code><?=$d['filename']?>.in/out</code></td></tr>
<tr><th>难度等级</th>
<td><?=难度($d['difficulty']); ?>
<!--<?php if(有此权限('查看题目')) { ?>
<a href="../problem/problem_export.php?pid=<?=$d['pid']?>" target='_blank' class='btn btn-mini pull-right'>导出题目</a>
<? } ?>-->
</td></tr>
<tr><th>时间限制</th>
<td><?=$d['timelimit']; ?> ms (<?=$d['timelimit']/1000?> s)</td></tr>
<tr><th>内存限制</th>
<td><?=$d['memorylimit']; ?> MB </td></tr>
<tr><th><?php if(有此权限('查看数据')) { ?>
<a href='testdata.php?problem=<?=$d['filename']?>'>测试数据</a>
<? } else { ?>测试数据<? } ?></th>
<td>
<span class='badge badge-<?=$d['submitable']?"success":"important"?>'><?=$d['datacnt']?></span>
<span class='pull-right'><?=$STR['plugin'][$d['plugin']]?></span>
</td></tr>
<tr>
<th>题目来源</th>
<td>
<?php if(有此权限('查看题目')) { 
    $sql="SELECT realname FROM userinfo WHERE uid ={$d['addid']} limit 1";
    $ac=$q->dosql($sql);
    $e=$q->rtnrlt(0); ?>
<a href="../user/detail.php?uid=<?=$d['addid']; ?>"><?=$e['realname']?></a>
<? } ?>
<?=date('Y-m-d', $d['addtime']) ?>
<?php if(有此权限('修改题目')) { ?>
<a href="editprob.php?action=edit&pid=<?=$d['pid']?>" class='btn btn-info btn-mini pull-right' >修改该题</a>
<? } ?>
</td></tr>
<tr><th>开放分组</th>
<td><a href="../user/index.php?gid=<?=$d['gid'] ?>" target="_blank" class='btn btn-mini btn-warning'><?=$d['gname']?></a></td></tr>
<tr><th><a href="../submit/index.php?pid=<?=$pid; ?>">提交状态</a></th>
<td><?php
$acpid=0;
if($_SESSION['ID']) {
    $sql="select * from submit where sid in (SELECT sid FROM submit WHERE pid ={$pid} AND uid ={$_SESSION['ID']} order by score desc) order by sid desc";
    $ac=$q->dosql($sql);
    if ($ac) {
        $e=$q->rtnrlt(0);
        $acpid=$e['accepted'];
        echo "<a href='../submit/code.php?id={$e['sid']}'>";
        echo $STR['lang'][$e['lang']];
        echo "</a> ";
        评测结果($e['result'], 20);
    }
} ?>
</td></tr>
<tr><th>分类标签</th>
<td><?php
$sql="select category.cname,category.caid from category,tag where tag.pid={$_GET[pid]} and category.caid=tag.caid";
$cnt2=$r->dosql($sql);
for ($i=0;$i<=$cnt2-1;$i++) {
    $e=$r->rtnrlt($i);
    HTML(" <a href='index.php?caid={$e['caid']}' target='_blank' class='btn btn-mini'>{$e['cname']}</a> ");
} ?>
<? if($acpid) { ?>
<a class='btn btn-success btn-mini' href="#addcate" data-toggle='modal'><i class='icon-plus icon-white'></i>添加分类</a>
<div id='addcate' class='modal hide fade in'>
<form method="post" action="addcate.php" class='form-horizontal'>
<fieldset>
<div class='modal-header'>
<button class='close' data-dismiss='modal'>×</button>
<h3>添加分类</h3>
我们以你输入的分类名称来在数据库中查询是否已有该分类或相似分类（即分类名称或分类说明有与之相似的文字）；如果没有找到就添加一个这样的新分类，找到的话就在分类说明末尾加上你的分类说明；然后再为该题添加这样一个分类。
</div>
<input name="pid" type="hidden" id="pid" value="<?=$d['pid']?>" />
<div class='modal-body'>
<div class='control-group'>
<label class='control-label' for='cname'>分类名称</label>
<div class='controls'>
<input name="cname" type="text" id="cname" value="" />
</div>
</div>
<div class='control-group'>
<label class='control-label' for='memo'>分类说明</label>
<div class='controls'>
<textarea id='memo' name="memo" class="textarea"></textarea>
</div>
</div>
<div class='modal-footer'>
<button data-dismiss='modal' class='btn'>取消</button>
<button type="submit" class='btn btn-primary'>添加分类</button>
</div>
</fieldset>
</form>
</div>
<? } ?>
</td></tr>
<? if($_SESSION['ID']) { ?>
<tr><form action="../submit/run.php" method="post" enctype="multipart/form-data" class='form-inline'>
<td colspan=2>
<input name="pid" type="hidden" id="pid" value="<?=$d['pid']?>" />
<input type="file" name="file" title='选择程序源文件' /><br />
<?php if(有此权限('测试题目')) { ?>
<label class='checkbox inline pull-right'>
<input name="testmode" type="checkbox" value="1" />不写入数据库
</label>
<?php } ?>
<select name='judger' class='input-medium'>
<option value=0 selected=selected>自动选择评测机</option>
<?
$sql="select grid,address,memo from grader where enabled=1 order by priority desc";
$cnt=$q->dosql($sql);
for ($i=0;$i<$cnt;$i++) {
    $e=$q->rtnrlt($i);
    echo "<option value={$e['grid']} >{$e['memo']}</option>";
}
?>       
</select>
<br />
<? if($d['plugin'] == 3 || $d['plugin'] == 4) { ?>
<input type='hidden' name='lang' value='zip' />
请提交一个 zip 压缩包，里面直接有 <?=$d['datacnt']?> 个 <?=$d['filename']?>#.out 文件<br />
<a href="downinput.php?file=<?=$d['filename']?>&data=<?=$d['datacnt']?>" class="btn btn-success btn-mini pull-left">下载测试数据</a>
<?php } else { ?>
<label class='radio inline'><input type='radio' name='lang' value='pas' />Pascal</label>
<label class='radio inline'><input type='radio' name='lang' value='c' />C</label>
<label class='radio inline'><input type='radio' name='lang' value='cpp' checked='checked'/>C++</label>
<? } ?>
<button type='submit' class='btn btn-primary pull-right' >提交代码</button>
</td>
</form>
</tr>
<? } ?>
</table>
<table id="singlerank" class='table table-striped table-condensed table-bordered fiexd'>
<tr><td colspan=4>
通过：<?php echo $d['acceptcnt']; ?>，
提交：<?php echo $d['submitcnt']; ?>，
通过率：<?php echo @round($d['acceptcnt']/$d['submitcnt']*100,2); ?>%
</td><tr>
<?php
$sql2="select submit.*,userinfo.nickname,userinfo.realname,userinfo.uid,userinfo.email from submit,userinfo where submit.pid={$pid} and submit.uid=userinfo.uid order by score desc, runtime asc, memory asc limit {$SET['style_single_ranksize']}";
$cnt=$q->dosql($sql2);
for ($i=0;$i<$cnt;$i++) {
    $f=$q->rtnrlt($i);
?>
<tr>
<td><a href="../user/detail.php?uid=<?=$f['uid'] ?>">
<?=gravatar::showImage($f['email']);?>
<?=有此权限('查看用户') ? $f['realname'] : $f['nickname'] ?>
</a></td>
<td align=center><span class="<?=$f['accepted']?'ok':'no'?>"><?=$f['score'] ?></span></td>
<td align=right><?php printf("%.3f s",$f['runtime']/1000.0) ?></td>
<td align=center><a href="../submit/code.php?id=<?=$f['sid'] ?>" target="_blank"><?=$STR['lang'][$f['lang']]?></a></td>
</tr>
<?php 
}
?></table>
<table id="Comments" class='table table-striped table-condensed table-bordered fiexd'>
<tr><th colspan=3>
<a href="comments.php?pid=<?=$pid?>">关于 <b><?=shortname($d['probname']); ?></b> 的讨论</a>
<? if($_SESSION['ID']) { ?>
<a href="comment.php?pid=<?=$pid?>" class="pull-right btn btn-mini btn-danger"><b>发表评论</b></a>
<? } ?>
</th></tr>
<?
$sql="select comments.*,userinfo.nickname,userinfo.uid,userinfo.email from comments,userinfo where userinfo.uid=comments.uid and comments.pid='{$d['pid']}' order by comments.cid desc";
$cnt=$q->dosql($sql);
for ($i=0;$i<$cnt;$i++) {
    $e=$q->rtnrlt($i);
?>
    <tr class="CommentsU">
    <td><a href="../user/detail.php?uid=<?=$e['uid'] ?>"><?=gravatar::showImage($e['email']);?><?=$e['nickname'] ?></a></td>
    <td><?=date('y/m/d H:i',$e['stime'])?>
   <?php if($e['showcode']) {
	$sql="select sid from submit where uid='{$e['uid']}' and pid='{$d['pid']}' order by subtime desc";
	$r->dosql($sql);
	$f=$r->rtnrlt(0);
	?>
	<a href="../submit/code.php?id=<?=$f['sid']?>" title="查看该用户最后一次提交的代码"><i class='icon icon-download'></i></a>
	<?php } ?>
 </td>
    <td>
    #<?=$cnt-$i?></td>
    </tr>
    <tr><td colspan=3 class="CommentsK">
    <? if($_SESSION['ID']==$e['uid']) echo "<a href='comment.php?cid={$e['cid']}' class='pull-right btn btn-mini btn-warning'>修改</a>";?>
    <?php echo nl2br(sp2n(htmlspecialchars($e['detail'])))?>
    </td></tr>
<?
}
?>
</table>
</div>
<div class='span8'>
<div id="probdetail" class='page'>
<center class="problem tou">
<h1><?=$d['pid']?>. <?=$d['probname']?></h1>
<?=难度($d['difficulty']); ?>&nbsp;&nbsp;
输入文件：<code><?=$d['filename']?>.in</code>&nbsp;&nbsp;
输出文件：<code><?=$d['filename']?>.out</code>&nbsp;&nbsp;
<?=$STR['plugin'][$d['plugin']]?>
<br />
时间限制：<?=$d['timelimit']/1000?> s&nbsp;&nbsp;
内存限制：<?=$d['memorylimit']; ?> MB
<?php
$Jia['url']="problem/problem.php?pid={$d['pid']}";
$Jia['title']="{$d['pid']}. {$d['probname']}" . 难度($d['difficulty']) . "({$d['filename']})";
$Jia['summary']=trim(strip_tags($d['detail']));
//include("../include/jia.inc.php");
?>
<hr />
</center>
<dl class='problem'>
<?=$d['detail']?>
</dl>
</div>
</div>
</div>

<?php
include_once("../include/footer.php");
?>
