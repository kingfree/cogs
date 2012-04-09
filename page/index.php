<?php
require_once("../include/stdhead.php");
gethead(1,"","页面列表");
$p=new DataAccess();
$q=new DataAccess();
?>

<?php if ($_SESSION['admin']>0){ ?>
<span class="admin_big"><a href="editpage.php?action=add">添加新页面</a></span>
<?php } ?>

<?php
$sql="select page.*,groups.*,userinfo.nickname as name,userinfo.uid from page,groups,userinfo where";

$sql.=" userinfo.uid=page.uid and groups.gid=page.group";

if ($_GET['key']!="")
$sql.=" and (page.text like '%{$_GET[key]}%' or page.title like '%{$_GET[key]}%')";

$sql .= " order by title asc";

$cnt=$p->dosql($sql);
$totalpage=(int)(($cnt-1)/$SET['style_pagesize'])+1;
if(!$_GET['page']) {
    $_GET['page']=1;
    $st=0;
} else {
    if ($_GET[page]<1 || $_GET[page]>$totalpage)
        异常("页面错误！");
    else
        $st=(($_GET[page]-1)*$SET['style_pagesize']);
}
?>
<form id="search_page" name="search_page" method="get" action="">
搜索页面
<input name="key" type="text" id="key" value="<?php echo $_GET['key'] ?>" />
<input name="sc" type="submit" id="sc" value="搜索"/>
</form>
<? 分页($cnt, $_GET['page'], '?key='.$_GET['key'].'&'); ?>
<table id="pagelist">
<tr>
<th>AID</th>
<th width=50%>页面标题</th>
<th>创建时间</th>
<th>修改时间</th>
<th>开放分组</th>
<?php if ($_SESSION['admin']>0){ ?>
<th class=admin>添加用户</th>
<th class=admin>权限</th>
<th class=admin>编辑</th>
<?php } ?>
</tr>
<?php
for ($i=$st;$i<$cnt && $i<$st+$SET['style_pagesize'] ;$i++) {
    $d=$p->rtnrlt($i);
    if($_GET['key'] && $cnt == 1)
        echo "<script language='javascript'>location='page.php?aid={$d['aid']}';</script>";
    if ($d['force']>$_SESSION['readforce'] && !($_SESSION['admin']>0)) continue;
?>
<tr>
<td><?php echo $d['aid'] ?></td>
<th align=left><a href="page.php?aid=<?=$d['aid'] ?>"><?=$d['title'] ?></h></td>
<td><?=date('Y-m-d', $d['time']) ?></td>
<td><?=date('Y-m-d', $d['etime']) ?></td>
<td><a href="../information/userlist.php?gid=<?=$d['gid'] ?>" target="_blank"><?=$d['gname'] ?></a></td>
<?php if ($_SESSION['admin']>0) { ?>
<td class=admin><?=$d['name']?></td>
<td class=admin><?=$d['force']?></td>
<th class=admin><a href="editpage.php?action=edit&aid=<?=$d['aid']?>">修改</a></th>
<?php } ?>
</tr>
<?php } ?>
</table>

<?php
include_once("../include/stdtail.php");
?>
