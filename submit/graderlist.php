<?php
require_once("../include/header.php");
gethead(1,"","评测机列表");
    $q=new DataAccess();
?>
<div class='row-fluid'>
<?php if(有此权限('管理评测')) { ?>
<a class="btn btn-info pull-left" href="editgrader.php?action=add">添加新评测机</a>
<?php } ?>
<table class='table table-striped table-condensed table-bordered fiexd'>
  <tr>
    <th>GRID</th>
    <th>名称</th>
    <th>评测次数</th>
    <th>状态</th>
    <th>版本</th>
    <th>备注</th>
    <th>地址</th>
    <th>优先级</th>
<?php if(有此权限('管理评测')) { ?>
    <th>操作</th>
  </tr>
<?php } ?>
<?php
    $LIB->func_socket();

    $p=new DataAccess();
    $sql="select * from grader";
    $cnt=$p->dosql($sql);
    for ($i=$st;$i<$cnt ;$i++) {
        $d=$p->rtnrlt($i);
        $s['action']="state";
        $debug=$tmp=httpsocket($d['address'],$s);
        $tmp=array_decode($tmp);
        if ($tmp==array()) {
            $tmp['name']="无法连接";
            $tmp['state']="未知";
            $tmp['ver']="未知";
            $tmp['cnt']="未知";
        }
?>
  <tr>
    <td><?php echo $d['grid'] ?></td>
    <td><?php echo $tmp['name'] ?></td>
    <td><?php echo $tmp['cnt'] ?></td>
    <td><?php echo $tmp['state'] ?></td>
    <td><?php echo $tmp['ver'] ?></td>
    <td><?php echo sp2n(htmlspecialchars($d['memo'])) ?></td>
    <td><a href='<?=$d['address']?>'><?php echo $d['address'] ?></a></td>
    <td><?php echo $d['priority'] ?></td>
<?php if(有此权限('管理评测')) { ?>
    <td><a href="editgrader.php?action=edit&amp;grid=<?php echo $d['grid'] ?>">修改</a> <a href="doeditgrader.php?action=start&amp;grid=<?php echo $d['grid'] ?>">启动</a> <a href="doeditgrader.php?action=stop&amp;grid=<?php echo $d['grid'] ?>">关闭</a></td>
<?php } ?>
  </tr>
<?php
    }
?>
</table>
</div>
<?php
    include_once("../include/footer.php");
?>
