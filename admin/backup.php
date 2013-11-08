<?php
require_once("../include/header.php");
gethead(1,"超级用户","备份与恢复");
?>
<div class="page">

<form method="post" action="dobackup.php" class='form-inline'>
<input name="backtype" type="hidden" value="showback" />
<button type="submit" class='btn btn-success'>查看已备份的数据</button>
</form>

<hr />

<form method="post" action="dobackup.php" class='form-inline'>
<h4>数据库　(database)</h4>
<input name="backtype" type="hidden" value="database" />
<div class="input-prepend input-append">
<span class="add-on"><?=$SET['dir_databackup']."database/"?></span>
<input type="text" name="filename" value="<?=date('Ymd', time())?>" />
<span class="add-on">_&lt;时间&gt;.sql.gz</span>
</div>
<button type="submit" class='btn btn-primary pull-right'>备份数据库</button>
</form>

<form method="post" action="dobackup.php" class='form-inline'>
<h4>用户代码(usercode)</h4>
<input name="backtype" type="hidden" value="usercode" />
<div class="input-prepend input-append">
<span class="add-on"><?=$SET['dir_databackup']."usercode/"?></span>
<input type="text" name="filename" value="<?=date('Ymd', time())?>" />
<span class="add-on">_&lt;时间&gt;.tar.gz</span>
</div>
<button type="submit" class='btn btn-warning pull-right'>备份用户代码</button>
</form>

<form method="post" action="dobackup.php" class='form-inline'>
<h4>比赛代码(compcode)</h4>
<input name="backtype" type="hidden" value="compcode" />
<div class="input-prepend input-append">
<span class="add-on"><?=$SET['dir_databackup']."compcode/"?></span>
<input type="text" name="filename" value="<?=date('Ymd', time())?>" />
<span class="add-on">_&lt;时间&gt;.tar.gz</span>
</div>
<button type="submit" class='btn btn-warning pull-right'>备份比赛代码</button>
</form>


<form method="post" action="dobackup.php" class='form-inline'>
<h4>测试数据(testdata)</h4>
<input name="backtype" type="hidden" value="testdata" />
<div class="input-prepend input-append">
<span class="add-on"><?=$SET['dir_databackup']."testdata/"?></span>
<input type="text" name="filename" value="<?=date('Ymd', time())?>" />
<span class="add-on">_&lt;时间&gt;.tar.gz</span>
</div>
<button type="submit" class='btn btn-danger pull-right'>备份测试数据</button>
</form>

</div>
<?php
include_once("../include/footer.php");
?>

