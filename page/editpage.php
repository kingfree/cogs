<?php
require_once("../include/stdhead.php");
gethead(1,"修改页面","修改页面");
$LIB->editor("editor_id");
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
<p>
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
    else echo '<script>document.location="../error.php?id=12"</script>';
}
?>
<form action="doeditpage.php" method="post" class='form-inline'>
<table class='table-form'>
<tr>
<td width="60px">AID</td>
<td><?=$d['aid'] ?>
<input name="aid" type="hidden" id="aid" value="<?php echo $d['aid'] ?>" />
</td>
<tr>
<td>页面标题</td>
<td><input name="title" type="text" id="title" value="<?=$d['title'] ?>" /></td>
</tr>
<tr>
<td>阅读权限</td>
<td><input name="force" type="number" id="force" value="<?=$d['force'] ?>" /> </td>
</tr>
<tr>
<td>开放分组</td>
<td><select name="group" id="group">
<?php
$sql="select * from groups order by gname";
$c=$q->dosql($sql);
for ($j=0;$j<$c;$j++) {
$e=$q->rtnrlt($j);
?>
<option value="<?php echo $e['gid'] ?>" <?php if($e['gid']==$d['group']) echo 'selected="selected"' ?>>[<?php echo $e['gname'] ?>]</option>
<?php } ?>
</select></td>
</tr>
<tr>
<td>提交修改</td>
<td><button type="submit" class='btn btn-primary'>单击此处提交对该页面的修改</button>
<input name="action" type="hidden" id="action" value="<?=$_GET['action']?>" />
</td>
</tr>
<tr>
<td valign="top">页面内容</td>
<td>
<textarea id="editor_id" name="text" style="width:100%; height:500px;"><?=$dtext?></textarea>
</td>
</tr>
</table>
</form>

<?php
include_once("../include/stdtail.php");
?>

