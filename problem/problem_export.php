<?php
// 让有修改题目权限的用户选择要导出的题目，选择结果交给 FPS XML 导出入口。
// 打开 problem/problem_export.php，可填写一段题号或逗号分隔的编号。
require_once("../include/header.php");
gethead(1,"修改题目","修改题目");
renderEditor("detail");
?>

<form action='problem_export_xml.php' method=post><?=formTokenField()?>
	<b>Export Problem:</b><br />
	from pid:<input type=text size=10 name="start" value=1000>
	to pid:<input type=text size=10 name="end" value=1000><br />
	or in<input type=text size=40 name="in" value=""><br />
	<input type='hidden' name='do' value='do'>
	<input type=submit name=submit value='Export'>
   <input type=submit value='Download'>
</form>
* from-to will working will empty IN <br>
* if using IN,from-to will not working.<br>
* IN can go with "," seperated problem_ids like [1000,1020]
<?php
include_once("../include/footer.php");
?>
