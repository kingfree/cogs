<?php
/**
 * 页头加载简繁转换脚本。根据当前脚本和站点目录计算地址，以支持安装到子路径。
 * 例：require __DIR__.'/tradsimp.inc.php';  // 在 include/header.php 中载入。
 */
$tradsimp=pathconvert($SET['cur'],$SET['base']."include/tradsimp.js");
?>

<script class="javascript" src="<?php echo $tradsimp ?>"></script>