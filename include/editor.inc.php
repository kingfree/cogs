<?php
/**
 * 在表单的 textarea 上初始化 KindEditor。上传请求沿用当前会话的 CSRF 令牌，和普通表单使用同一次校验。
 * 例：renderEditor('detail');  // 对应 id 为 detail 的 textarea。
 */
function renderEditor($edname) {
$SH=路径("include/");
$SS=路径("style/");
?>
<script charset="utf-8" src="/kindeditor/kindeditor-min.js"></script>
<script charset="utf-8" src="/kindeditor/lang/zh_CN.js"></script>
<script>
var editor;
KindEditor.ready(function(K) {
    editor = K.create('#<?=$edname?>', {
        cssPath: ['/Bootstrap/css/<?=$_SESSION['user_style']?>.min.css', '<?=$SS?>cogs.css', '/kindeditor/plugins/code/prettify.css'],
        bodyClass: 'problem',
        extraFileUploadParams: {form_token: <?=json_encode(formToken())?>},
        uploadJson: '<?=$SH?>upload_json.inc.php',
        //fileManagerJson : '../php/file_manager_json.php',
        //allowFileManager : true,
        width: '100%',
        syncType: 'form',
        filterMode: false,
        themeType: 'simple',
        wellFormatMode: true,
        indentChar: '',
        shadowMode: false,
        imageTabIndex: 1,
        pasteType: 1,
        //newlineTag: 'br',
        items: ['fullscreen', 'preview', 'source', '|',
        'undo', 'redo', '|',
        'plainpaste', 'wordpaste', '|',
        'formatblock', 'code', 'link', 'unlink', '|',
        'fontsize', 'bold', 'italic', 'underline',
        'forecolor', 'hilitecolor', '|',
        'image', 'emoticons', 'table', 'insertfile', '|',
        'clearhtml', 'about'],
        //afterChange: MathJax.Hub,
    });
});
</script>

<?php }
