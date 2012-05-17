<?php
require_once("../include/stdhead.php");
gethead(0,"sess","");

if ($_POST[action]=="edit") 
{
    if($_FILES['file']['tmp_name']) {
        $portrait=$SET['base']."images/background";
        $path = pathconvert($SET['cur'],$portrait).'/';
        $backfile = $path . $_POST['uid'] . ".png";
        if(file_exists($backfile)) {
            $cmd = "rm $backfile";
            $hr = popen($cmd, 'r');
            pclose($hr);
        }
        $cmd = "convert {$_FILES['file']['tmp_name']} $backfile";
        $hr = popen($cmd, 'r');
        pclose($hr);
    }
	$p=new DataAccess();
	$sql="update userinfo set nickname='{$_POST['nick']}',realname='{$_POST['realname']}',email='{$_POST['email']}',memo='{$_POST['memo']}' where uid={$_POST['uid']}";
	$p->dosql($sql);
	$LIB->get_userinfo($_GET['uid']);
	echo '<script>document.location="../refresh.php?id=8"</script>';
}
if ($_POST[action]=="editpwd") 
{
	if ($_POST[npwd1]==$_POST[npwd2])
	{
		$p=new DataAccess();
		$sql="select pwdhash from userinfo where uid={$_GET[uid]}";
		$p->dosql($sql);
		$d=$p->rtnrlt(0);
		if ($d[pwdhash]==encode($_POST[opwd]))
		{
			$sql="update userinfo set pwdhash='". encode($_POST[npwd1]) ."' where uid={$_GET[uid]}";
			$p->dosql($sql);
			echo '<script>document.location="../refresh.php?id=8"</script>';
		}
		else
		{
			echo '<script>document.location="../error.php?id=14"</script>';
		}
	}
	else
	{
		echo '<script>document.location="../error.php?id=13"</script>';
	}
}
?>
