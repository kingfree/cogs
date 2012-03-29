<?php
require_once("../include/stdhead.php");
gethead(1,"admin","比赛评测");
?>

<?php
if ($_POST['do']=="评测选定")
{
	echo "<p>评测选定</p>";
	$list=$_POST['doit'];
}
else if ($_POST['do']=="评测全部")
{
	echo "<p>评测全部</p>";
	$list=$_POST['doall'];
}
if (!is_array($list))$list=array();
sort($list);
?>

<script language="javascript">
var HTTP;
var list = new Array(<?php 
	$cnt=0;
	foreach($list as $k=>$v)
	{
		echo "$v,";
		$cnt++;
	}
	echo 0;
?>);
var p=0;
var pmax=<?php echo $cnt ?>;
var text="";

function createHTTP()
{
	if (window.ActiveXObject)
	{
		HTTP=new ActiveXObject("Microsoft.XMLHTTP");
	}
	else
	if (window.XMLHttpRequest)
	{
		HTTP=new XMLHttpRequest();
	}
}

function doStart(csid)
{
	document.getElementById("score"+csid).innerHTML="<span style='background:#FFFF00'>正在评测...</span>";
	document.getElementById("nowp").innerHTML=p+1;
	createHTTP();
	url="compile.php";
	strA = "csid="+csid;
	HTTP.open("POST",url);
	HTTP.setRequestHeader("CONTENT-TYPE","application/x-www-form-urlencoded");
	HTTP.send(strA);
	HTTP.onreadystatechange=Callback;
}

function Callback()
{
	if (HTTP.readyState==4)
	{
		if(HTTP.status==200)
		{
			var w="";
			text=HTTP.responseText;
			str = text.split("!");
			document.getElementById("score"+list[p]).innerHTML=str[0];
			document.getElementById("result"+list[p]).innerHTML=str[1];
			++p;
			document.getElementById("progress").value=Math.round(p/pmax*100)+"%";
			if (p<pmax)
				doStart(list[p]);
			else
				Finish();
		}
	}
}

function StartJudge()
{
	doStart(list[0]);
	p=0;
	document.getElementById("st").disabled=true;
	document.getElementById("st").value="正在评测...";
}

function Finish()
{
	document.getElementById("st").disabled=false;
	document.getElementById("st").value="重新评测";
}

</script>

<p>
  <input name="st" type="button" id="st" value="开始评测" onclick="StartJudge()" class="Button" />
</p>
<p>
进度
  <input readonly="readonly" type="text" id="progress" style="background-color:#6699CC; color:#FFFF00" value="0%" size="6" />
当前第<span id="nowp">0</span>个 共<?php echo $cnt ?>个 </p>
<table width="100%" border="1">
  <tr>
    <th width="7%" scope="col">CSID</th>
    <th width="17%" scope="col">用户昵称</th>
    <th width="14%" scope="col">真实姓名</th>
    <th width="17%" scope="col">题目名</th>
    <th width="14%" scope="col">文件名</th>
    <th width="8%" scope="col">代码</th>
    <th width="10%" scope="col">得分</th>
    <th width="13%" scope="col">测试点</th>
  </tr>
<?php 
	$p=new DataAccess();
	$sql="select compscore.csid,compscore.ctid,userinfo.nickname,userinfo.realname,problem.probname,problem.filename,compscore.lang,compscore.score,compscore.result,compscore.pid,compscore.uid,compscore.ctid from compscore,problem,userinfo where compscore.uid=userinfo.uid and compscore.pid=problem.pid and(";
	foreach($list as $k=>$v)
		$sql.="compscore.csid={$v} or ";
	$sql.="compscore.csid=0) order by compscore.csid asc";
	$cnt=$p->dosql($sql);
	for ($i=0;$i<$cnt;$i++)
	{
		$d=$p->rtnrlt($i);
?>
  <tr>
    <td><?php echo $d[csid] ?></td>
    <td><a href="../user/detail.php?uid=<?php echo $d[uid] ?>" target="_blank"><?php echo $d[nickname] ?></a></td>
    <td><a href="../user/detail.php?uid=<?php echo $d[uid] ?>" target="_blank"><?php echo $d[realname] ?></a></td>
    <td><a href="cdetail.php?pid=<?php echo $d[pid] ?>&ctid=<?php echo $d[ctid] ?>" target="_blank"><?php echo $d[probname] ?></a></td>
    <td><?php echo $d[filename] ?></td>
    <td><a href="code.php?csid=<?php echo $d[csid] ?>" target="_blank"><?php echo $STR[lang][$d[lang]] ?></a></td>
    <td id="score<?php echo $d[csid] ?>"><?php echo $d[score] ?></td>
    <td id="result<?php echo $d[csid] ?>"><?php echo $d[result] ?></td>
  </tr>
<?php 
	}
?>
</table>
<p><a href="../admin/comp/comptime.php?ctid=<?php echo $_POST['ctid'] ?>">返回比赛场次管理</a></p>

<?php
	include_once("../include/stdtail.php");
?>