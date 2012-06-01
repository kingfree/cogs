<?php
require_once("../include/header.php");
gethead(1,"","分类列表");
$p=new DataAccess();
$q=new DataAccess();
?>
<div class='container'>
<?php if(有此权限('修改分类')) { ?>
<a href="editcate.php?action=add" class="btn btn-info">添加新分类</a>
<?php } ?>
<?
    $sql="select * from category order by cname";
    $cnt=$p->dosql($sql);
?>
<table id="catelist" class='table table-striped table-condensed table-bordered fiexd'>
  <tr>
    <?php if(有此权限('修改分类')) { ?>
    <th class='admin' width='40px'>操作</th>
    <?php } ?>
    <th width='240px'>分类</th>
    <th>备注</th>
  </tr>
<?php 
for ($i=$st;$i<$cnt;$i++) {
    $d=$p->rtnrlt($i);
?>
  <tr>
    <?php if(有此权限('修改分类')) { ?>
    <td class=admin><a href="editcate.php?action=edit&caid=<?php echo $d['caid'] ?>">修改</a></td><?php } ?>
    <td><a href="index.php?caid=<?php echo $d['caid'] ?>"><?php echo $d['cname'] ?></a></td>
    <td><?php echo sp2n(htmlspecialchars($d['memo'])) ?></td>
  </tr>
<?php
    }
?>
</table>
</div>
<?php
    include_once("../include/footer.php");
?>
