<?php
require_once("../../include/stdhead.php");
gethead(1,"修改题目","修改题目");
$LIB->editor("detail");
$LIB->htmldom();
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
if ($_GET['action']=='add') {
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
if($_GET['oj']=='poj' && $_GET['id']>=1000) {
    $d=array();
    $url="http://www.poj.org/problem?id=".$_GET['id']."&lang=zh-CN&change=true";
    if(get_magic_quotes_gpc()) {
        $url=stripslashes($url);
    }
    $baseurl=substr($url,0,strrpos($url,"/")+1);
    $html=file_get_html($url);
    foreach($html->find('img') as $element)
        $element->src=$baseurl.$element->src;

    $element=$html->find('div[class=ptt]',0);
    $d['probname']=$element->plaintext;
    $d['filename']="poj_".$_GET['id'];
    $d['submitable']=1;

    $element=$html->find('div[class=plm]',0);
    $tlimit=$element->find('td',0);
    $tlimit=substr($tlimit->plaintext,11);
    $tlimit=substr($tlimit,0,strlen($tlimit)-2);
    $d['timelimit']=(int)$tlimit;
    $mlimit=$element->find('td',2);
    $mlimit=substr($mlimit->plaintext,13);
    $mlimit=substr($mlimit,0,strlen($mlimit)-1);
    $d['memorylimit']=(int)($mlimit/1024);

    $d['datacnt']=10;
    $d['difficulty']=2;
    $d['readforce']=0;
    $d['plugin']=1;
    $d['group']=0;

    $d['detail']="";
    $element=$html->find('div[class=ptx]',0);
    $d['detail'].="<h3>【题目描述】</h3>".$element->outertext;
    $element=$html->find('div[class=ptx]',1);
    $d['detail'].="<h3>【输入格式】</h3>".$element->outertext;
    $element=$html->find('div[class=ptx]',2);
    $d['detail'].="<h3>【输出格式】</h3>".$element->outertext;
    $element=$html->find('pre[class=sio]',0);
    $d['detail'].="<h3>【样例输入】</h3><pre>".$element->outertext."</pre>";
    $element=$html->find('pre[class=sio]',1);
    $d['detail'].="<h3>【样例输出】</h3><pre>".$element->outertext."</pre>";
    $element=$html->find('div[class=ptx]',3);
    $d['detail'].="<h3>【提示】</h3>".$element->outertext;
    $element=$html->find('div[class=ptx]',4);
    $d['detail'].="<h3>【来源】</h3>".$element->outertext;
} else if($_GET['oj']=='soj' && $_GET['id']>=1000) {
    $d=array();
    $url="http://www.soj.me/show_problem.php?pid=".$_GET['id'];
    if(get_magic_quotes_gpc()) {
        $url=stripslashes($url);
    }
    $baseurl=substr($url,0,strrpos($url,"/")+1);
    $html=file_get_html($url);
    foreach($html->find('img') as $element)
        $element->src=$baseurl.$element->src;

    $element=$html->find('div[class=cent]',0);
    $pname=ltrim($element->plaintext);
    $pname=rtrim($pname);
    $pname=substr($pname,6);
    $d['probname']=$pname;
    $d['filename']="soj_".$_GET['id'];
    $d['submitable']=1;

    $element=$html->find('div[class=rtbar]',0);
    $limit=ltrim($element->plaintext);
    $tlimit=substr($limit,stripos($limit,"Time Limit:")+11);
    $mlimit=substr($limit,stripos($limit,"Memory Limit:")+13);
    $tlimit=substr($tlimit,0,stripos($tlimit,"sec"));
    $mlimit=substr($mlimit,0,stripos($mlimit,"MB"));
    $d['timelimit']=(int)($tlimit) * 1000;
    $d['memorylimit']=(int)($mlimit);

    $d['datacnt']=10;
    $d['difficulty']=2;
    $d['readforce']=0;
    $d['plugin']=1;
    $d['group']=0;

    $d['detail']="";
    $element=$html->find('div[class=description]',0);
    $d['detail'].="<h3>【题目描述】</h3>".$element->outertext;
    $element=$html->find('div[class=description]',1);
    $d['detail'].="<h3>【输入格式】</h3>".$element->outertext;
    $element=$html->find('div[class=description]',2);
    $d['detail'].="<h3>【输出格式】</h3>".$element->outertext;
    $element=$html->find('pre',0);
    $d['detail'].="<h3>【样例输入】</h3>".str_replace("  ","\n",$element->outertext)."";
    $element=$html->find('pre',1);
    $d['detail'].="<h3>【样例输出】</h3>".str_replace("  ","\n",$element->outertext)."";
}

}
?>
<form action='' method='get' class='form-inline'>
从POJ抄题：
<input name='action' type='hidden' value='add' />
<input name='oj' type='hidden' value='poj' />
<input name='id' type='number' value='<?=$_GET['id']?$_GET['id']:1000?>' />
<button type='submit' class='btn'>载入</button>
</form>
<form action='' method='get' class='form-inline'>
从SOJ抄题：
<input name='action' type='hidden' value='add' />
<input name='oj' type='hidden' value='soj' />
<input name='id' type='number' value='<?=$_GET['id']?$_GET['id']:1000?>' />
<button type='submit' class='btn'>载入</button>
</form>


<form action="doeditprob.php" method="post" enctype="multipart/form-data" class='form-inline'>
<table class='table-form fixed'>
<tr>
<th width="80px">PID</th>
<td width="280px"><?php echo $d['pid'] ?>
<input name="pid" type="hidden" id="pid" value="<?php echo $d['pid'] ?>" /></td>
<th>题目分类</th>
</tr>
<tr>
<th>题目名称</th>
<td><input name="probname" type="text" id="probname" onchange="checkprobname()" value="<?php echo $d['probname'] ?>" /><span id="msg1"></span></td>
<td rowspan='9' valign="top">
<?php
if ($_GET['pid']) {
    $sql="select caid from tag where pid={$_GET[pid]}";
    $cnt=$p->dosql($sql);
    for ($i=0;$i<=$cnt-1;$i++) {
        $f=$p->rtnrlt($i);
        $hash[$f['caid']]=true;
    }
}
$sql="select * from category order by cname";
$cnt=$p->dosql($sql);
if($cnt) {
?>
<table>
<?php
$last=0;
$linecnt=0;
$line=1;
for ($i=0;$i<$cnt;$i++) {
$f=$p->rtnrlt($i);
$last=$f['pid'];
if($i % 5 == 0) echo "<tr>";
?>
<td><input name="cate[<?=$f['caid']?>]" type="hidden" value="0" />
<input name="cate[<?=$f['caid']?>]" type="checkbox" id="cate[<?=$f[caid]?>]" value="1" <?php if ($hash[$f['caid']]) echo 'checked="checked"';?> /><label for="cate[<?=$f['caid']?>]"> <?=$f['cname']?>&nbsp;</label></td>
<? } ?>
</table>
<? } ?>
</td></tr>
<tr>
<th>文件名称</th>
<td><input name="filename" type="text" id="filename" onchange="checkfilename()" value="<?php echo $d[filename] ?>" /><span id="msg2"></span></td>
</tr>
<tr>
<th>阅读权限</th>
<td><input name="readforce" type="number" id="readforce" value="<?php echo $d['readforce'] ?>" /></td>
</tr>
<tr>
<th>可否提交</th>
<td><input name="submitable" type="checkbox" id="submitable" value="1" <?php if ($d['submitable']) echo 'checked="checked"'; ?> /></td>
</tr>
<tr>
<th>对比方式</th>
<td><select name="plugin" id="plugin">
<option value="-1"<?php if ($d['plugin']==-1){ ?> selected="selected"<?php } ?>>交互式</option>
<option value="1"<?php if ($d['plugin']==1){ ?> selected="selected"<?php } ?>>简单对比</option>
<option value="2"<?php if ($d['plugin']==2){ ?> selected="selected"<?php } ?>>逐字节对比</option>
<option value="0"<?php if ($d['plugin']==0){ ?> selected="selected"<?php } ?>>评测插件</option>
</select>                </td>
</tr>
<tr>
<th>时间限制</th>
<td><input name="timelimit" type="number" id="timelimit" value="<?php echo $d[timelimit] ?>" /> ms</td>
</tr>
<tr>
<th>内存限制</th>
<td><input name="memorylimit" type="number" id="memorylimit" value="<?php echo $d['memorylimit'] ?>" /> MiB</td>
</tr>
<tr>
<th>难度等级</th>
<td><input name="difficulty" type="number" id="difficulty" value="<?php echo $d['difficulty'] ?>" /></td>
</tr>
<tr>
<th>开放分组</th>
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
<th>测试点数</th>
<td><input name="datacnt" type="number" id="datacnt" value="<?php echo $d[datacnt] ?>" /></td>
<td>
<button type="submit" class='btn btn-primary'>单击此处提交对该题目的修改</button>
<input name="action" type="hidden" id="action" value="<?php echo $_GET[action] ?>" />
</td>
<tr><th>测试数据</th>
<td colspan='2'>
<input type="file" name="datafile" id="datafile" />
打包zip文件包含一个以该题目*命名的文件夹，其中为*#.in和*#.ans数据，*.cc为评测插件
</td></tr>
</tr>
<tr>
<th valign="top">题目内容</th>
<td colspan='2'>
<textarea id="detail" name="detail" style="width:100%; height:400px;"><?=$d['detail']?></textarea>
</td>
</tr>
</table>
</form>

<?php
include_once("../../include/stdtail.php");
?>
