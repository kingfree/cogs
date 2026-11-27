<?php
// 浏览和下载后台测试数据需查看数据权限，编辑、删除、移动和上传还需修改题目权限。
// 打开 problem/testdata.php?problem=sum 查看文件；附加 &download=sum1.in 可下载单个输入。
require_once __DIR__.'/../include/header.php';
gethead(0, '查看数据', '');
$name = $_GET['problem'] ?? $_GET['dir'] ?? '';
$canWrite = Privilege::allows('修改题目');
try {
    $dir = $name === '' ? TestData::root() : TestData::directory($name);
    if (!is_dir($dir)) { http_response_code(404); exit('没有此题的数据目录。'); }
    if ($name !== '' && isset($_GET['download'])) {
        $files = TestData::files($name);
        if ($_GET['download'] === 'all') Archive::download($name.'.zip', $files);
        else {
            $leaf = $_GET['download'];
            if (!isset($files[$leaf])) { http_response_code(404); exit; }
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.$leaf.'"');
            readfile($files[$leaf]);
        }
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!$canWrite) { http_response_code(403); exit; }
        $action = $_POST['action'] ?? '';
        $leaf = $_POST['file'] ?? '';
        if ($action === 'mkdir') {
            $name = $_POST['problem'] ?? '';
            TestData::directory($name, true);
        } elseif ($action === 'upload' && is_uploaded_file($_FILES['data']['tmp_name'] ?? '')) {
            $file = $_FILES['data'];
            if ($file['size'] > 16777216) throw new DomainException('单个文件超过 16 MiB。');
            TestData::store($name, $file['name'], file_get_contents($file['tmp_name']));
        } elseif ($action === 'save') {
            TestData::store($name, $leaf, $_POST['contents'] ?? '');
        } elseif ($action === 'delete') {
            $path = TestData::file($name, $leaf);
            if (!is_file($path) || !unlink($path)) throw new DomainException('删除文件失败。');
        } elseif (in_array($action, ['copy','move'], true)) {
            $source = TestData::file($name, $leaf);
            $target = TestData::file($_POST['target_problem'] ?? $name, $_POST['target'] ?? '');
            if (!is_file($source) || file_exists($target)) throw new DomainException('源文件不存在或目标文件已存在。');
            if (!($action === 'copy' ? copy($source, $target) : rename($source, $target))) throw new DomainException('文件操作失败。');
        } else throw new DomainException('数据操作无效。');
        header('Location: testdata.php?problem='.rawurlencode($name), true, 303);
        exit;
    }
    $files = $name === '' ? [] : TestData::files($name);
    $folders = [];
    if ($name === '') {
        foreach (new DirectoryIterator($dir) as $item) {
            if ($item->isDir() && !$item->isLink() && TestData::name($item->getFilename())) $folders[]=$item->getFilename();
        }
        natcasesort($folders);
    }
    $selected = $_GET['edit'] ?? '';
    $query = $_GET['key'] ?? '';
    gethead(1, '查看数据', '测试数据');
} catch (DomainException $error) { http_response_code(400); exit(h($error->getMessage())); }
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
