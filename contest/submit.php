<?php
require_once __DIR__.'/../include/header.php';
gethead(0, '普通用户', '');
requirePost();
$id = (int)($_POST['ctid'] ?? 0);
$pid = (int)($_POST['pid'] ?? 0);
$uid = (int)$_SESSION['ID'];
$round = ContestRound::rules($id)[0] ?? null;
$problem = Problem::byId($pid)[0] ?? null;
if (!$round || !$problem || !in_array((string)$pid, explode(':', $round['contains']), true) ||
    (!Privilege::allows('查看比赛') && (time() < $round['starttime'] || time() > $round['endtime'] ||
    !UserGroup::includes($round['gid'], $_SESSION['group']) || $round['readforce'] > $_SESSION['readforce'] ||
    $problem['readforce'] > $_SESSION['readforce']))) { http_response_code(403); exit('本场比赛当前不接受这次提交。'); }
$language = Language::id($_POST['lang'] ?? '');
$file = $_FILES['file'] ?? [];
if ($language === null || !TestData::name($problem['filename']) || !$file || $file['error'] !== UPLOAD_ERR_OK ||
    $file['size'] < 1 || $file['size'] > 102400 || !is_uploaded_file($file['tmp_name'])) {
    http_response_code(400); exit('源文件或提交语言无效。');
}
$csid = ContestEntry::submitSource($id, $pid, $uid, $problem['filename'], $language, $file['tmp_name'], $SET['dir_competition']);
i提示('比赛提交代码成功！', 取路径('contest/code.php?csid='.$csid));
