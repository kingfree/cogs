<?php
require_once("../include/header.php");
$uid = (int) $_SESSION['ID'];
if($pid = (int) $_GET['pid'])
    gethead(7,"sess","修改题目");
else
    gethead(7,"sess","新建题目");
if($pid) {
    $pRows = Problem::byId($pid);
    $cnt = count($pRows);
    if($cnt) {
        $d=($pRows[0] ?? false);
        if(!Privilege::allows("查看题目") && $uid != $d['addid'])
            异常("没有阅读权限！", 取路径("problem/index.php"));
    } else {
        异常("无此题目！！", 取路径("problem/index.php"));
    }
}
renderEditor("detail");

?>
<script>
function checkprobname(){
var probname = $("#probname").val();
$.get("checkprobname.php",{name: probname},function(txt){
if(txt == 0){$("#msg1").html("<span style='color:blue;'>OK</span>");}
else {$("#msg1").html("<b><span style='color:red;'>NO</span></b>");}
});
}
function checkfilename(){
var filename = $("#filename").val();
$.get("checkfilename.php",{name: filename},function(txt){
if(txt == 0){$("#msg2").html("<span style='color:blue;'>OK</span>");}
else {$("#msg2").html("<b><span style='color:red;'>NO</span></b>");}
});
}
</script>

<?php
if ($_GET['action']=='del') {
    echo "确认要删除该题目及与该题目相关所有内容吗(无法恢复)？<p><a href='doeditprob.php?action=del&pid={$_GET['pid']}'>确认删除</a>";
    exit;
}
if (!$cnt) {
    if ($_GET['action']=='add') {
        $d=array();
        $d['submitable']=0;
        $d['datacnt']=10;
        $d['timelimit']=$SET['prob_deftimelimit'] ? $SET['prob_deftimelimit'] : 1000;
        $d['memorylimit']=$SET['prob_defmemorylimit'] ? $SET['prob_defmemorylimit'] : 128;
        $d['difficulty']=$SET['prob_defdifficulty'] ? $SET['prob_defdifficulty'] : 2;
        $d['readforce']=0;
        $d['plugin']=1;
        $d['group']=0;
        $d['detail']="";
        $d['detail'].="<h3>【题目描述】</h3>";
        $d['detail'].="<p>在此键入。</p>";
        $d['detail'].="<h3>【输入格式】</h3>";
        $d['detail'].="<p>在此键入。</p>";
        $d['detail'].="<h3>【输出格式】</h3>";
        $d['detail'].="<p>在此键入。</p>";
        $d['detail'].="<h3>【样例输入】</h3>";
        $d['detail'].="<pre>在此键入。</pre>";
        $d['detail'].="<h3>【样例输出】</h3>";
        $d['detail'].="<pre>在此键入。</pre>";
        $d['detail'].="<h3>【提示】</h3>";
        $d['detail'].="<p>在此键入。</p>";
        $d['detail'].="<h3>【来源】</h3>";
        $d['detail'].="<p>在此键入。</p>";
    }
}
if (!empty($_GET['oj'])) {
    try { $d = ProblemSource::draft($_GET['oj'], (int)($_GET['id'] ?? 0)); }
    catch (DomainException $error) { 异常(h($error->getMessage())); }
}
?>


<div id='copy' class='modal hide fade in'>
<div class='modal-header'>
<button class='close' data-dismiss='modal'>×</button>
<h2>抄袭题目</h2>
</div>
<div class='modal-body'>
<form action='' method='get' class='form-horizontal'>
<input name='action' type='hidden' value='<?=h($_GET['action'] ?? '')?>' />
<input name='pid' type='hidden' value='<?=h($_GET['pid'] ?? '')?>' />
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
<input id='id' name='id' type='number' class='input-medium' value='<?=$_GET['id']?$_GET['id']:1000?>' />
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
if ($pid) {
    $pRows = Category::forProblem($pid);
    $cnt = count($pRows);
    for ($i=0;$i<=$cnt-1;$i++) {
        $f=($pRows[$i] ?? false);
        $hash[$f['caid']]=true;
    }
}
$pRows = Category::all();
$cnt = count($pRows);
if($cnt) {
?>
<div class='modal-header'>
<button class='close' data-dismiss='modal'>×</button>
<h3>题目分类</h3>
</div>
<div class='modal-body'>
<ul class='nav nav-pills'>
<?php
foreach ($pRows as $i => $f) {
?>
<li style='width: 120px'>
<input name="cate[<?=$f['caid']?>]" type="hidden" value="0" />
<label class='checkbox inline'>
<input name="cate[<?=$f['caid']?>]" type="checkbox" id="cate[<?=$f['caid']?>]" value="1" <?php if ($hash[$f['caid']]) echo 'checked="checked"';?>/>
<label <?php if ($hash[$f['caid']]) echo 'class="label"';?>><?=h($f['cname'])?></label>
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
<span id='pid' class='uneditable-input span2' ><?=$_GET['pid'] ? $_GET['pid'] : "新建题目"?></span>
<input name='pid' type='hidden' value='<?=$_GET['pid'] ? $_GET['pid'] : "0"?>' />
<input name="action" type="hidden" id="action" value="<?=h($_GET['action'] ?? '')?>" />
<a class='btn pull-right' href="#copy" data-toggle='modal'><i class='icon-barcode'></i>抄袭题目</a>
</div>
</div>
<div class='control-group'>
<label class='control-label' for='probname'>题目名称</label>
<div class='controls'>
<input name="probname" type="text" id="probname" onchange="checkprobname()" value="<?=h($d['probname'])?>" />
<span class='help-inline' id="msg1"></span>
</div>
</div>
<div class='control-group'>
<label class='control-label' for='filename'>文件名称</label>
<div class='controls'>
<input name="filename" type="text" id="filename" onchange="checkfilename()" value="<?=h($d['filename'])?>" />
<span class='help-inline' id="msg2"></span>
</div>
</div>
<div class='control-group'>
<label class='control-label' for='timelimit'>时间限制</label>
<div class='controls'>
<div class='input-append'>
<input name="timelimit" type="number" id="timelimit" value="<?=$d['timelimit'] ?>" /><span class='add-on'>ms</span>
</div>
</div>
</div>
<div class='control-group'>
<label class='control-label' for='memorylimit'>内存限制</label>
<div class='controls'>
<div class='input-append'>
<input name="memorylimit" type="number" id="memorylimit" value="<?=$d['memorylimit'] ?>" /><span class='add-on'>MiB</span>
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
<input name="difficulty" type="number" id="difficulty" value="<?=$d['difficulty'] ?>" class='span4' />
</div>
</div>
<div class='control-group'>
<?php  if(Privilege::allows("查看题目")) { ?>
<label class='control-label' for='readforce'>阅读权限</label>
<div class='controls'>
<input name="readforce" type="number" id="readforce" value="<?=$d['readforce'] ?>" class='span4' />
<span class='help-inline'><label class='checkbox inline'>
<input name="submitable" type="checkbox" id="submitable" value="1" <?php if ($d['submitable']) echo 'checked="checked"'; ?> />
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
    if($d['plugin']==$i) echo "selected='selected'";
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
$groups = UserGroup::all();
$c = count($groups);
foreach ($groups as $j => $e) {
    echo "<option value='{$e['gid']}' ";
    if($d['group']==$e['gid']) echo "selected='selected'";
    echo " >".h($e['gname'])."</option>";
}
?>
</select>
</div>
</div>
<div class='control-group'>
<label class='control-label' for='datacnt'>测试数据</label>
<div class='controls'>
<input name="datacnt" type="number" id="datacnt" value="<?=$d['datacnt'] ?>" class='span4' />
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
<textarea id="detail" name="detail" class='pagearea'><?=htmlspecialchars($d['detail'])?></textarea>
</div>
</div>
</div>
</form>
</div>
</div>
<?php
//renderEditor("detail");
include_once("../include/footer.php");
?>
