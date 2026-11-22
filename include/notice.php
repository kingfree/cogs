<?php
// 提示页只回到本站路径，错误消息在终止请求前显示。
function renderNotice($message, $destination, $seconds, $error)
{
    global $SET;
    $home = '/'.$SET['global_root'];
    $path = base64_decode((string)$destination, true);
    if (!$path || !str_starts_with($path, '/') || str_starts_with($path, '//') || preg_match('/[[:cntrl:]\\\\]/', $path)) $path = $home;
    $path = h($path);
    $seconds = max(0, (int)($seconds ?: $SET['style_jumptime']));
    $kind = $error ? 'error' : 'success';
    $label = $error ? '错误' : '提示';
    echo "<div id='dialog' class='modal fade in'><div class='modal-header'>$label</div>",
        "<div class='modal-body alert alert-$kind'>",richText($message),"</div>",
        "$seconds 秒后跳转到<a href='$path'>$path</a>……</div>",
        "<meta http-equiv='refresh' content='$seconds; URL=$path'>";
}

function 异常($msg = '错误', $id = '', $jp = 0) { renderNotice($msg, $id, $jp, true); exit; }
function 提示($msg = '提示', $id = '', $jp = 0) { renderNotice($msg, $id, $jp, false); }
function i异常($msg = '错误', $id = '', $jp = 0) { gethead(8, '', '错误'); 异常($msg, $id, $jp); }
function i提示($msg = '提示', $id = '', $jp = 0) { gethead(8, '', '提示'); 提示($msg, $id, $jp); }
