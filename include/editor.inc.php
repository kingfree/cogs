<?php
$SHfull=$SET['base']."include/kindeditor/";
$SH=pathconvert($SET['cur'],$SHfull);
?>
<script charset="utf-8" src="<?=$SH?>kindeditor-min.js"></script>
<script charset="utf-8" src="<?=$SH?>lang/zh_CN.js"></script>
<script>
        var editor;
        KindEditor.ready(function(K) {
                editor = K.create('#<?=$edname?>', {
                        themeType : 'simple'
                });
        });
</script>
