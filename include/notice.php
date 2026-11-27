<?php
/**
 * 保存操作返回的提示、错误及站内跳转。错误提示会终止请求，普通提示输出后把控制权交还给调用者。
 * 例：i提示('站内信已发送。', 取路径('mail/index.php'));
 */
/** 输出对话框和站内返回地址。目的地为空、包含非法字符或指向站外时返回站点首页。 */
function renderNotice($message, $destination, $seconds, $error)
{
    global $SET;
    $home = '/'.$SET['global_root']; // 跳转失败时退回当前安装的首页。
    $path = base64_decode((string)$destination, true); // 表单中保留的是编码过的原始站内地址。
    if (!$path || !str_starts_with($path, '/') || str_starts_with($path, '//') || preg_match('/[[:cntrl:]\\\\]/', $path)) $path = $home;
    $path = h($path); // 同一地址会进入链接和刷新标签，先转义。
    $seconds = max(0, (int)($seconds ?: $SET['style_jumptime'])); // 使用站点默认延迟，负秒数按零处理。
    $kind = $error ? 'error' : 'success';
    $label = $error ? '错误' : '提示'; // 同一对话框呈现两种状态。
    echo "<div id='dialog' class='modal fade in'><div class='modal-header'>$label</div>",
        "<div class='modal-body alert alert-$kind'>",richText($message),"</div>",
        "$seconds 秒后跳转到<a href='$path'>$path</a>……</div>",
        "<meta http-equiv='refresh' content='$seconds; URL=$path'>";
}

/** 显示错误并结束当前请求；适用于已输出页头的页面。 */
function 异常($msg = '错误', $id = '', $jp = 0) { renderNotice($msg, $id, $jp, true); exit; }
/** 显示成功提示后返回调用方；需要中止后续业务时，调用者应自行返回。 */
function 提示($msg = '提示', $id = '', $jp = 0) { renderNotice($msg, $id, $jp, false); }
/** 先输出简洁页头，再显示错误并中止请求。 */
function i异常($msg = '错误', $id = '', $jp = 0) { gethead(8, '', '错误'); 异常($msg, $id, $jp); }
/** 先输出简洁页头，再显示成功提示和返回链接。 */
function i提示($msg = '提示', $id = '', $jp = 0) { gethead(8, '', '提示'); 提示($msg, $id, $jp); }
