
<script language="javascript">

var HTTP;
var p=0;
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

function doStart(cmd)
{
	createHTTP();
	url="terminal/send.php";
	document.getElementById("cmds").innerHTML=cmd;
	document.getElementById("sta").innerHTML="正在执行命令";
	document.getElementById("Submit").disabled=true;
	enstr=BASE64.encode(cmd);
	strA = "cmd=" + enstr;
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
			document.getElementById("memo").innerHTML=text;
			document.getElementById("sta").innerHTML="命令成功完成";
			document.getElementById("Submit").disabled=false;
		}
	}
}
</script>
<p>执行命令：<span id="cmds"></span> 状态：<span id="sta">就绪</span></p>
<p>
  <textarea name="memo" cols="140" rows="16" id="memo" class="TextArea" readonly="readonly">
  </textarea>
</p>
  <p>输入命令(多条命令顺序执行请用;隔开)</p>
  <p>
    <textarea name="command" cols="40" class="InputBox" id="command"></textarea>
    <input type="button" name="Submit" class="Button" value="执行" onclick='doStart(document.getElementById("command").value);' id="Submit" />
  </p>
<script language="javascript">
document.getElementById("command").focus();
</script>