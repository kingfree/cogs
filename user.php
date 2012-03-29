<?

require_once("./include/stdhead.php");
gethead(1,"admin","首页");
$p=new DataAccess();
$q=new DataAccess();
for($i=690; $i<705; $i++) {
    $sql="select * from userinfo where uid=$i";
    $cnt=$p->dosql($sql);
    if(!$cnt) {
        $sql = "INSERT INTO `cojs`.`userinfo` (`uid`, `usr`, `pwdhash`, `pwdtipques`, `pwdtipanshash`, `nickname`, `readforce`, `admin`, `portrait`, `accepted`, `memo`, `regtime`, `realname`, `style`, `gbelong`, `submited`, `grade`, `email`, `lastip`) VALUES ('$i', 'User$i', '3b46d8d37a513c4a1f36bfa95aca77d3', '', '3b46d8d37a513c4a1f36bfa95aca77d3', '恢复用户$i', '0', '0', '0', '0', NULL, '0', '', '1', '1', '0', '', '', '');";
        $ok=$p->dosql($sql);
        if($ok) echo $sql; else echo "<span style='color:red;'>".$sql."<span>";
    }
}
?>
