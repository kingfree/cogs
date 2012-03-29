<?php
require_once("../include/stdhead.php");
gethead(1,"","发表新帖");

?>
<center>
<form id=huifu name=huifu method=post action=huifu.php>
<label for="title">新帖标题：</label>
<input name="title" size="85" type="text" id="title" value="在此输入帖子标题" class="InputBox"/>
<br />
<textarea name="text" cols="100" rows="8" id="text" class="TextArea">
</textarea>
<br />
<input name="submit" type="submit" id="submit" value="发表新帖" class="LinkButton"/>
<label for="pid">关联题目编号</label><input name="pid" type="number" id="pid" value="<?=(int)$_GET['pid']?>" />
<input name="showcode" type="checkbox" id="showcode" value="1" <?php if ($sc){ ?> checked="checked" <?php } ?> /><label for="showcode">关联你最近提交的代码</label>
<input name="fid" type="hidden" id="fid" value="0" />
<input name="url" type="hidden" id="url" value="../bbs/index.php" />
</form>
</center>
<?
require_once("../include/stdtail.php");
?>
