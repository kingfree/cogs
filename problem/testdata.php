<?php
// 浏览和下载后台测试数据需查看数据权限，编辑、删除、移动和上传还需修改题目权限。
// 打开 problem/testdata.php?problem=sum 查看文件；附加 &download=sum1.in 可下载单个输入。
require_once __DIR__.'/../include/header.php';
gethead(0, '查看数据', '');
// 老书签的 dir 与新地址的 problem 表示同一题目目录。
$name = $_GET['problem'] ?? $_GET['dir'] ?? '';
$canWrite = Privilege::allows('修改题目');
try {
    // 不指定题目时列出数据根，指定目录缺失时明确返回 404。
    $dir = $name === '' ? TestData::root() : TestData::directory($name);
    if (!is_dir($dir)) { http_response_code(404); exit('没有此题的数据目录。'); }  // 浏览缺失目录不会创建题目。
    if ($name !== '' && isset($_GET['download'])) {
        // all 下载整题；单文件只可取经过筛选的普通数据文件。
        $files = TestData::files($name);
        if ($_GET['download'] === 'all') Archive::download($name.'.zip', $files);
        else {
            $leaf = $_GET['download'];
            // 文件不在该目录列表就结束本次请求。
            if (!isset($files[$leaf])) { http_response_code(404); exit; }
            // 单个文件按附件返回，浏览器不会在后台页执行其中内容。
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.$leaf.'"');
            // 正文使用已核实的真实路径，展示名称使用该题内的短名。
            readfile($files[$leaf]);
        }
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // 查看权限不自动允许写入，编辑文件必须另有修改题目权。
        if (!$canWrite) { http_response_code(403); exit; }  // 只有具有修改题目权限的账号才可改变评测机下一次使用的数据。
        $name = TestData::editFiles($name, $_POST, $_FILES['data'] ?? []);
        // 成功后改用 GET 重新打开目录，刷新不会重复操作。
        header('Location: testdata.php?problem='.rawurlencode($name), true, 303);
        exit;
    }
    // 根页展示各题的目录，题内页展示可下载的文件。
    $files = $name === '' ? [] : TestData::files($name);
    $folders = $name === '' ? TestData::folders() : [];
    $selected = $_GET['edit'] ?? '';
    // 关键字只筛选当前列表，不用它构造磁盘路径。
    $query = $_GET['key'] ?? '';
    gethead(1, '查看数据', '测试数据');
} catch (DomainException $error) { http_response_code(400); exit(h($error->getMessage())); }  // 题目或文件名不符合数据目录约定时提示用户重新选择。
?>
<div class="container">
<h3><a href="testdata.php">测试数据</a> <?=h($name)?></h3>
<form method="get"><input type="hidden" name="problem" value="<?=h($name)?>"><input name="key" value="<?=h($query)?>" placeholder="搜索文件或题目"><button>搜索</button></form>
<ul>
<?php foreach ($folders as $folder) { if (!str_contains($folder, $query)) continue; ?>
<li><a href="?problem=<?=rawurlencode($folder)?>"><?=h($folder)?></a></li>
<?php } foreach ($files as $file=>$path) { if (!str_contains($file, $query)) continue; ?>
<li><a href="?problem=<?=rawurlencode($name)?>&amp;download=<?=rawurlencode($file)?>"><?=h($file)?></a> <?=filesize($path)?> B
<?php if ($canWrite) { ?><a href="?problem=<?=rawurlencode($name)?>&amp;edit=<?=rawurlencode($file)?>">编辑</a><?php } ?></li>
<?php } ?></ul>
<?php if ($files) { ?><p><a href="?problem=<?=rawurlencode($name)?>&amp;download=all">打包下载</a></p><?php } ?>
<?php if ($canWrite && $name === '') { ?>
<form method="post"><?=formTokenField()?><input name="problem" required><button name="action" value="mkdir">创建题目目录</button></form>
<?php } elseif ($canWrite) { ?>
<form method="post" enctype="multipart/form-data"><?=formTokenField()?><input type="file" name="data" required><button name="action" value="upload">上传文件</button></form>
<form method="post"><?=formTokenField()?>
<label>文件名 <input name="file" value="<?=h($selected)?>" required></label>
<textarea name="contents" rows="12" class="span10"><?=isset($files[$selected]) ? h(file_get_contents($files[$selected], false, null, 0, 1048576)) : ''?></textarea>
<?php if (!isset($files[$selected]) || filesize($files[$selected]) <= 1048576) { ?><button name="action" value="save">保存文本</button><?php } ?>
<button name="action" value="delete">删除</button>
<label>目标题目 <input name="target_problem" value="<?=h($name)?>"></label>
<label>目标文件 <input name="target"></label><button name="action" value="copy">复制</button><button name="action" value="move">移动或改名</button>
</form><?php } ?></div>
<?php require dirname(__DIR__).'/include/footer.php'; ?>
