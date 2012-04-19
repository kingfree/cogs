<?php
global $cfg;

function rfile($fp)
{
	$out="";
	while (!feof($fp)) 
		$out.= fgets($fp, 1024000);
	return $out;
}

function wfile($str,$fname)
{
	$fp=fopen($fname,"w");
	fputs($fp,$str,strlen($str));
	fclose($fp);
}

function write_cnt()
{
	$t=read_cnt();
	$t++;
	$fp=fopen("cnt.php","w");
	fprintf($fp,"%ld",$t);
	fclose($fp);
}

function deldir($dir)
{
	$dh=opendir($dir);
	while ($file=readdir($dh)) 
	{
		if($file!="." && $file!="..") 
		{
			$fullpath=$dir."/".$file;
			if(!is_dir($fullpath)) 
			{
				unlink($fullpath);
			} 
			else 
			{
				deldir($fullpath);
			}
		}
	}
	
	closedir($dh);
	rmdir($dir);
}

function standard_compare($f1,$f2)
{
	do
	{
		$d1=fgetc($f1);
		while ($d1==" " || $d1=="\n" || $d1=="\r")
			$d1=fgetc($f1);
		$d2=fgetc($f2);
		while ($d2==" " || $d2=="\n" || $d2=="\r")
			$d2=fgetc($f2);
		if ($d1===false) break;
		if ($d2===false) return 0.0;
		if ($d1!=$d2) return 0.0;
	} while (true);
	if (!($d2===false))
	{
		while (!($d2===false))
		{
			$d2=fgetc($f2);
			if ($d2!=" " && $d2!="\n" && $d2!="\r")
				return 0.0;
		}
	}
	return 1.0;
}

function array_encode($arr)
{
	$sa=array();
	$i=0;
	foreach($arr as $k=>$v)
	{
		$sa[$i]=base64_encode($k);
		$sa[$i+1]=base64_encode($v);
		$i+=2;
	}
	$s=implode("?",$sa);
	return base64_encode($s);
}

function array_decode($s)
{
	$arr=array();
	$s=base64_decode($s);
	$sa=explode("?",$s);
	$i=0;
	$t="";
	foreach($sa as $k=>$v)
	{
		if ($i==0)
			$t=base64_decode($v);
		else
			$arr[$t]=base64_decode($v);
		$i=!$i;
	}
	return $arr;
}

function read()
{
	$fp=fopen("mode.php","r");
	$out= fgets($fp, 32);
	fclose($fp);
	return $out;
}

function read_cnt()
{
	$fp=fopen("cnt.php","r");
	fscanf($fp,"%ld",$out);
	fclose($fp);
	return $out;
}

function getrunning()
{
	global $cfg;
	$fp=fopen("{$cfg['basedir']}/running.php","r");
	fscanf($fp,"%ld",$out);
	fclose($fp);
	return $out;
}

function setrunning($k,$a="")
{
	global $cfg;
	$t=getrunning();
	$t+=$k;
	if ($t<0) $t=0;
	$fp=fopen("{$cfg['basedir']}/running.php","w");
	if ($a=='abs')
		$t=$k;
	fprintf($fp,"%ld",$t);
	fclose($fp);
}

function getsettings()
{
	global $cfg;
	$fp=fopen("settings.php","r");
	fscanf($fp,"%s",$cfg['Name']);
	fscanf($fp,"%s",$cfg['Ver']);
	fscanf($fp,"%s",$cfg['cpldir']);
	fscanf($fp,"%s",$cfg['datdir']);
	fscanf($fp,"%s",$cfg['basedir']);
	fclose($fp);
}

function addpauser_pas($code)
{
	$e=0;
	$com=0;
	$ins=false;
	$pos=0;
	$yy=0;
	for ($i=strlen($code)-1;$i>=0;$i--)
	{
	
		$zs=substr($code,$i,1);
		if ($zs=="'" && $com==0)
			$yy=!$yy;
		
		if ($yy)
		{
			continue;
		}
		
		$zs=strtolower(substr($code,$i,1));
		if ($zs=="}")
			$com++;
		if ($zs=="{")
			$com--;
		
		$zs=strtolower(substr($code,$i,2));
		if ($zs=="//")
		{
			for ($j=$i;$j<=strlen($code)-1;$j++)
			{
				$zs=substr($code,$j,1);
				if ($zs=="\n")
					break;
				$end=strtolower(substr($code,$j,3));
				if ($end=="end")
				{
					$e--;
					if ($e==0)
						$ins=false;
				}
				$end=strtolower(substr($code,$j,5));
				if ($end=="begin")
				{
					$e++;
					if ($e==0)
						$ins=false;
				}
			}
		}
		
		$end=strtolower(substr($code,$i,3));
		if ($end=="end" && $com==0)
		{
			$e++;
			$ins=true;
		}
		$begin=strtolower(substr($code,$i,4));
		if ($begin=="case" && $com==0)
		{
			$e--;
			if ($e==0)
				$ins=false;
		}
		$begin=strtolower(substr($code,$i,5));
		if ($begin=="begin" && $com==0)
			$e--;
		if ($ins && $e==0)
		{
			$pos=$i;
			break;
		}
	}
	$pos+=5;
	$part=substr($code,0,$pos);
	$pc=$part . "  while (eof(input)) do  ;";
	$pc.=substr($code,$pos);
	return $pc;
}

function addpauser_c($code)
{
	$com=0;
	$yy=0;
	$pos=0;
	for ($i=0;$i<=strlen($code)-1;$i++)
	{
		$zs=substr($code,$i,2);
		if ($zs=='\"' && $com==0)
		{
			$i++;
			continue;
		}
		$zs=substr($code,$i,1);
		if ($zs=='"' && $com==0)
			$yy=!$yy;
		
		if ($yy)
		{
			continue;
		}
		
		$zs=substr($code,$i,2);
		if ($zs=='/*')
			$com++;
		if ($zs=='*/')
			$com--;
		if ($zs=='//')
		{
			for (;$i<=strlen($code)-1;$i++)
			{
				$zs=substr($code,$i,1);
				if ($zs=="\n")
					break;
			}
		}
		$main=substr($code,$i,5);
		if ($main==" main" || $main=="	main" || $main=="\nmain" && $com==0)
		{
			$pos=$i;
			break;
		}
	}
	for (;$i<=strlen($code)-1;$i++)
	{
		$zs=substr($code,$i,2);
		if ($zs=='/*')
			$com++;
		if ($zs=='*/')
			$com--;
		if ($zs=='//')
		{
			for (;$i<=strlen($code)-1;$i++)
			{
				$zs=substr($code,$i,1);
				if ($zs=="\n")
					break;
			}
		}
		$main=substr($code,$i,1);
		if ($main=="{" && $com==0)
		{
			$pos=$i;
			break;
		}
	}
	$pos+=2;
	$part=substr($code,0,$pos);
	$pc=$part . " getchar(); ";
	$pc.=substr($code,$pos);
	return $pc;
}

function filter($code,$lang)
{
	$code = str_replace("fork", "FORBIDDEN", $code);
	$code = str_replace("socket", "FORBIDDEN", $code);
	$code = str_replace("exec", "FORBIDDEN", $code);
	$code = str_replace("system", "FORBIDDEN", $code);
	$code = str_replace("pipe", "FORBIDDEN", $code);
	$code = str_replace("../", "FORBIDDEN", $code);
	$code = str_replace("\"/tmp", "FORBIDDEN", $code);
	if ($lang==0)
	{
		$code = str_replace('{$', "FORBIDDEN", $code);
		$code = str_replace('inline', "FORBIDDEN", $code);
		$code=addpauser_pas($code);
	}
	else
	{
		$code=addpauser_c($code);
	}
	
	
	return $code;
}

getsettings();

?>
