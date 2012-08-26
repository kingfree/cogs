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
        cssPath : '<?=$SS?>cogs.css',
        uploadJson : '<?=$SH?>upload_json.inc.php',
        //fileManagerJson : '../php/file_manager_json.php',
        //allowFileManager : true,
    });
});
</script>
