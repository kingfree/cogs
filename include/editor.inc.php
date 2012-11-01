<?php
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
        items: ['source', 'preview', '|', 'undo', 'redo', '|', 'plainpaste', 'wordpaste', '|', 'formatblock', 'code', 'link', 'unlink', '|', 'fontsize', 'bold', 'italic', 'underline', 'forecolor', 'hilitecolor', '|', 'image', 'emoticons', 'table', 'insertfile', '|', 'clearhtml', 'about', 'fullscreen'],
    });
});
</script>

