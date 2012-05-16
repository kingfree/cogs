<?php
require_once("../include/stdhead.php");
gethead(1,"","评测机状态");
    $q=new DataAccess();
?>

<?php if(有此权限($q, '管理评测')) { ?>
<a class="admin_big" href="../admin/grader/editgrader.php?action=add">添加新评测机</a>
<?php } ?>
<p />
<center>
<table width="100%" border="1">
  <tr>
    <th scope="col">GRID</th>
    <th scope="col">名称</th>
    <th scope="col">评测次数</th>
    <th scope="col">状态</th>
    <th scope="col">版本</th>
    <th scope="col">备注</th>
<?php if(有此权限($q, '管理评测')) { ?>
    <th bgcolor="#99FFCC"  scope="col">地址</th>
    <th bgcolor="#99FFCC"  scope="col">优先级</th>
    <th bgcolor="#99FFCC"  scope="col">操作</th>
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
<?php if(有此权限($q, '管理评测')) { ?>
    <td class="admin"><a href='<?=$d['address']?>'><?php echo $d['address'] ?></a></td>
    <td class="admin"><?php echo $d['priority'] ?></td>
    <td class="admin"><a href="../admin/grader/editgrader.php?action=edit&amp;grid=<?php echo $d['grid'] ?>">修改</a> <a href="../admin/grader/doeditgrader.php?action=start&amp;grid=<?php echo $d['grid'] ?>">启动</a> <a href="../admin/grader/doeditgrader.php?action=stop&amp;grid=<?php echo $d['grid'] ?>">关闭</a></td>
<?php } ?>
  </tr>
<?php
    }
?>
</table>
</center>

<?php
    include_once("../include/stdtail.php");
?>
