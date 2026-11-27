<?php
// 同一表单兼容查看已有备份和新建备份，例如 backtype=usercode、filename=weekly。
require_once __DIR__.'/../include/header.php';
gethead(0, '超级用户', '');  // 先核验超级用户权限，再受理备份请求。
requirePost();  // 旧链接进入确认页，提交的 POST 已由站点校验令牌。
$type = $_POST['backtype'] ?? '';  // backtype 同时承载“查看列表”和各类“创建备份”操作。
try {
    if ($type === 'showback') {  // showback 是旧表单约定的只读操作。
        $message = implode("\n", Backup::listing($SET['dir_databackup']));  // 把分类内已发布的相对文件名逐行列出。
    } else {
        $filename = Backup::create($type, $_POST['filename'] ?? '', $SET, $cfg);  // 按所选类别写出归档，返回时文件已完成发布。
        $message = '备份已保存为 '.$filename;  // 结果页回显含日期和随机尾缀的实际名称。
    }
} catch (DomainException $error) {
    http_response_code(400);  // 无效种类或标签可以由提交者修改后重试。
    exit(h($error->getMessage()));
} catch (RuntimeException $error) {
    http_response_code(500);  // 建目录、压缩或发布失败需要管理员检查服务器。
    exit(h($error->getMessage()));  // 保留失败原因，页面按纯文本展示。
}
gethead(1, '超级用户', '备份');  // 成功时再渲染列表或成品名称。
?>
<div class="container"><a href="backup.php">返回备份页面</a><pre><?=h($message)?></pre></div>
<?php require __DIR__.'/../include/footer.php'; ?>
