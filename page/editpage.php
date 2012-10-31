<?php
require_once("../include/header.php");
gethead(1,"修改页面","修改页面");
$LIB->editor("detail");
?>

<script type = "text/javascript">
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
if ($_GET[action]=='del') {
    echo "确认要删除该题目及与该题目相关所有内容吗(无法恢复)？<p><a href='doeditpage.php?action=del&aid={$_GET[aid]}'>确认删除</a>";
    exit;
}
$p=new DataAccess();
$q=new DataAccess();
if ($_GET[action]=='edit') {
    $sql="select * from page where aid={$_GET[aid]}";
    $cnt=$p->dosql($sql);
}
if ($cnt) {
    $d=$p->rtnrlt(0);
    $dtext=$d['text'];
} else {
    if ($_GET[action]=='add') {
        $d=array();
        $d['force']=0;
        $d['group']=0;
        $dtext="请在此键入页面内容";
    }
}
?>
<div class='row-fluid'>
<form action="doeditpage.php" method="post" class='form-inline' id="tijiao">
<dl class='dl-horizontal'>
<dt>AID</dt>
<dd><?=$d['aid'] ?>
<input name="aid" type="hidden" id="aid" value="<?php echo $d['aid'] ?>" />
</dd>
<dt>页面标题</dt>
<dd><input name="title" type="text" id="title" value="<?=$d['title'] ?>"></dd>
<dt>阅读权限</dt>
<dd><input name="force" type="number" id="force" value="<?=$d['force'] ?>" /> </dd>
<dt>开放分组</dt>
<dd><select name="group" id="group">
<?php
$sql="select * from groups order by gname";
$c=$q->dosql($sql);
for ($j=0;$j<$c;$j++) {
$e=$q->rtnrlt($j);
?>
<option value="<?php echo $e['gid'] ?>" <?php if($e['gid']==$d['group']) echo 'selected="selected"' ?>>[<?php echo $e['gname'] ?>]</option>
<?php } ?>
</select></dd>
<dt>页面内容</dt>
<dd>
<textarea id="detail" name="text" class='pagearea'><?=htmlspecialchars($d['text'])?></textarea>
</dd>
<dd><button type="submit" class='btn btn-primary'>单击此处提交对该页面的修改</button>
<input name="action" type="hidden" id="action" value="<?=$_GET['action']?>" />
</dd>
</dl>
</form>
</div>

<?php
include_once("../include/footer.php");
?>

