<?php
require_once("../include/stdhead.php");
gethead(1,"","分类列表");
$p=new DataAccess();
?>
<?php if ($_SESSION['admin']>0){ ?>
<a class="admin_big" href="../admin/category/editcate.php?action=add">添加新分类</a>
<?php } ?>
<table id="cate_tags">
<?$sql="select * from category order by cname";
$cnt=$p->dosql($sql);
for ($i=$st;$i<$cnt;$i++) {
    $d=$p->rtnrlt($i);
    if($i % 8 == 0 && $i) echo "<tr>";
?>
<td><a href="../problem/problist.php?caid=<?=$d['caid']?>" title="<?=sp2n(htmlspecialchars($d['memo']))?>" target="_blank"><?=$d['cname']?></a></td>
<? } ?>
</table>
<?
    $sql="select * from category order by cname";
    $cnt=$p->dosql($sql);
?>
<table id="catelist">
  <tr>
    <th>分类</th>
    <th>备注</th>
    <?php if ($_SESSION['admin']>0){ ?>
    <th class=admin>操作</th>
    <?php } ?>
  </tr>
<?php 
for ($i=$st;$i<$cnt;$i++) {
    $d=$p->rtnrlt($i);
?>
  <tr>
    <td><a href="../problem/problist.php?caid=<?php echo $d['caid'] ?>"><?php echo $d['cname'] ?></a></td>
    <td><?php echo sp2n(htmlspecialchars($d['memo'])) ?></td>
    <?php if ($_SESSION['admin']>0){ ?>
    <td class=admin><a href="../admin/category/editcate.php?action=edit&caid=<?php echo $d['caid'] ?>">修改</a></td><?php } ?>
  </tr>
<?php
    }
?>
</table>

<?php
    include_once("../include/stdtail.php");
?>
