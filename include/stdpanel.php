<?php
$cur=$_SERVER['DOCUMENT_ROOT'].$_SERVER['SCRIPT_NAME'];
$base=$_SERVER['DOCUMENT_ROOT']."/".$SETTINGS['global_root'];
$portrait=$base."images/portrait";
$panel=$base."user/panel.php";
$index=$base."index.php";
$problist=$base."problem/problist.php";
$cate=$base."problem/catelist.php";
$competition=$base."competition/index.php";
$help=$base."user/help.php";
$about=$base."about.php";
$logout=$base."user/dologout.php";
$admin=$base."admin/index.php";
?>
<div class="stdPanel">
      <table border="0">
        <tr>
          <td><img src="<?php echo pathconvert($cur,$portrait).'/'.$_SESSION['portrait']?>.jpg" width="64" height="64" /></td>
        </tr>
        <tr>
          <td><?php echo $_SESSION['nickname'];?></td>
        </tr>
        <tr>
          <td><a href="<?php echo pathconvert($cur,$panel); ?>">控制面板</a></td>
        </tr>
        <tr>
          <td><a href="<?php echo pathconvert($cur,$index);?>">系统首页</a></td>
        </tr>
        <tr>
          <td><a href="<?php echo pathconvert($cur,$problist);?>">进入题库</a></td>
        </tr>
        <tr>
          <td><a href="<?php echo pathconvert($cur,$cate);?>">进入分类</a></td>
        </tr>
        <tr>
          <td><a href="<?php echo pathconvert($cur,$competition);?>">进入比赛</a></td>
        </tr>
        <tr>
          <td><a href="<?php echo pathconvert($cur,$help);?>">系统帮助</a></td>
        </tr>
        <tr>
          <td><a href="<?php echo pathconvert($cur,$about);?>">关于系统</a></td>
        </tr>
        <tr>
          <td><a href="<?php echo pathconvert($cur,$logout);?>">登出系统</a></td>
        </tr>
        <tr>
          <td>你的权限</td>
        </tr>
        <tr>
          <td><?php echo $STR[adminn][$_SESSION['admin']]; ?></td>
        </tr>
        <tr>
          <td><?php if ($_SESSION['admin'])	echo '<p><a href="'. pathconvert($cur,$admin) .'">后台管理</a>';?></td>
        </tr>
      </table>
      </div>
