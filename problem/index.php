<?php
// 显示当前读者可访问的题库，按分类、关键字或难度筛选。
// problem/index.php?caid=1&key=sum 会在指定分类里查找匹配的题目。
require_once(__DIR__.'/../include/header.php');
$categoryId = (int)($_GET['caid'] ?? 0);
$key = $_GET['key'] ?? '';                          // 分类、检索词和难度可叠加到同一次搜索。
$difficulty = $_GET['diff'] ?? '';
$sort = $_GET['rank'] ?? 'pid';
$order = $_GET['order'] ?? 'asc';                   // 排序列和方向由 Problem::search 限定。
$page = (int)($_GET['page'] ?? 1);
$category = $categoryId ? (Category::byId($categoryId)[0] ?? null) : null;
$heading = $category['cname'] ?? $key;              // 分类页优先用分类名做标题。
gethead(1, '', ($heading !== '' ? $heading.' - ' : '').'题目列表');
$mySubmissions = Submission::bestByProblem($_SESSION['ID']);
?>

<div class='row-fluid'>


<?php if(Privilege::allows('普通用户')) { ?>
<a href="editprob.php?action=add" class="btn btn-info pull-left">添加新题目</a>
<?php } ?>
<a href="catelist.php" class='btn btn-success pull-left'><i class="icon-tags icon-white"></i>题目分类列表</a>

<?php  if ($category) { ?>
<span id="cate_detail"> 当前分类：
<?php if(Privilege::allows('分类管理')) { ?>
<a href="editcate.php?action=edit&caid=<?php echo $category['caid'] ?>">
<?php } ?>
<span class="big"><?=h($category['cname'])?></span>
<?php if(Privilege::allows('分类管理')) { ?>
</a>
<?php } ?>
（<?php echo nl2br(sp2n(htmlspecialchars($category['memo']))) ?>）
</span>
<?php } ?>

<?php
$problems = Problem::search($categoryId, $key, $difficulty, $sort, $order);
$total = count($problems);                          // 用同一结果集计算总页数和这一页的切片。
$offset = 检测页面($total, $page);
?>
<form method="get" action="" class='form-search center'>
<a href="random.php" title="随机选择一道你没有通过的题目" class='btn btn-danger' >随机题目<i class='icon icon-random icon-white'></i></a>
<input name="caid" type="hidden" value="<?=$categoryId?>" />
<input name="order" type="hidden" value="<?=$order === 'asc' ? 'desc' : 'asc'?>" />
难度 
<select name="diff" onchange="this.form.submit()" class='input-medium'>
<option value="" <?php if ($difficulty === '') {?> selected="selected"<?php } ?> >全部</option>
<?php for ($i=1;$i<=10;$i++) { ?>
<option value="<?php echo $i;?>" <?php if ($difficulty == $i) {?> selected="selected"<?php } ?> ><?php echo 难度($i);?></option>
<?php } ?>
</select>
<div class='input-append pull-right'>
<input name="key" type="text" class='search-query input-medium' value='<?=h($key)?>' title='输入关键字按回车搜索题目' placeholder='搜索题目'/>
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
<?php if(Privilege::allows('修改题目')) { ?>
<button name="rank" class='btn' value='submitable'>标识</button>
<?php  } ?>
</div>
</form>
<table id="problist" class='table table-striped table-condensed table-bordered fiexd'>
<thead><tr>
<th style="min-width: 4ex;" onclick="sortTable('problist', 0, 'int')">PID</th>
<th onclick="sortTable('problist', 1, 'string')">题目名称</th>
<th>文件名称</th>
<th>时间</th>
<th>空间</th>
<th>难度</th>
<th>评测方式</th>
<th onclick="sortTable('problist', 7, 'int')">通过</th>
<th onclick="sortTable('problist', 8, 'int')">提交</th>
<th onclick="sortTable('problist', 9, 'float')">通过率</th>
<?php if(Privilege::allows('修改题目')) { ?>
<th class=admin style="min-width: 4ex;"><i class='icon icon-question-sign'></i></th>
<?php } ?>
</tr></thead>
<?php
// 单条搜索结果可直接进入题面；其他情况按页显示列表。
foreach (array_slice($problems, $offset, $SET['style_pagesize']) as $problem) {
    if ($key !== '' && $total === 1)
        echo "<script language='javascript'>location='problem.php?pid={$problem['pid']}';</script>";
?>
<tr>
<td><?php echo $problem['pid'] ?></td>
<td>
<?php if(Privilege::allows('修改题目')) { ?>
<a href="editprob.php?action=edit&pid=<?=$problem['pid']?>" title="修改题目 <?=h($problem['probname'])?>" class="pull-right"><i class="icon icon-edit"></i></a>
<?php } ?>
<?php  是否通过($mySubmissions[$problem['pid']] ?? null);?>
<b><a href="problem.php?pid=<?=$problem['pid'] ?>"><?=h($problem['probname'])?></a></b>
</td>
<td><code><?=h($problem['filename'])?></code></td>
<td><?php echo $problem['timelimit']/1000 . " s"; ?></td>
<td><?php echo $problem['memorylimit'] . " MB"; ?></td>
<td><?php echo 难度($problem['difficulty']); ?></td>
<td><?=$STR['plugin'][$problem['plugin']]?></td>
<td><?php echo $problem['acceptcnt']; ?></td>
<td><?php echo $problem['submitcnt']; ?></td>
<td><?php echo ($problem['submitcnt'] ? round($problem['acceptcnt']/$problem['submitcnt']*100,2) : 0); ?>%</td>
<?php if(Privilege::allows('修改题目')) { ?>
<td class="center">
<a href="doeditprob.php?action=change&pid=<?=$problem['pid']?>" title="更改题目 <?=shortname($problem['probname'])?> 的可用性" onclick="return confirm('更改该题的提交属性？')">
<?php if ($problem['submitable']) echo "<span class='label label-success'><i class='icon icon-ok-circle icon-white'></i></span>"; else echo "<span class='label label-important'><i class='icon icon-ban-circle icon-white'></i></span>"; ?>
</a>
</td>
<?php } ?>
</tr>
<?php } ?>
</table>
<?php
// 页码由分页组件追加，保留分类、关键字、难度和排序。
$query = http_build_query(['caid'=>$categoryId,'order'=>$order,'diff'=>$difficulty,'key'=>$key,'rank'=>$sort]);
分页($total, $page, '?'.$query.'&');
?>
</div>

<?php
include_once(__DIR__.'/../include/footer.php');
?>
