<?php
require_once("../include/header.php");
gethead(1,"","页面列表");
$p=new DataAccess();
$q=new DataAccess();
?>
<div class='row-fluid'>
<?php if(有此权限('修改页面')) { ?>
<a href="editpage.php?action=add" class="btn btn-info pull-left">添加新页面</a>
<?php } ?>

<?php
$sql="select page.*,groups.*,userinfo.nickname,userinfo.email,userinfo.realname from page,groups,userinfo where page.force<={$_SESSION['readforce']} and ";

$sql.=" userinfo.uid=page.uid and groups.gid=page.group";

if ($_GET['key']!="")
$sql.=" and (page.text like '%{$_GET[key]}%' or page.title like '%{$_GET[key]}%')";

$sql .= " order by title asc";

$cnt=$p->dosql($sql);
$st=检测页面($cnt, $_GET['page']);
?>
<form method="get" action="" class='form-search center'>
<div class='input-append pull-right'>
<input name="key" type="text" class='search-query input-medium' value='<?=$_GET['key']?>' placeholder='搜索页面'/>
<button type='submit' class='btn'><i class='icon icon-search'></i></button>
</div>
</form>
<table id="pagelist" class='table table-striped table-condensed table-bordered fiexd'>
<thead><tr>
<th>AID</th>
<?php if(有此权限('修改页面')) { ?>
<th class=admin>编辑</th>
<?php } ?>
<th width=50%>页面标题</th>
<th>创建时间</th>
<th>修改时间</th>
<th>开放分组</th>
<th>作者</th>
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
<?php if(有此权限('修改页面')) { ?>
<td><a href="editpage.php?action=edit&aid=<?=$d['aid']?>">修改</a></td>
<?php } ?>
<td align=left><b><a href="page.php?aid=<?=$d['aid'] ?>"><?=$d['title'] ?></b></td>
<td><?=date('Y-m-d', $d['time']) ?></td>
<td><?=date('Y-m-d', $d['etime']) ?></td>
<td><a href="../user/index.php?gid=<?=$d['gid'] ?>" target="_blank"><?=$d['gname'] ?></a></td>
<td><a href='../user/detail.php?uid=<?=$d['uid']?>' target='_blank'>
<?=gravatar::showImage($d['email']);?>
<?if(有此权限("查看用户")) echo $d['realname']; else echo $d['nickname'];?>
</a></td>
</tr>
<?php } ?>
</table>
<? 分页($cnt, $_GET['page'], '?key='.$_GET['key'].'&'); ?>
</div>

<?php
include_once("../include/footer.php");
?>
