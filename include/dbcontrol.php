<?
class DBControl
{
	public function deletetable()
	{
		$p=new DataAccess();
		$sql="drop table if exists settings";
		$p->dosql($sql);
		$sql="drop table if exists userinfo";
		$p->dosql($sql);
		$sql="drop table if exists problem";
		$p->dosql($sql);
		$sql="drop table if exists submit";
		$p->dosql($sql);
		$sql="drop table if exists comments";
		$p->dosql($sql);
		$sql="drop table if exists groups";
		$p->dosql($sql);
		$sql="drop table if exists category";
		$p->dosql($sql);
		$sql="drop table if exists tag";
		$p->dosql($sql);
		$sql="drop table if exists compbase";
		$p->dosql($sql);
		$sql="drop table if exists comptime";
		$p->dosql($sql);
		$sql="drop table if exists compscore";
		$p->dosql($sql);
		$sql="drop table if exists grader";
		$p->dosql($sql);
	}
	
	public function createtable()
	{
		chdir("../../include/sql");
		$p=new DataAccess();
		$fp=fopen("c_settings.sql","r");
		$sql=rfile($fp);
		fclose($fp);
		$p->dosql($sql);
		
		$fp=fopen("c_userinfo.sql","r");
		$sql=rfile($fp);
		fclose($fp);
		$p->dosql($sql);
		
		$fp=fopen("c_problem.sql","r");
		$sql=rfile($fp);
		fclose($fp);
		$p->dosql($sql);
		
		$fp=fopen("c_submit.sql","r");
		$sql=rfile($fp);
		fclose($fp);
		$p->dosql($sql);
	
		$fp=fopen("c_comments.sql","r");
		$sql=rfile($fp);
		fclose($fp);
		$p->dosql($sql);
		
		$fp=fopen("c_groups.sql","r");
		$sql=rfile($fp);
		fclose($fp);
		$p->dosql($sql);
		
		$fp=fopen("c_category.sql","r");
		$sql=rfile($fp);
		fclose($fp);
		$p->dosql($sql);
	
		$fp=fopen("c_tag.sql","r");
		$sql=rfile($fp);
		fclose($fp);
		$p->dosql($sql);
		
		$fp=fopen("c_compbase.sql","r");
		$sql=rfile($fp);
		fclose($fp);
		$p->dosql($sql);
	
		$fp=fopen("c_comptime.sql","r");
		$sql=rfile($fp);
		fclose($fp);
		$p->dosql($sql);
		
		$fp=fopen("c_compscore.sql","r");
		$sql=rfile($fp);
		fclose($fp);
		$p->dosql($sql);
		
		$fp=fopen("c_grader.sql","r");
		$sql=rfile($fp);
		fclose($fp);
		$p->dosql($sql);
		
		$sql="insert into userinfo(uid,usr,nickname,readforce,admin,regtime,pwdhash) values (0, 'root','根管理员',999,2, ".time().",'".encode("")."')";
		$p->dosql($sql);
		
		$sql="insert into problem(pid,probname,filename,detail,addtime,datacnt) values(0,'A+B Problem','aplusb','<p>给予两个数，请计算两数之和。</p>',".time().",10)";
		$p->dosql($sql);
		
		$sql="insert into groups(gname,memo) values('默认分组','当一个用户注册以后自动加入默认分组')";
		$p->dosql($sql);
		
		$sql="insert into category(cname,memo) values('默认分类','默认的分类')";
		$p->dosql($sql);
		
		require("default.sql");
	}
}
?>