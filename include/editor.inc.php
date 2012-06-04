<?php
$SH=路径("include/kindeditor/");
$CSSH=路径("style/bootstrap/css/bootstrap.css");
?>
<script charset="utf-8" src="<?=$SH?>kindeditor-min.js"></script>
<script charset="utf-8" src="<?=$SH?>lang/zh_CN.js"></script>
<script>
        var editor;
        KindEditor.ready(function(K) {
                editor = K.create('#<?=$edname?>', {
                        cssPath : '<?=$CSSH?>'
                });
        });
</script>
