<?php
// 普通题面。打开 problem/problem.php?pid=1 可查看当前账号能读的题目、标签和提交入口。
require_once(__DIR__.'/../include/header.php');
$uid = (int) $_SESSION['ID'];
$pid = (int) ($_GET['pid'] ?? 0);
$problem = Problem::forReader($pid);           // 先判断阅读范围，再取得题面及关联数据。
if (!$problem) { http_response_code(404); exit('题目不存在或未开放。'); }  // 对读者统一隐藏不存在与尚未开放的题。
// 读者获准打开题面后，才载入题目标题、代码高亮和数学公式。
gethead(1, '', $pid.'. '.$problem['probname']);
require_once __DIR__.'/../include/hlighter.inc.php';
require_once __DIR__.'/../include/mathjax.inc.php';
// 游客没有个人提交，已登录用户取本题最好的成绩。
$myBest = $uid ? (Submission::best($pid, $uid)[0] ?? null) : null;
$authorName = Account::realName($problem['addid'])[0]['realname'] ?? '';  // 按旧权限在题面显示真实姓名。
$labels = Category::labels($pid);
$judges = Judge::enabled();                    // 两个提交表单共用同一份在线评测机列表。
$testDraft = (int)($problem['addid'] == $uid && !$problem['submitable']);
$leaders = Submission::leaders($pid, $SET['style_single_ranksize']);
$comments = Comment::recentForProblem($pid);   // 只取题面速览所需的讨论，全部楼层另有专页。
?>

<div class='row-fluid'>
<div id="leftbar" class='span4'>
<table class='table table-striped table-condensed table-bordered fiexd'>
<tr><th style="min-width: 5em;">题目名称</th>
<td style="min-width: 80%;"><?=$problem['pid']?>. <b><?=h($problem['probname'])?></b></td></tr>
<tr><th>输入输出</th>
<td><code><?=h($problem['filename'])?>.in/out</code></td></tr>
<tr><th>难度等级</th>
<td><?=难度($problem['difficulty']); ?>
<!--<?php if(Privilege::allows('查看题目')) { ?>
<a href="../problem/problem_export.php?pid=<?=$problem['pid']?>" target='_blank' class='btn btn-mini pull-right'>导出题目</a>
<?php  } ?>-->
</td></tr>
<tr><th>时间限制</th>
<td><?=$problem['timelimit']; ?> ms (<?=$problem['timelimit']/1000?> s)</td></tr>
<tr><th>内存限制</th>
<td><?=$problem['memorylimit']; ?> MB </td></tr>
<tr><th><?php if(Privilege::allows('查看数据')) { ?>
<a href='testdata.php?problem=<?=h($problem['filename'])?>'>测试数据</a>
<?php  } else { ?>测试数据<?php  } ?></th>
<td>
<span class='badge badge-<?=$problem['submitable']?"success":"important"?>'><?=$problem['datacnt']?></span>
<span class='pull-right'><?=$STR['plugin'][$problem['plugin']]?></span>
</td></tr>
<tr>
<th>题目来源</th>
<td>
<?php if(Privilege::allows('查看题目') || $uid == $problem['addid']) {
?>
<a href="../user/detail.php?uid=<?=$problem['addid']; ?>"><?=h($authorName)?></a>
<?php  } ?>
<?=date('Y-m-d', $problem['addtime']) ?>
<?php if(Privilege::allows('修改题目') || $problem['addid'] == $uid) { ?>
<a href="editprob.php?action=edit&pid=<?=$problem['pid']?>" class='btn btn-info btn-mini pull-right' title="修改题目 <?=h($problem['probname'])?>" >修改该题</a>
<?php  } ?>
</td></tr>
<tr><th>开放分组</th>
<td><a href="../user/index.php?gid=<?=$problem['gid'] ?>" target="_blank" class='btn btn-mini btn-warning'><?=h($problem['gname'])?></a></td></tr>
<tr><th><a href="../submit/index.php?pid=<?=$pid; ?>">提交状态</a></th>
<td><?php
$accepted = !empty($myBest['accepted']);      // 已通过本题的人可以直接看到标签。
if ($myBest) {
    echo "<a href='../submit/code.php?id=".(int)$myBest['sid']."'>".$STR['lang'][$myBest['lang']]."</a> ";
    评测结果($myBest['result'], 20);
} ?>
</td></tr>
<tr><th id="fenleito" title="单击以显示或隐藏分类标签" style="cursor:pointer">分类标签</th>
<td><div id="fenlei">
<?php
// 分类可由普通用户填写。名称放进链接正文，说明放进 title，两处都在输出时转义。
foreach ($labels as $label) { ?>
<a href="index.php?caid=<?=(int)$label['caid']?>" target="_blank" title="<?=h($label['memo'])?>" class="btn btn-mini"><?=h($label['cname'])?></a>
<?php } ?>
<?php  if(!$accepted) { ?>
<script>
  $('#fenlei').hide();
</script>
<?php  } else { ?>
<a id="addfenlei" class='btn btn-success btn-mini pull-right' href="#addcate" data-toggle='modal' title="添加分类标签"><i class='icon-plus icon-white'></i></a>
<div id='addcate' class='modal hide fade in'>
<form method="post" action="addcate.php" class='form-horizontal'><?=formTokenField()?>
<fieldset>
<div class='modal-header'>
<button class='close' data-dismiss='modal'>×</button>
<h3>添加分类</h3>
我们以你输入的分类名称来在数据库中查询是否已有该分类或相似分类（即分类名称或分类说明有与之相似的文字）；如果没有找到就添加一个这样的新分类，找到的话就在分类说明末尾加上你的分类说明；然后再为该题添加这样一个分类。
</div>
<input name="pid" type="hidden" id="pid" value="<?=$problem['pid']?>" />
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
<?php  } ?></div>
</td></tr>
<?php  if($uid) { ?>
<tr><form action="../submit/run.php" method="post" enctype="multipart/form-data" class='form-inline'><?=formTokenField()?>
<td colspan=2>
<input name="pid" type="hidden" id="pid" value="<?=$problem['pid']?>" />
<input type="file" name="file" title='选择程序源文件' /><br />
<?php if(Privilege::allows('测试题目') || $problem['addid'] == $uid) { ?>
<label class='checkbox inline pull-right'>
<input name="testmode" type="checkbox" <?=$testDraft ? 'checked' : ''?> value="1" />不写入数据库
</label>
<?php } ?>
<select name='judger' class='input-medium'>
<option value=0 selected=selected>自动选择评测机</option>
<?php
foreach ($judges as $judge) {
    echo "<option value={$judge['grid']} >".h($judge['memo'])."</option>";
}
?>       
</select>
<br />
<?php  if($problem['plugin'] == 3 || $problem['plugin'] == 4) { ?>
<input type='hidden' name='lang' value='zip' />
请提交一个 zip 压缩包，里面直接有 <?=$problem['datacnt']?> 个 <?=h($problem['filename'])?>#.out 文件<br />
<a href="downinput.php?file=<?=h($problem['filename'])?>&data=<?=$problem['datacnt']?>" class="btn btn-success btn-mini pull-left">下载测试数据</a>
<?php } else { ?>
<?php renderLanguageChoices(false); ?>
<?php  } ?>
<button type='submit' class='btn btn-primary pull-right' >提交代码</button>
</td>
</form>
</tr>
<?php  } ?>
</table>
<table id="singlerank" class='table table-striped table-condensed table-bordered fiexd'>
<tr><td colspan=4>
通过：<?php echo $problem['acceptcnt']; ?>，
提交：<?php echo $problem['submitcnt']; ?>，
通过率：<?php echo ($problem['submitcnt'] ? round($problem['acceptcnt']/$problem['submitcnt']*100,2) : 0); ?>%
</td><tr>
<?php
foreach ($leaders as $leader) {
?>
<tr>
<td><a href="../user/detail.php?uid=<?=$leader['uid'] ?>">
<?=gravatar::showImage($leader['email']);?>
<?=h(Privilege::allows('查看用户') ? $leader['realname'] : $leader['nickname'])?>
</a></td>
<td><span class="<?=$leader['accepted']?'ok':'no'?>"><?=$leader['score'] ?></span></td>
<td><?php printf("%.3f s",$leader['runtime']/1000.0) ?></td>
<td><a href="../submit/code.php?id=<?=$leader['sid'] ?>" target="_blank"><?=$STR['lang'][$leader['lang']]?></a></td>
</tr>
<?php
}
?></table>
<table class='table table-condensed table-bordered fiexd'>
<tr><th colspan=5>
<a href="comments.php?pid=<?=$pid?>">关于 <b><?=shortname($problem['probname']); ?></b> 的讨论</a>
<?php  if($uid) { ?>
<a href="comment.php?pid=<?=$pid?>" class="pull-right btn btn-mini btn-danger">发表评论</a>
<?php  } ?>
</th></tr>
<?php
foreach ($comments as $i => $comment) {
?>
<tr><td>
<div class="wrap">
<?php echo BBCode($comment['detail'])?>
</div>
<div>
<a href="../user/detail.php?uid=<?=$comment['uid'] ?>"><?=gravatar::showImage($comment['email']);?><?=h($comment['nickname'])?></a>
<div class="pull-right">
<?php if ($comment['showcode'] && $comment['latest_sid']) { ?>
    <a href="../submit/code.php?id=<?=$comment['latest_sid']?>" title="查看该用户最后一次提交的代码"><i class='icon icon-download'></i></a>
<?php } ?>
<span class="muted"><?=date('Y-m-d H:i',$comment['stime'])?></span>
<span><?=($i+1)?>楼</span>
<?php  if($_SESSION['ID'] && $_SESSION['ID'] == $comment['uid']) { ?>
<a href='comment.php?cid=<?=$comment['cid']?>' class='btn btn-mini btn-warning'>修改</a>
<?php  } else if($_SESSION['ID']) { ?>
<a class='btn btn-mini btn-danger' href="comment.php?ccid=<?=$comment['cid']?>&pid=<?=$problem['pid']?>&user=<?=h($comment['nickname'])?>">回复</a>
<?php  } ?>
</div>
</div>
</td></tr>
<?php
}
?>
</table>
</div>
<div id="rightbar" class='span8'>
<div id="probdetail" class='page'>
<div class="problem tou">
<a id="chbar" title="隐藏左边栏" class="pull-left" style="cursor:pointer"><i id="chbaricon" class="icon icon-indent-left"></i></a>
<h1><?=$problem['pid']?>. <?=h($problem['probname'])?>
<?php if(Privilege::allows('修改题目') || $problem['addid'] == $uid) { ?>
<a href="editprob.php?action=edit&pid=<?=$problem['pid']?>" title="修改题目 <?=h($problem['probname'])?>" class="pull-right"><i class="icon icon-edit"></i></a>
<?php } ?>
</h1>
<?=难度($problem['difficulty']); ?>&nbsp;&nbsp;
输入文件：<code><?=h($problem['filename'])?>.in</code>&nbsp;&nbsp;
输出文件：<code><?=h($problem['filename'])?>.out</code>&nbsp;&nbsp;
<?=$STR['plugin'][$problem['plugin']]?>
<br />
时间限制：<?=$problem['timelimit']/1000?> s&nbsp;&nbsp;
内存限制：<?=$problem['memorylimit']; ?> MB

</div>
<dl class='problem'>
<?=richText($problem['detail'])?>
</dl>
<?php  if($uid) { ?>
<form action="../submit/run.php" method="post" enctype="multipart/form-data" class='form-inline' id="tijiao"><?=formTokenField()?>
<input name="pid" type="hidden" id="pid" value="<?=$problem['pid']?>" />
<input id="source" type="file" name="file" title='选择程序源文件' />
<select name='judger' class='input-medium'>
<option value=0 selected=selected>自动选择评测机</option>
<?php
foreach ($judges as $judge) {
    echo "<option value={$judge['grid']} >".h($judge['memo'])."</option>";
}
?>       
</select>
<?php if(Privilege::allows('测试题目') || $problem['addid'] == $uid) { ?>
<label class='checkbox inline'>
<input id="testmode" name="testmode" type="checkbox" <?=$testDraft ? 'checked' : ''?> value="1" />不写入数据库
</label>
<?php } ?>
<br />
<button type='submit' class='btn btn-primary' >提交代码</button>
<?php  if($problem['plugin'] == 3 || $problem['plugin'] == 4) { ?>
<input type='hidden' name='lang' value='zip' />
请提交一个 zip 压缩包，里面直接有 <?=$problem['datacnt']?> 个 <?=h($problem['filename'])?>#.out 文件
<a href="downinput.php?file=<?=h($problem['filename'])?>&data=<?=$problem['datacnt']?>" class="btn btn-success btn-mini">下载测试数据</a>
<?php } else { ?>
<?php renderLanguageChoices(true); ?>
<?php  } ?>
</form>
<?php  } ?>

</div>
</div>
</div>

<?php
include_once(__DIR__.'/../include/footer.php');
?>
