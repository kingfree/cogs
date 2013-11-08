<?php
require_once("../include/header.php");
gethead(1,"超级用户","执行备份与恢复");
$LIB->func_socket();
过滤();

$filename=$_POST['filename']."_".date('His', time());
$ima=getcwd();
chdir($SET['dir_databackup']);
?>

<div class="page">
<a href="backup.php">返回备份与恢复页面</a>
<?
if($_POST['backtype'] == "database") {
    mkdir("database");
    chdir("database");
    $cmd = "mysqldump --host={$cfg['data_server']} --user={$cfg['data_uid']} --password={$cfg['data_pwd']} --databases {$cfg['data_database']} > {$filename}.sql";
    // echo "<code>$cmd</code>";
    flush();
    exec($cmd);
    exec("gzip $filename.sql");
    exec("tree -ah > /tmp/cogs_back_info.log");
    echo "<p><pre>".file_get_contents("/tmp/cogs_back_info.log")."</pre><p>";
    flush();
    if(file_exists("$filename.sql.gz")) {
        echo "<span class=ok>成功导出数据库<code>$filename.sql.gz</code>！</span>";
    } else {
        echo "<span class=no>导出数据库失败！</span>";
    }
} else if($_POST['backtype'] == "usercode") {
    mkdir("usercode");
    chdir("usercode");
    $cmd = "tar -zcvf {$filename}.tar.gz {$SET['dir_source']} > /tmp/cogs_back_doing.log";
    // echo "<code>$cmd</code>";
    flush();
    exec($cmd);
    echo "<p><textarea class='textarea'>".file_get_contents("/tmp/cogs_back_doing.log")."</textarea><p>";
    flush();
    exec("tree -ah > /tmp/cogs_back_info.log");
    echo "<p><pre>".file_get_contents("/tmp/cogs_back_info.log")."</pre><p>";
    flush();
    if(file_exists("$filename.tar.gz")) {
        echo "<span class=ok>成功导出用户提交的代码！</span>";
    } else {
        echo "<span class=no>导出用户代码失败！</span>";
    }
} else if($_POST['backtype'] == "compcode") {
    mkdir("compcode");
    chdir("compcode");
    $cmd = "tar -zcvf {$filename}.tar.gz {$SET['dir_competition']} > /tmp/cogs_back_doing.log";
    // echo "<code>$cmd</code>";
    flush();
    exec($cmd);
    echo "<p><textarea class='textarea'>".file_get_contents("/tmp/cogs_back_doing.log")."</textarea><p>";
    flush();
    exec("tree -ah > /tmp/cogs_back_info.log");
    flush();
    echo "<p><pre>".file_get_contents("/tmp/cogs_back_info.log")."</pre><p>";
    if(file_exists("$filename.tar.gz")) {
        echo "<span class=ok>成功导出用户比赛提交的代码！</span>";
    } else {
        echo "<span class=no>导出比赛代码失败！</span>";
    }
} else if($_POST['backtype'] == "testdata") {
    mkdir("testdata");
    chdir("testdata");
    $cmd = "tar -zcvf {$filename}.tar.gz {$cfg['testdata']} > /tmp/cogs_back_doing.log";
    // echo "<code>$cmd</code>";
    flush();
    exec($cmd);
    echo "<p><textarea class='textarea'>".file_get_contents("/tmp/cogs_back_doing.log")."</textarea><p>";
    flush();
    exec("tree -ah > /tmp/cogs_back_info.log");
    flush();
    echo "<p><pre>".file_get_contents("/tmp/cogs_back_info.log")."</pre><p>";
    if(file_exists("$filename.tar.gz")) {
        echo "<span class=ok>成功导出全部测试数据！</span>";
    } else {
        echo "<span class=no>导出测试数据失败！</span>";
    }
} else if($_POST['backtype'] == "showback") {
    echo "<h4>备份文件夹<code>{$SET['dir_databackup']}</code>下的内容如下：</h4>";
    flush();
    exec("tree -ah > /tmp/cogs_back_info.log");
    echo "<pre>".file_get_contents("/tmp/cogs_back_info.log")."</pre>";
} else {
    异常("未定义的操作！",取路径("admin/backup.php"));
}
?>
<a href="backup.php">返回备份与恢复页面</a>
</div>

<?
chdir($ima);

include_once("../include/footer.php");
?>

