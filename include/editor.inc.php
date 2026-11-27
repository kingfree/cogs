<?php
/**
 * 为编辑题目、文章或站点设置的 textarea 初始化 KindEditor。
 * 例：renderEditor('detail');  // 表单中的 textarea 使用 id="detail"。
 */
function renderEditor($edname) {
    $styles = ['/Bootstrap/css/'.rawurlencode($_SESSION['user_style']).'.min.css', 路径('style/cogs.css'), '/kindeditor/plugins/code/prettify.css'];  // 编辑区沿用当前主题和代码配色。
?>
<script charset="utf-8" src="/kindeditor/kindeditor-min.js"></script>
<script charset="utf-8" src="/kindeditor/lang/zh_CN.js"></script>
<script>
var editor;
KindEditor.ready(function(K) {                            // 等编辑器主程序和中文语言包就绪。
    editor = K.create(<?=json_encode('#'.$edname, JSON_HEX_TAG)?>, {
        cssPath: <?=json_encode($styles, JSON_HEX_TAG)?>,  // 作为 JS 数组传入，路径中的引号不能越过字符串。
        bodyClass: 'problem',                             // 预览沿用题面排版。
        extraFileUploadParams: {form_token: <?=json_encode(formToken())?>},  // 附件上传和普通表单校验同一会话令牌。
        uploadJson: <?=json_encode(路径('include/upload_json.inc.php'), JSON_HEX_TAG)?>,  // 子目录部署也提交到本站上传入口。
        width: '100%',
        syncType: 'form',                                 // 提交前把编辑内容同步回原 textarea。
        filterMode: false,                                // 保留编辑器里的 HTML 原文，题面和文章在显示时另行过滤。
        themeType: 'simple',
        wellFormatMode: true,
        indentChar: '',                                   // 格式化 HTML 时不额外插入缩进。
        shadowMode: false,
        imageTabIndex: 1,                                 // 打开插图对话框时选中本地上传栏。
        pasteType: 1,                                     // 普通粘贴以纯文本导入。
        items: ['fullscreen', 'preview', 'source', '|',   // 分隔符保留工具按钮在旧模板中的分组。
            'undo', 'redo', '|', 'plainpaste', 'wordpaste', '|',
            'formatblock', 'code', 'link', 'unlink', '|',
            'fontsize', 'bold', 'italic', 'underline', 'forecolor', 'hilitecolor', '|',
            'image', 'emoticons', 'table', 'insertfile', '|', 'clearhtml', 'about']
    });
});
</script>
<?php }
