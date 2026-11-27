<?php
/**
 * KindEditor 上传入口。先验登录权限和表单令牌，再让 Attachment 校验和保存文件，返回编辑器认识的 JSON。
 * 例：在编辑器中选择图片时 POST imgFile 到 include/upload_json.inc.php?dir=image。
 */
require_once __DIR__.'/header.php'; // 恢复站点会话并检查表单令牌。
gethead(0, '上传数据', ''); // 只检查上传权限，编辑器需要 JSON 响应。
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Allow: POST', true, 405); exit; } // 拒绝通过访问链接触发上传。
// KindEditor 在旧浏览器中用 iframe 读取 JSON。
header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff'); // 即使走旧式 iframe 响应也禁止类型嗅探。
try {
    $path = Attachment::save($_GET['dir'] ?? 'image', $_FILES['imgFile'] ?? []); // 由附件主体核对内容并保存到公开数据目录。
    $result = ['error'=>0, 'url'=>'/'.$SET['global_root'].$path]; // 返回公开 URL，物理存储位置只在服务器使用。
} catch (DomainException | RuntimeException $error) {
    $result = ['error'=>1, 'message'=>$error->getMessage()];
}
echo json_encode($result, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE);
