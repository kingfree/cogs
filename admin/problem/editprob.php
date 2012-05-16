<?php
require_once("../../include/stdhead.php");
gethead(1,"修改题目","修改题目");
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

<script charset="utf-8" src="../../include/kindeditor/kindeditor-min.js"></script>
<script charset="utf-8" src="../../include/kindeditor/lang/zh_CN.js"></script>
<script>
        var editor;
        KindEditor.ready(function(K) {
                editor = K.create('#detail', {
                        themeType : 'simple'
                });
        });
</script>

<p>
<?php
if ($_GET[action]=='del')
{
echo "确认要删除该题目及与该题目相关所有内容吗(无法恢复)？<p><a href='doeditprob.php?action=del&pid={$_GET[pid]}'>确认删除</a>";
exit;
}
$p=new DataAccess();
$q=new DataAccess();
if ($_GET[action]=='edit')
{
$sql="select * from problem where pid={$_GET[pid]}";
$cnt=$p->dosql($sql);
}
if ($cnt) {
$d=$p->rtnrlt(0);
$d['detail'];
} else {
if ($_GET[action]=='add') {
$d=array();
$d['submitable']=1;
$d['datacnt']=10;
$d['timelimit']=1000;
$d['memorylimit']=128;
$d['difficulty']=2;
$d['readforce']=0;
$d['plugin']=1;
$d['group']=0;
$d['detail']="请在此键入题目内容";
}
else echo '<script>document.location="../../error.php?id=12"</script>';
}
?>
<form id="form1" name="form1" action="doeditprob.php" method="post">
<table width="100%" border="1" bordercolor=#000000 cellspacing=0 cellpadding=4>
<tr>
<td width="80px" valign="top" scope="col">PID</td>
<td scope="col"><?php echo $d[pid] ?>
<input name="pid" type="hidden" id="pid" value="<?php echo $d['pid'] ?>" /></td>
<th valign="top" scope="col">题目分类</th>
</tr>
<tr>
<td valign="top">题目名称</td>
<td><input name="probname" type="text" id="probname" onchange="checkprobname()" value="<?php echo $d[probname] ?>" class="InputBox" /><span id="msg1"></span></td>
<td rowspan=9 valign="top">
<?php
if ($_GET[pid]) {
    $sql="select caid from tag where pid={$_GET[pid]}";
    $cnt=$p->dosql($sql);
    for ($i=0;$i<=$cnt-1;$i++) {
        $f=$p->rtnrlt($i);
        $hash[$f[caid]]=true;
    }
}
$sql="select * from category order by cname";
$cnt=$p->dosql($sql);
if ($cnt) {
    $table_width=5;
?>
    <table border="1" id="bc">
    <tr>
    <?php
    $last=0;
$linecnt=0;
$line=1;
for ($i=0;$i<$cnt;$i++) {
$f=$p->rtnrlt($i);
$last=$f['pid'];
$linecnt++;
?>
<td><input name="cate[<?php echo $f[caid] ?>]" type="hidden" value="0" />
<input name="cate[<?php echo $f[caid] ?>]" type="checkbox" id="cate[<?=$f[caid]?>]" value="1" 
<?php if ($hash[$f[caid]]) echo 'checked="checked"';?>  /><label for="cate[<?=$f[caid]?>]"> <?php echo $f['cname'] ?></label></td>
<?php
if ($linecnt==$table_width) {
    $linecnt=0;
    $line++;
    echo "</tr></tr>";
}
}
if ($linecnt>0 && $line>1)
    for ($i=$linecnt;$i<$table_width;$i++)
    echo "<td>&nbsp;</td>";
?>
</tr>
</table>
<?php
}
?>
</td></tr>
<tr>
<td valign="top">文件名称</td>
<td><input name="filename" type="text" id="filename" onchange="checkfilename()" value="<?php echo $d[filename] ?>" /><span id="msg2"></span></td>
</tr>
<tr>
<td valign="top">阅读权限</td>
<td><input name="readforce" type="number" id="readforce" value="<?php echo $d['readforce'] ?>" /></td>
</tr>
<tr>
<td valign="top">可提交</td>
<td><input name="submitable" type="checkbox" id="submitable" value="1" <?php if ($d['submitable']) echo 'checked="checked"'; ?> /></td>
</tr>
<tr>
<td valign="top">测点数目</td>
<td><input name="datacnt" type="number" id="datacnt" value="<?php echo $d[datacnt] ?>" />
<!--【暂不可用】测试数据打包zip(文件包含一个以该题目命名的文件夹，其中为in和ans数据)：
<input type="file" name="file" id="file" class="Button"/>
<input type="hidden" name="MAX_FILE_SIZE" value="102400">
-->
</td>
</tr>
<tr>
<td valign="top">时间限制</td>
<td><input name="timelimit" type="number" id="timelimit" value="<?php echo $d[timelimit] ?>" /> ms</td>
</tr>
<tr>
<td valign="top">内存限制</td>
<td><input name="memorylimit" type="number" id="memorylimit" value="<?php echo $d['memorylimit'] ?>" /> MiB</td>
</tr>
<tr>
<td valign="top">难度等级</td>
<td><input name="difficulty" type="number" id="difficulty" value="<?php echo $d['difficulty'] ?>" /></td>
</tr>
<tr>
<td valign="top">开放分组</td>
<td><select name="group" id="group">
<?php
$sql="select * from groups order by gname";
$c=$q->dosql($sql);
for ($j=0;$j<$c;$j++)
{
$e=$q->rtnrlt($j);
?>
<option value="<?php echo $e['gid'] ?>" <?php if($e['gid']==$d['group']) echo 'selected="selected"' ?>>[<?php echo $e['gname'] ?>]</option>
<?php }?>
</select></td>
</tr>
<tr>
<td valign="top">对比方式</td>
<td><select name="plugin" id="plugin">
<option value="-1"<?php if ($d['plugin']==-1){ ?> selected="selected"<?php } ?>>交互式</option>
<option value="1"<?php if ($d['plugin']==1){ ?> selected="selected"<?php } ?>>简单对比</option>
<option value="2"<?php if ($d['plugin']==2){ ?> selected="selected"<?php } ?>>逐字节对比</option>
<option value="0"<?php if ($d['plugin']==0){ ?> selected="selected"<?php } ?>>评测插件</option>
</select>                </td>
<td class=admin>提交修改：
<input type="submit" value="单击此处提交对该题目的修改" />
<input name="action" type="hidden" id="action" value="<?php echo $_GET[action] ?>" />
</td>
</tr>
<tr>
<td valign="top">题目内容</td>
<td colspan=2>
<textarea id="detail" name="detail" style="width:100%; height:400px;"><?=$d['detail']?></textarea>
</td>
</tr>
</table>
</form>

<?php
include_once("../../include/stdtail.php");
?>
