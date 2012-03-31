<?php
set_time_limit(0);
error_reporting(0);
// write log
require_once("stdlib.php");
$LIB->cls_getsettings();

if (!isset($_SESSION['ID']))
{
	$_SESSION['readforce']=0;
	$_SESSION['ID']=0;
}

function gethead($head,$check,$title)
{
	global $SETTINGS,$cfg,$LIB;
	if ($check=="sess")
	{
		if (!$_SESSION['ID'])header("location: /{$SETTINGS['global_root']}error.php?id=1&from={$SETTINGS['URI']}");
	}
	else if ($check=="admin")
	{
		if (!($_SESSION['admin']>0))header("location: /{$SETTINGS['global_root']}error.php?id=7");
	}
	else if ($check=="advadmin")
	{
		if ($_SESSION['admin']!=2)header("location: /{$SETTINGS['global_root']}error.php?id=7");
	}else if ($check=="verfy")
	{
		if ($_SESSION['admin']!=-1)header("location: /{$SETTINGS['global_root']}");
	}
	if ($head==1)
	{
        //if($_SESSION['ID']==524) $SETTINGS['style_profile'] = "4.css";
		$style=$SETTINGS['base']."style/".$SETTINGS['style_profile'];
		$stylepath=pathconvert($SETTINGS['cur'],$style);
		$nagbar=$SETTINGS['base']."include/stdbar.php";
		$icon=$SETTINGS['base']."style/icon.css";
		$iconpath=pathconvert($SETTINGS['cur'],$icon);
		$jq=$SETTINGS['base']."include/jquery.js";
		$jqpath=pathconvert($SETTINGS['cur'],$jq);
		$useracc=new DataAccess();
?>
<!DOCTYPE HTML>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<link rel=stylesheet href="<?php echo $stylepath ?>"><?php $LIB->tradsimp(); ?>
<link rel=stylesheet type="text/css" href="<?=$iconpath?>" />
<script type="text/javascript" src="<?=$jqpath?>"></script>
<title><?php echo $title." - ".$SETTINGS['global_sitename'] ?></title>
</head>
<body>
<div id="alltext">
<div id="globalbar">
<table class="Head" border="0" width="100%">
  <tr>
    <td rowspan="2"><?php showuser($_SESSION['ID'],$useracc) ?></td>
    <td><?php echo output_text($SETTINGS['global_head']) ?></td>
  </tr>
  <tr>
    <td><?php include($nagbar) ?></td>
  </tr>
</table>
</div>
<marquee class="stdPanel" id="publicbar" align="right" direction="left" scrollamount="5" onMouseOver="this.stop();" onMouseOut="this.start();">
<?php if ($_SESSION['admin']==2) { ?>[<a href="<?=pathconvert($SETTINGS['cur'],$editbulletin);?>">修改</a>]<?php } ?><font color="#003366"><b>公告 &gt;&gt;</b></font>
<?=output_text($SETTINGS['global_bulletin']); ?>
</marquee>
<div id="body">
<?php
	}
}
?>
