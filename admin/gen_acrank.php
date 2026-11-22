<?php
require_once("../include/header.php");
gethead(1,"生成等级","生成等级");
requirePost();

?>
<div class='page'>
开始计算……<br />
<?php



$accounts = Account::directory();

foreach ($accounts as $i => $d) {
  $uid = (int)($d['uid']);
  $username = ($d['realname']);
  $arr = Ranking::recalculate($uid);
  $submt = $arr[0];
  $accpd = $arr[1];
  $grade = $arr[2];
  if ($grade > 0) {
    echo "$username = <span class=ok>$grade</span> "; //<br />";
    echo "(<span class=no>$accpd</span>/";
    echo "<span class=did>$submt</span>). ";
  }
  flush();
}

?>

<span class=ok>计算完成！</span>
</div>
<?php
require_once("../include/footer.php");
?>
