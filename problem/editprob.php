<?php
// 显示题目的名称、题面、评测参数和数据包表单，可从外站题面生成初稿。
// problem/editprob.php?action=add 新建；?action=edit&pid=1 载入已有题目。
require_once(__DIR__.'/../include/header.php');
$uid = (int)$_SESSION['ID'];
$pid = (int)($_GET['pid'] ?? 0);                         // 没有题号时打开投稿表单。
$action = $_GET['action'] ?? ($pid ? 'edit' : 'add');
gethead(7, 'sess', $pid ? '修改题目' : '新建题目');  // 编辑草稿和投稿都要求已登录。
if ($pid) {
    $problem = Problem::byId($pid)[0] ?? null;             // 作者也可以编辑自己的未开放题。
    if (!$problem) 异常('无此题目！！', 取路径('problem/index.php'));
    if (!Privilege::allows('查看题目') && !Privilege::allows('修改题目') && $uid !== (int)$problem['addid']) {
        异常('没有阅读权限！', 取路径('problem/index.php'));  // 普通账号只能通过本表单打开自己创建的题。
    }
} else {
    // 新题采用站点的评测默认值，以草稿状态打开，数据包稍后由投稿人选择。
    $problem = ['probname'=>'', 'filename'=>'', 'submitable'=>0, 'datacnt'=>10,
        'timelimit'=>($SET['prob_deftimelimit'] ?: 1000), 'memorylimit'=>($SET['prob_defmemorylimit'] ?: 128),
        'difficulty'=>($SET['prob_defdifficulty'] ?: 2), 'readforce'=>0, 'plugin'=>1, 'group'=>0, 'detail'=>''];
    // 样例按原有空格排版，其余段落可用富文本编辑器直接输入。
    foreach (['题目描述'=>'p','输入格式'=>'p','输出格式'=>'p','样例输入'=>'pre','样例输出'=>'pre','提示'=>'p','来源'=>'p'] as $heading=>$tag) {
        $problem['detail'] .= "<h3>【{$heading}】</h3><{$tag}>在此键入。</{$tag}>";
    }
}
if ($action === 'del') {
    // 老书签指向未实现的删除动作，提供可用的题库入口。
    异常('题库不支持删除题目。', 取路径('problem/index.php'));
}
if (!empty($_GET['oj'])) {
    // 外站内容只填入当前表单，须由投稿人确认后才写入题库。
    try { $problem = ProblemSource::draft($_GET['oj'], (int)($_GET['id'] ?? 0)); }
    catch (DomainException $error) { 异常(h($error->getMessage())); }
}
// 编辑器使用当前题目或刚生成的外站草稿作为初始值。
renderEditor('detail');
?>
<script>
function checkName(field, endpoint, message) {
    $.get(endpoint, {name: $(field).val()}, function (count) {
        $(message).html(count == 0 ? "<span style='color:blue;'>OK</span>" : "<b><span style='color:red;'>NO</span></b>");
    });
}
</script>


<div id='copy' class='modal hide fade in'>
<div class='modal-header'>
<button class='close' data-dismiss='modal'>×</button>
<h2>抄袭题目</h2>
</div>
<div class='modal-body'>
<form action='' method='get' class='form-horizontal'>
<input name='action' type='hidden' value='<?=h($action)?>' />
<input name='pid' type='hidden' value='<?=$pid?>' />
<div class='control-group'>
<label class='control-label' for='oj'>在线系统</label>
<div class='controls'>
<select id='oj' name='oj' class='input-medium'>
<?php foreach (ProblemSource::sources() as $key=>$rule) { ?>
<option value="<?=h($key)?>" <?=($_GET['oj'] ?? '') === $key ? 'selected' : ''?>><?=h($rule['name'])?></option>
<?php } ?>
</select>
</div>
</div>
<div class='control-group'>
<label class='control-label' for='id'>题目编号</label>
<div class='controls'>
<input id='id' name='id' type='number' class='input-medium' value='<?=(int)($_GET['id'] ?? 1000)?>' />
</div>
</div>
<div class='modal-footer'>
<button data-dismiss='modal' class='btn'>取消</button>
<button type="submit" class='btn btn-primary'>载入</button>
</div>
</div>
</form>
</div>

<div class='row-fluid'>
<form action="doeditprob.php" method="post" enctype="multipart/form-data" class='form-horizontal' id="tijiao"><?=formTokenField()?>
<div id='cates' class='modal hide fade in' style='width:940px; margin-left:-470px;'>
<?php
// 以分类编号查是否已勾选，显示时仍按全站分类名顺序排列。
$selectedCategories = $pid ? array_column(Category::forProblem($pid), 'caid', 'caid') : [];
$categories = Category::all();
if ($categories) {
?>
<div class='modal-header'>
<button class='close' data-dismiss='modal'>×</button>
<h3>题目分类</h3>
</div>
<div class='modal-body'>
<ul class='nav nav-pills'>
<?php
foreach ($categories as $category) {
    $selected = isset($selectedCategories[$category['caid']]);
?>
<li style='width: 120px'>
<input name="cate[<?=$category['caid']?>]" type="hidden" value="0" />
<label class='checkbox inline'>
<input name="cate[<?=$category['caid']?>]" type="checkbox" id="cate[<?=$category['caid']?>]" value="1" <?php if ($selected) echo 'checked="checked"';?>/>
<label <?php if ($selected) echo 'class="label"';?>><?=h($category['cname'])?></label>
</label>
</li>
<?php  } ?>
</ul>
</div>
<?php  } ?>
<div class='modal-footer'>
<button data-dismiss='modal' class='btn'>关闭</button>
</div>
</div>
<div class='row'>
<div class='span6'>
<div class='control-group'>
<label class='control-label' for='pid'>PID</label>
<div class='controls'>
<span id='pid' class='uneditable-input span2' ><?=$pid ?: '新建题目'?></span>
<input name='pid' type='hidden' value='<?=$pid?>' />
<input name="action" type="hidden" id="action" value="<?=h($action)?>" />
<a class='btn pull-right' href="#copy" data-toggle='modal'><i class='icon-barcode'></i>抄袭题目</a>
</div>
</div>
<div class='control-group'>
<label class='control-label' for='probname'>题目名称</label>
<div class='controls'>
<input name="probname" type="text" id="probname" onchange="checkName('#probname', 'checkprobname.php', '#msg1')" value="<?=h($problem['probname'])?>" />
<span class='help-inline' id="msg1"></span>
</div>
</div>
<div class='control-group'>
<label class='control-label' for='filename'>文件名称</label>
<div class='controls'>
<input name="filename" type="text" id="filename" onchange="checkName('#filename', 'checkfilename.php', '#msg2')" value="<?=h($problem['filename'])?>" />
<span class='help-inline' id="msg2"></span>
</div>
</div>
<div class='control-group'>
<label class='control-label' for='timelimit'>时间限制</label>
<div class='controls'>
<div class='input-append'>
<input name="timelimit" type="number" id="timelimit" value="<?=$problem['timelimit'] ?>" /><span class='add-on'>ms</span>
</div>
</div>
</div>
<div class='control-group'>
<label class='control-label' for='memorylimit'>内存限制</label>
<div class='controls'>
<div class='input-append'>
<input name="memorylimit" type="number" id="memorylimit" value="<?=$problem['memorylimit'] ?>" /><span class='add-on'>MiB</span>
</div>
</div>
</div>
<div class='control-group'>
<div class='controls'>
<a class='btn btn-success' href="#cates" data-toggle='modal'><i class='icon-tags icon-white'></i>编辑分类</a>
<button type="submit" class='btn btn-primary'>提交题目修改</button>
</div>
</div>
</div>
<div class='span6'>
<div class='control-group'>
<label class='control-label' for='difficulty'>难度等级</label>
<div class='controls'>
<input name="difficulty" type="number" id="difficulty" value="<?=$problem['difficulty'] ?>" class='span4' />
</div>
</div>
<div class='control-group'>
<?php  if(Privilege::allows('修改题目')) { ?>
<label class='control-label' for='readforce'>阅读权限</label>
<div class='controls'>
<input name="readforce" type="number" id="readforce" value="<?=$problem['readforce'] ?>" class='span4' />
<span class='help-inline'><label class='checkbox inline'>
<input name="submitable" type="checkbox" id="submitable" value="1" <?php if ($problem['submitable']) echo 'checked="checked"'; ?> />
可否提交</label></span>
</div>
<?php  } ?>
</div>
<div class='control-group'>
<label class='control-label' for='plugin'>评测方式</label>
<div class='controls'>
<select name="plugin" id="plugin">
<?php  for($i=-1; $i<5; $i++) {
    echo "<option value='$i' ";
    if($problem['plugin']==$i) echo "selected='selected'";
    echo " >{$STR['plugin'][$i]}</option>";
} ?>
</select>
</div>
</div>
<div class='control-group'>
<label class='control-label' for='group'>开放分组</label>
<div class='controls'>
<select name="group" id="group">
<?php
// 选中当前开放分组；在一个分组内发布的题遵循它的阅读范围。
foreach (UserGroup::all() as $group) {
    echo "<option value='{$group['gid']}' ";
    if($problem['group']==$group['gid']) echo "selected='selected'";
    echo " >".h($group['gname'])."</option>";
}
?>
</select>
</div>
</div>
<div class='control-group'>
<label class='control-label' for='datacnt'>测试数据</label>
<div class='controls'>
<input name="datacnt" type="number" id="datacnt" value="<?=$problem['datacnt'] ?>" class='span4' />
<input type="file" name="datafile" id="datafile" />
</div>
</div>
</div>
</div>
<div class='row'>
<div class='span12'>
<div class='control-group'>
<label class='control-label' for='detail'>题目内容
</label>
<div class='controls'>
<textarea id="detail" name="detail" class='pagearea'><?=htmlspecialchars($problem['detail'])?></textarea>
</div>
</div>
</div>
</form>
</div>
</div>
<?php
include_once(__DIR__.'/../include/footer.php');
?>
