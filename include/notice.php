<?php
function i异常($msg = "错误", $id = "", $jp = 0) {
    gethead(8,"","");
    global $SET;
    if(!$jp) $jp = $SET['style_jumptime'];
    异常($msg, $id, $jp);
}

function 异常($msg = "错误", $id = "", $jp = 0) {
    global $SET;
    if(!$jp) $jp = $SET['style_jumptime'];
    if($id == "") $id = base64_encode("/".$SET['global_root']);
    $id = base64_decode($id);
    HTML("<div id='dialog' class='modal fade in'>");
    HTML("<div id='dialog_title' class='modal-header'>");
    HTML("<span class='label label-important'>错误</span>");
    HTML("</div>");
    HTML("<div id='dialog_text' class='modal-body alert alert-error'>");
    HTML($msg);
    HTML("</div>{$jp} 秒后跳转到<a href='{$id}'>{$id}</a>……");
    HTML("</div>");
    HTML("<meta http-equiv='refresh' content='{$jp}; URL={$id}'>");
    exit;
}

function i提示($msg = "提示", $id = "", $jp = 0) {
    gethead(8,"","");
    global $SET;
    if(!$jp) $jp = $SET['style_jumptime'];
    提示($msg, $id, $jp);
}

function 提示($msg = "提示", $id = "", $jp = 0) {
    global $SET;
    if(!$jp) $jp = $SET['style_jumptime'];
    if($id == "") $id = base64_encode("/".$SET['global_root']);
    $id = base64_decode($id);
    HTML("<div id='dialog' class='modal fade in'>");
    HTML("<div id='dialog_title' class='modal-header'>");
    HTML("<span class='label label-success'>提示</span>");
    HTML("</div>");
    HTML("<div id='dialog_text' class='modal-body alert alert-success'>");
    HTML($msg);
    HTML("</div>{$jp} 秒后跳转到<a href='{$id}'>{$id}</a>……");
    HTML("</div>");
    HTML("<meta http-equiv='refresh' content='{$jp}; URL={$id}'>");
}
