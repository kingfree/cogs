<?php
require_once("../include/header.php");
gethead(1,"","页面列表");
$p=new DataAccess();
$q=new DataAccess();
?>
<div class='container-fluid'>
<?php if(有此权限('修改页面')) { ?>
<a href="editpage.php?action=add" class="btn btn-info pull-left">添加新页面</a>
<?php } ?>

<?php
$sql="select page.*,groups.*,userinfo.nickname as name,userinfo.uid from page,groups,userinfo where";

$sql.=" userinfo.uid=page.uid and groups.gid=page.group";

if ($_GET['key']!="")
$sql.=" and (page.text like '%{$_GET[key]}%' or page.title like '%{$_GET[key]}%')";

$sql .= " order by title asc";

$cnt=$p->dosql($sql);
$st=检测页面($cnt, $_GET['page']);
?>
<form method="get" action="" class='center'>
搜索页面
<input name="key" type="text" class='search-query input-medium' value='<?=$_GET['key']?>'/>
<button type="submit" class='btn'>搜索</button>
</form>
<table id="pagelist" class='table table-striped table-condensed table-bordered fiexd'>
<thead><tr>
<th>AID</th>
<th width=50%>页面标题</th>
<th>创建时间</th>
<th>修改时间</th>
<th>开放分组</th>
<?php if(有此权限('查看页面')) { ?>
<th class=admin>添加用户</th>
<?php } ?>
<?php if(有此权限('修改页面')) { ?>
<th class=admin>编辑</th>
<?php } ?>
</tr></thead>
<?php
for ($i=$st;$i<$cnt && $i<$st+$SET['style_pagesize'] ;$i++) {
    $d=$p->rtnrlt($i);
    if($_GET['key'] && $cnt == 1)
        echo "<script language='javascript'>location='page.php?aid={$d['aid']}';</script>";
    if ($d['force']>$_SESSION['readforce'] && !($_SESSION['admin']>0)) continue;
?>
<tr>
<td><?php echo $d['aid'] ?></td>
<td align=left><b><a href="page.php?aid=<?=$d['aid'] ?>"><?=$d['title'] ?></b></td>
<td><?=date('Y-m-d', $d['time']) ?></td>
<td><?=date('Y-m-d', $d['etime']) ?></td>
<td><a href="../user/index.php?gid=<?=$d['gid'] ?>" target="_blank"><?=$d['gname'] ?></a></td>
<?php if(有此权限('查看页面')) { ?>
<td><?=$d['name']?></td>
<?php } ?>
<?php if(有此权限('修改页面')) { ?>
<td><a href="editpage.php?action=edit&aid=<?=$d['aid']?>">修改</a></td>
<?php } ?>
</tr>
<?php } ?>
</table>
<? 分页($cnt, $_GET['page'], '?key='.$_GET['key'].'&'); ?>
</div>

<?php
include_once("../include/footer.php");
?>
