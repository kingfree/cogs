<?php
$commonjsfull=$SETTINGS['base']."include/common.js.php";
$commonjs=pathconvert($SETTINGS['cur'],$commonjsfull);
$SHfull=$SETTINGS['base']."include/syntaxhighlighter/";
$SH=pathconvert($SETTINGS['cur'],$SHfull);
?>
<script type="text/javascript" src="<?=$SH?>scripts/shCore.js"></script>
<script type="text/javascript" src="<?=$SH?>scripts/shBrushDelphi.js"></script>
<script type="text/javascript" src="<?=$SH?>scripts/shBrushCpp.js"></script>
<script type="text/javascript" src="<?=$SH?>scripts/shBrushDiff.js"></script>
<script type="text/javascript" src="<?=$SH?>scripts/shBrushPlain.js"></script>
<link type="text/css" rel="stylesheet" href="<?=$SH?>styles/shCoreDefault.css"/>
