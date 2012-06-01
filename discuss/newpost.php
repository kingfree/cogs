<?php
require_once("../include/header.php");
gethead(1,"sess","发表新帖");
$p = new DataAccess();
$q = new DataAccess();

$pid = $_REQUEST['pid'];
$cid = $_REQUEST['cid'];
?>
<script charset="utf-8" src="../include/kindeditor/kindeditor.js"></script>
<script charset="utf-8" src="../include/kindeditor/lang/zh_CN.js"></script>
<script>
        var editor;
        KindEditor.ready(function(K) {
                editor = K.create('#editor_id');
        });
</script>
<form action="post.php?action=new" method=post>
<table id='newpost'>
<tr>
<th>题目编号</th>
<td><input type=number name=pid value="<?=$pid?>"></td>
</tr>
<tr>
<th>帖子标题</th>
<td><input type=text name=title value="Discuss p<?=$pid?>"></td>
</tr>
<tr>
<th>帖子内容</th>
<td><textarea id="editor_id" name="content" style="width:90%; height:250px;"><?=$ddetail?></textarea></td>
</tr>
<tr>
<th></th>
<td><input type=submit value="发表帖子"></td>
</tr>
</table>
</form>
<?
require_once("../include/footer.php");
?>
