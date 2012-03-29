<?php

class SQL_Expression
{
	public $key,$value,$predication;
	public function __construct($t_key,$t_value,$t_predication)
	{
		$this->key=$t_key;
		$this->value=$t_value;
		$this->predication=$t_predication;
	}
};

class SQL_Where
{
	public $logic,$sub;
	public function __construct($t_logic,$t_sub)
	{
		$this->logic=$t_logic;
		$this->sub=$t_sub;
	}
	public function GetSQL()
	{
		$first=true;
		$SQL=" ( ";
		foreach ($this->sub as $E)
		{
			if (!$first)
				$SQL.= ' '.$this->logic.' ';
			$first=false;
			if ($E->predication)
				$SQL.=$E->key . $E->predication . $E->value;
			else
				$SQL.=$E->GetSQL();
		}
		$SQL.=" ) ";
		return $SQL;
	}
};

class DataAccess
{
	private $conn,$result,$rows;
	
	private function fail()
	{
		echo "数据库连接失败";
		exit;
	}
	
	public function __construct()
	{
		global $cfg;
		@$this->conn=mysql_connect($cfg['data_server'],$cfg['data_uid'],$cfg['data_pwd'])
		or $this->fail();
		mysql_select_db($cfg['data_database'],$this->conn);
		mysql_query("set names utf8");
	}
	
	public function __destruct() 
	{
		@mysql_close($this->conn);
	}
	
	public function dosql($SQL)
	{
		global $Query_Times;
		$Query_Times++;
		if (!empty($this->result))
			@mysql_free_result($this->result);
		
		$this->result=mysql_query($SQL) or die(mysql_error());
		$this->rows=@mysql_num_rows($this->result);
		return $this->rows;
	}
	
	public function rtnrlt($rownum)
	{
		mysql_data_seek($this->result,$rownum);
		$data=@mysql_fetch_array($this->result);
		return $data;
	}
	
	public function Query($SQL)
	{
		global $Query_Times;
		$Query_Times++;
		if (!empty($this->result))
			@mysql_free_result($this->result);
		
		$this->result=mysql_query($SQL) or die(mysql_error());
		$this->rows=@mysql_num_rows($this->result);
		return $this->rows;
	}
	
	public function Update($table,$keys,$condition)
	{
		$first=true;
		$SQL="UPDATE `";
		$SQL.=$table;
		$SQL.="` SET ";
		foreach ($keys as $E)
		{
			if (!$first)
				$SQL.=" , ";
			$first=false;
			$SQL.=$E->key . $E->predication . $E->value;
		}
		if ($condition!=array())
		{
			$SQL.=" WHERE ";
			$SQL.=$condition->GetSQL();
		}
		
		//$this->Query($SQL);
	}
};
?>