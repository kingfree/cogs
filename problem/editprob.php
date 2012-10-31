<?php
require_once("../include/header.php");
gethead(7,"修改题目","修改题目");
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
if ($_GET[action]=='del') {
    echo "确认要删除该题目及与该题目相关所有内容吗(无法恢复)？<p><a href='doeditprob.php?action=del&pid={$_GET[pid]}'>确认删除</a>";
    exit;
}
$p=new DataAccess();
$q=new DataAccess();
if ($_GET['action']=='edit') {
    $sql="select * from problem where pid={$_GET['pid']}";
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
    $d['detail'].="<h3>【样例输入】</h3>".str_replace("  ","\n",$element->outertext)."";
    $element=$html->find('pre[class=sio]',1);
    $d['detail'].="<h3>【样例输出】</h3>".str_replace("  ","\n",$element->outertext)."";
    $element=$html->find('div[class=ptx]',3);
    $d['detail'].="<h3>【提示】</h3>".$element->outertext;
    $element=$html->find('div[class=ptx]',4);
    $d['detail'].="<h3>【来源】</h3>".$element->outertext;
    $d['detail'].="<h3>【题目来源】</h3>"."<a href=\"".$url."\"> 北京大学 POJ ".$_GET['id']."</a>";
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
    $d['detail'].="<h3>【题目来源】</h3>"."<a href=\"".$url."\">中山大学 SOJ ".$_GET['id']."</a>";
} else if($_GET['oj']=='pojgrids' && $_GET['id']>=1000) {
    $d=array();
    $url="http://poj.grids.cn/practice/".$_GET['id'];
    if(get_magic_quotes_gpc()) {
        $url=stripslashes($url);
    }
    $baseurl=substr($url,0,strrpos($url,"/")+1);
    $html=file_get_html($url);

    foreach($html->find('img') as $element)
        $element->src=$baseurl.$element->src;

    $pname=$html->find('div[id=pageTitle]', 0)->plaintext;
    $pname=substr($pname,stripos($pname,"题目")+5);
    $d['probname']=$pname;
    $d['filename']="pojg_".$_GET['id'];
    $d['submitable']=1;

    $params=$html->find('dl[class=problem-params]',0);
    $tlimit=$params->find('dd', 0)->plaintext;
    $mlimit=$params->find('dd', 1)->plaintext;
    $tlimit=substr($tlimit,0,stripos($tlimit,"ms"));
    $mlimit=substr($mlimit,0,stripos($mlimit,"kB"));
    $d['timelimit']=(int)($tlimit);
    $d['memorylimit']=(int)($mlimit) / 1024;

    $d['datacnt']=10;
    $d['difficulty']=2;
    $d['readforce']=0;
    $d['plugin']=1;
    $d['group']=0;

    $content=$html->find('dl[class=problem-content]',0);
    $d['detail']="";
    $element=$content->find('dd',0);
    $d['detail'].="<h3>【题目描述】</h3>".$element->outertext;
    $element=$content->find('dd',1);
    $d['detail'].="<h3>【输入格式】</h3>".$element->outertext;
    $element=$content->find('dd',2);
    $d['detail'].="<h3>【输出格式】</h3>".$element->outertext;
    $element=$content->find('pre',0);
    $d['detail'].="<h3>【样例输入】</h3>".str_replace("  ","\n",$element->outertext)."";
    $element=$content->find('pre',1);
    $d['detail'].="<h3>【样例输出】</h3>".str_replace("  ","\n",$element->outertext)."";
    $d['detail'].="<h3>【题目来源】</h3>"."<a href=\"".$url."\">百练 POJ ".$_GET['id']."</a>";
} else if($_GET['oj']=='lydsy' && $_GET['id']>=1000) {
    $d=array();
    $url="http://www.lydsy.com/JudgeOnline/problem.php?id=".$_GET['id'];
    if(get_magic_quotes_gpc()) {
        $url=stripslashes($url);
    }
    $baseurl=substr($url,0,strrpos($url,"/")+1);
    $html=file_get_html($url);
    foreach($html->find('img') as $element)
        $element->src=$baseurl.$element->src;

    $element=$html->find('h2',0);
    $pname=ltrim($element->plaintext);
    $pname=rtrim($pname);
    $pname=substr($pname,6);
    $d['probname']=$pname;
    $d['filename']="bzoj_".$_GET['id'];
    $d['submitable']=1;

    $element=$html->find('center',2);
    $limit=ltrim($element->plaintext);
    $tlimit=substr($limit,stripos($limit,"Time Limit:")+11);
    $mlimit=substr($limit,stripos($limit,"Memory Limit:")+13);
    $tlimit=substr($tlimit,0,stripos($tlimit,"Sec"));
    $mlimit=substr($mlimit,0,stripos($mlimit,"MB"));
    $d['timelimit']=(int)($tlimit) * 1000;
    $d['memorylimit']=(int)($mlimit);

    $d['datacnt']=10;
    $d['difficulty']=2;
    $d['readforce']=0;
    $d['plugin']=1;
    $d['group']=0;

    $d['detail']="";
    $element=$html->find('div[class=content]',0);
    $d['detail'].="<h3>【题目描述】</h3>".$element->outertext;
    $element=$html->find('div[class=content]',1);
    $d['detail'].="<h3>【输入格式】</h3>".$element->outertext;
    $element=$html->find('div[class=content]',2);
    $d['detail'].="<h3>【输出格式】</h3>".$element->outertext;
    $element=$html->find('span[class=sampledata]',0);
    $d['detail'].="<h3>【样例输入】</h3><pre>".$element->plaintext."</pre>";
    $element=$html->find('span[class=sampledata]',1);
    $d['detail'].="<h3>【样例输出】</h3><pre>".$element->plaintext."</pre>";
    $element=$html->find('div[class=content]',5);
    $d['detail'].="<h3>【提示】</h3>".$element->outertext;
    $element=$html->find('div[class=content]',6);
    $d['detail'].="<h3>【来源】</h3>".$element->outertext;
    $d['detail'].="<h3>【题目来源】</h3>"."<a href=\"".$url."\">耒阳大世界（衡阳八中） OJ ".$_GET['id']."</a>";
} else if($_GET['oj']=='uva' && $_GET['id']) {
    $d=array();
    $url="http://uva.onlinejudge.org/external/".(int)($_GET['id']/100)."/".$_GET['id'].".html";
    echo $url;
    if(get_magic_quotes_gpc()) {
        $url=stripslashes($url);
    }
    $baseurl=substr($url,0,strrpos($url,"/")+1);
    $html=file_get_html($url);
    foreach($html->find('img') as $element)
        $element->src=$baseurl.$element->src;

    $element=$html->find('H1',0);
    $pname=ltrim($element->plaintext);
    $pname=rtrim($pname);
    $d['probname']=$pname;
    $d['filename']="uva_".$_GET['id'];
    $d['submitable']=1;

    $d['timelimit']=1000;
    $d['memorylimit']=128;

    $d['datacnt']=10;
    $d['difficulty']=2;
    $d['readforce']=0;
    $d['plugin']=1;
    $d['group']=0;

    $d['detail']="";
    $element=$html->find('div[class=content]',0);
    $d['detail'].="<h3>【题目描述】</h3>".$element->outertext;
    $element=$html->find('div[class=content]',1);
    $d['detail'].="<h3>【输入格式】</h3>".$element->outertext;
    $element=$html->find('div[class=content]',2);
    $d['detail'].="<h3>【输出格式】</h3>".$element->outertext;
    $element=$html->find('pre',0);
    $d['detail'].="<h3>【样例输入】</h3>".str_replace("  ","\n",$element->outertext)."";
    $element=$html->find('pre',1);
    $d['detail'].="<h3>【样例输出】</h3>".nl2br($element->outertext)."";
}

?>


<div id='copy' class='modal hide fade in'>
<div class='modal-header'>
<button class='close' data-dismiss='modal'>×</button>
<h2>抄袭题目</h2>
</div>
<div class='modal-body'>
<form action='' method='get' class='form-horizontal'>
<input name='action' type='hidden' value='<?=$_GET['action']?>' />
<input name='pid' type='hidden' value='<?=$_GET['pid']?>' />
<div class='control-group'>
<label class='control-label' for='oj'>在线系统</label>
<div class='controls'>
<select id='oj' name='oj' class='input-medium'>
<option value='poj' <?if($_GET['oj']=='poj') echo "selected=selected";?> >POJ</option>
<option value='soj' <?if($_GET['oj']=='soj') echo "selected=selected";?> >SOJ</option>
<!--<option value='rqnoj' <?if($_GET['oj']=='rqnoj') echo "selected=selected";?> >RQNOJ</option>-->
<option value='pojgrids' <?if($_GET['oj']=='pojgrids') echo "selected=selected";?> >POJ 百练</option>
<option value='lydsy' <?if($_GET['oj']=='lydsy') echo "selected=selected";?> >耒阳大视野</option>
<!--<option value='uva' <?if($_GET['oj']=='uva') echo "selected=selected";?> >UVa</option>-->
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
<form action="doeditprob.php" method="post" enctype="multipart/form-data" class='form-horizontal'>
<div id='cates' class='modal hide fade in' style='width:940px; margin-left:-470px;'>
<?php
if ($_GET['pid']) {
    $sql="select caid from tag where pid={$_GET['pid']}";
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
<div class='modal-header'>
<button class='close' data-dismiss='modal'>×</button>
<h3>题目分类</h3>
</div>
<div class='modal-body'>
<ul class='nav nav-pills'>
<?php
for ($i=0;$i<$cnt;$i++) {
$f=$p->rtnrlt($i);
?>
<li style='width: 120px'>
<input name="cate[<?=$f['caid']?>]" type="hidden" value="0" />
<label class='checkbox inline'>
<input name="cate[<?=$f['caid']?>]" type="checkbox" id="cate[<?=$f[caid]?>]" value="1" <?php if ($hash[$f['caid']]) echo 'checked="checked"';?>/>
<label <?php if ($hash[$f['caid']]) echo 'class="label"';?>><?=$f['cname']?></label>
</label>
</li>
<? } ?>
</ul>
</div>
<? } ?>
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
<input name="action" type="hidden" id="action" value="<?=$_GET['action'] ?>" />
<a class='btn pull-right' href="#copy" data-toggle='modal'><i class='icon-barcode'></i>抄袭题目</a>
</div>
</div>
<div class='control-group'>
<label class='control-label' for='probname'>题目名称</label>
<div class='controls'>
<input name="probname" type="text" id="probname" onchange="checkprobname()" value="<?=$d['probname'] ?>" />
<span class='help-inline' id="msg1"></span>
</div>
</div>
<div class='control-group'>
<label class='control-label' for='filename'>文件名称</label>
<div class='controls'>
<input name="filename" type="text" id="filename" onchange="checkfilename()" value="<?=$d['filename'] ?>" />
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
<label class='control-label' for='readforce'>阅读权限</label>
<div class='controls'>
<input name="readforce" type="number" id="readforce" value="<?=$d['readforce'] ?>" class='span4' />
<span class='help-inline'><label class='checkbox inline'>
<input name="submitable" type="checkbox" id="submitable" value="1" <?php if ($d['submitable']) echo 'checked="checked"'; ?> />
可否提交</label></span>
</div>
</div>
<div class='control-group'>
<label class='control-label' for='plugin'>评测方式</label>
<div class='controls'>
<select name="plugin" id="plugin">
<? for($i=-1; $i<5; $i++) {
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
$sql="select * from groups order by gname";
$c=$q->dosql($sql);
for ($j=0;$j<$c;$j++) {
    $e=$q->rtnrlt($j);
    echo "<option value='{$e['gid']}' ";
    if($d['group']==$e['gid']) echo "selected='selected'";
    echo " >{$e['gname']}</option>";
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
<textarea id="detail" name="detail" class='pagearea'><?=$d['detail']?></textarea>
</div>
</div>
</div>
</form>
</div>
</div>
<?php
//$LIB->editor("detail");
include_once("../include/footer.php");
?>
