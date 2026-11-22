<?php
require_once("../include/header.php");
$mySubmissions = Submission::bestByProblem($_SESSION['ID']);
if ($_GET['caid']) {
    $pRows = Category::byId($_GET['caid']);
    $cnt = count($pRows);
    $d=($pRows[0] ?? false);
    gethead(1,"","{$d['cname']} - 题目列表");
} else if($_GET['key']) {
    gethead(1,"","{$_GET['key']} - 题目列表");
} else {
    gethead(1,"","题目列表");
}
?>

<div class='row-fluid'>


<?php if(Privilege::allows('普通用户')) { ?>
<a href="editprob.php?action=add" class="btn btn-info pull-left">添加新题目</a>
<?php } ?>
<a href="catelist.php" class='btn btn-success pull-left'><i class="icon-tags icon-white"></i>题目分类列表</a>

<?php  if ($_GET['caid']) { ?>
<span id="cate_detail"> 当前分类：
<?php if(Privilege::allows('分类管理')) { ?>
<a href="editcate.php?action=edit&caid=<?php echo $d['caid'] ?>">
<?php } ?>
<span class="big"><?=h($d['cname'])?></span>
<?php if(Privilege::allows('分类管理')) { ?>
</a>
<?php } ?>
（<?php echo nl2br(sp2n(htmlspecialchars($d['memo']))) ?>）
</span>
<?php } ?>

<?php
$pRows = Problem::search($_GET['caid'] ?? 0,
    $_GET['key'] ?? '', $_GET['diff'] ?? '', $_GET['rank'] ?? 'pid', $_GET['order'] ?? 'asc');
$cnt = count($pRows);
$st=检测页面($cnt, ($_GET['page'] ?? 1));
?>
<form method="get" action="" class='form-search center'>
<a href="random.php" title="随机选择一道你没有通过的题目" class='btn btn-danger' >随机题目<i class='icon icon-random icon-white'></i></a>
<input name="caid" type="hidden" value="<?=h($_GET['caid'] ?? '')?>" />
<input name="order" type="hidden" value="<?=$_GET['order']=='asc'?'desc':'asc'?>" />
难度 
<select name="diff" onchange="this.form.submit()" class='input-medium'>
<option value="" selected="selected" <?php if ($_GET['diff']=="") {?> selected="selected"<?php } ?> >全部</option>
<?php for ($i=1;$i<=10;$i++) { ?>
<option value="<?php echo $i;?>" <?php if ($_GET['diff']==$i) {?> selected="selected"<?php } ?> ><?php echo 难度($i);?></option>
<?php } ?>
</select>
<div class='input-append pull-right'>
<input name="key" type="text" class='search-query input-medium' value='<?=h($_GET['key'] ?? '')?>' title='输入关键字按回车搜索题目' placeholder='搜索题目'/>
<button type='submit' class='btn'><i class='icon icon-search'></i></button>
</div>
<div class='btn-group'>排序方法：
<button name="rank" class='btn' value='pid'>PID</button>
<button name="rank" class='btn' value='probname'>题目名称</button>
<button name="rank" class='btn' value='filename'>文件名称</button>
<button name="rank" class='btn' value='timelimit'>时间限制</button>
<button name="rank" class='btn' value='memorylimit'>空间限制</button>
<button name="rank" class='btn' value='difficulty'>题目难度</button>
<button name="rank" class='btn' value='plugin'>评测方式</button>
<button name="rank" class='btn' value='acceptcnt'>通过次数</button>
<button name="rank" class='btn' value='submitcnt'>提交次数</button>
<?php if(Privilege::allows('查看题目')) { ?>
<button name="rank" class='btn' value='submitable'>标识</button>
<?php  } ?>
</div>
</form>
<script language=javascript>
function okic(name) {
    if(confirm("你确定要更改 "+name+" 的提交属性吗？"))
        return true;
    return false;
}
</script>
<table id="problist" class='table table-striped table-condensed table-bordered fiexd'>
<thead><tr>
<th style="min-width: 4ex;">PID</th>
<th onclick="sortTable('problist', 0, 'int')">题目名称</th>
<th>文件名称</th>
<th>时间</th>
<th>空间</th>
<th>难度</th>
<th>评测方式</th>
<th onclick="sortTable('problist', 6, 'int')">通过</th>
<th onclick="sortTable('problist', 7, 'int')">提交</th>
<th onclick="sortTable('problist', 8, 'int')">通过率</th>
<?php if(Privilege::allows('查看题目')) { ?>
<th class=admin style="min-width: 4ex;"><i class='icon icon-question-sign'></i></th>
<?php } ?>
</tr></thead>
<?php
if (!$err) for ($i=$st;$i<$cnt && $i<$st+$SET['style_pagesize'] ;$i++) {
    $d=($pRows[$i] ?? false);
    if($_GET['key'] && $cnt == 1)
        echo "<script language='javascript'>location='problem.php?pid={$d['pid']}';</script>";
?>
<tr>
<td><?php echo $d['pid'] ?></td>
<td>
<?php if(Privilege::allows('修改题目')) { ?>
<a href="editprob.php?action=edit&pid=<?=$d['pid']?>" title="修改题目 <?=h($d['probname'])?>" class="pull-right"><i class="icon icon-edit"></i></a>
<?php } ?>
<?php  是否通过($mySubmissions[$d['pid']] ?? null);?>
<b><a href="problem.php?pid=<?=$d['pid'] ?>"><?=h($d['probname'])?></a></b>
</td>
<td><code><?=h($d['filename'])?></code></td>
<td><?php echo $d['timelimit']/1000 . " s"; ?></td>
<td><?php echo $d['memorylimit'] . " MB"; ?></td>
<td><?php echo 难度($d['difficulty']); ?></td>
<td><?=$STR['plugin'][$d['plugin']]?></td>
<td><?php echo $d['acceptcnt']; ?></td>
<td><?php echo $d['submitcnt']; ?></td>
<td><?php echo ($d['submitcnt'] ? round($d['acceptcnt']/$d['submitcnt']*100,2) : 0); ?>%</td>
<?php if(Privilege::allows('查看题目')) { ?>
<td class="center">
<a href="doeditprob.php?action=change&pid=<?=$d['pid']?>" title="更改题目 <?=shortname($d['probname'])?> 的可用性" onclick="return confirm('更改该题的提交属性？')">
<?php if ($d['submitable']) echo "<span class='label label-success'><i class='icon icon-ok-circle icon-white'></i></span>"; else echo "<span class='label label-important'><i class='icon icon-ban-circle icon-white'></i></span>"; ?>
</a>
</td>
<?php } ?>
</tr>
<?php } ?>
</table>
<?php  分页($cnt, ($_GET['page'] ?? 1), '?caid='.$_GET['caid'].'&order='.$_GET['order'].'&diff='.$_GET['diff'].'&key='.$_GET['key'].'&rank='.$_GET['rank'].'&'); ?>
</div>

<?php
include_once("../include/footer.php");
?>
