<?php
// 邮件页面共用的访问规则。发件人和收件人可查看，只有收件人可标记已读。
function mailReadMessage($db, $mid, $uid) {
    $mid = filter_var($mid, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));
    $uid = (int)$uid;
    if (!$mid || $uid < 1) return false;
    if (!$db->dosql("select * from mail where mid = ? and (fromid = ? or toid = ?)", [$mid, $uid, $uid])) return false;
    return $db->rtnrlt();
}

function mailMarkRead($db, $mid, $uid) {
    $mid = filter_var($mid, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));
    $uid = (int)$uid;
    if ($mid && $uid > 0) $db->dosql("update mail set readed = 1 where mid = ? and toid = ?", [$mid, $uid]);
}

function mailHtml($value) {
    return is_string($value) ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : '';
}
