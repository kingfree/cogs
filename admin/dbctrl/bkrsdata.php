<?php
require_once("../../include/stdhead.php");
gethead(1,"advadmin","数据库管理");
?>

<p><a href="../settings.php?settings=dbctrl"><?php echo $STR[admin][data]; ?></a></p>
<?php
if ($_GET[action]=="backup")
{
?>
<p><strong>备份数据库</strong></p>
<form id="form1" name="form1" method="get" action="dodata.php">
  <p>备份文件名：
    <input name="filename" type="text" id="filename" value="<?php echo date('Y-m-d-H-i-s', time()).'.cdb' ?>" size="40" class="InputBox" />
</p>
  <p>请不要将文件扩展名命名为.php</p>
  <p>
    <input type="submit" name="Submit" value="开始备份" class="Button"/>
    <input name="action" type="hidden" id="action" value="backup" />
  </p>
</form>
<p>
<?php
}
if ($_GET[action]=="backupsucc")
{
?>
</p>
<p>数据库备份成功！</p>
<p>文件名：<a href="downloadbackupdata.php?src=<?php echo $_GET['file']; ?>"><?php echo $_GET['file']; ?></a></p>
<p>点击以上链接以下载到本地备份</p>
<?php
}
if ($_GET[action]=="restore")
{
?>
<p><strong>恢复数据库</strong></p>

<form id="form2" name="form2" method="get" action="dodata.php">
  <p>从文件恢复</p>
  <table border="1">
    <tr>
      <th scope="col">选定</th>
      <th scope="col">文件名</th>
      <th scope="col">备份日期</th>
      <th scope="col">大小</th>
    </tr>
<?php
$handle=opendir($SETTINGS['dir_databackup']);
while ($file = readdir($handle)) 
{
	if ($file!='.'&& $file!='..')
	{
		$path="{$SETTINGS['dir_databackup']}/$file";
?>
	 <tr>
      <td><input name="filename" type="radio" value="<?php echo $file; ?>" checked="checked" /></td>
      <td><a href="downloadbackupdata.php?src=<?php echo $file; ?>"><?php echo $file; ?></a></td>
      <td><?php echo date('Y-m-d H:i:s',filemtime($path)); ?></td>
      <td><?php echo getfilesize(filesize($path)) ?></td>
    </tr>
<?php
	}
}
?>
  </table>
    <input name="action" type="hidden" id="action" value="restore" />
    <p>
    <input type="submit" name="Submit" value="从文件恢复"  class="Button" />
  </p>
</form>
<p>
  <?php
}
if ($_GET[action]=="restoresucc")
{
?>
</p>
<p>数据库恢复成功！</p>
<p>
  <?php
}
?>
</p>

<?php
	include_once("../../include/stdtail.php");
?>