<?php
// 提供 FPS 题包上传表单。需要修改题目权限，由导入入口读取上传的 XML。
// 打开 problem/problem_import.php，选取 FPS XML 文件后提交。
require_once __DIR__.'/../include/header.php';
gethead(1, '修改题目', '导入题目');
?>
<form method="post" action="problem_import_xml.php" enctype="multipart/form-data"><?=formTokenField()?>
<label>FPS 题包 <input type="file" name="fps" accept=".xml" required></label>
<button class="btn btn-primary">导入题目</button>
</form>
<?php require dirname(__DIR__).'/include/footer.php'; ?>
