<?php
require_once("../include/stdhead.php");
gethead(1,"","分组列表");
$p=new DataAccess();
$q=new DataAccess();
?>
<?php if(有此权限($q, '分组管理')) { ?>
<a href="../admin/group/editgroup.php?action=add" class="admin_big">添加新分组</a>
<?php } ?>
<p />
<table id="grouplist">
  <tr>
    <th>分组名</th>
    <th>上级分组</th>
    <th>备注</th>
    <th>组管理员</th>
    <?php if(有此权限($q, '普通用户')) { ?>
    <th>加入</th>
    <?php } ?>
    <?php if(有此权限($q, '分组管理')) { ?>
    <th class=admin>操作</th>
    <?php } ?>
  </tr>
<?php
    $sql="select groups.*,userinfo.uid,userinfo.nickname from groups,userinfo where groups.adminuid=userinfo.uid order by gname";
    $cnt=$p->dosql($sql);
    for ($i=$st;$i<$cnt;$i++)
    {
        $d=$p->rtnrlt($i);
        if ($d['uid']==$_SESSION['ID'])
            $groupadmin=true;
?>
  <tr>
    <td><b><a href="userlist.php?gid=<?=$d['gid']?>"><?=$d['gname']?></a></b></td>
    <td><?php
if ($d['parent']!=-1)
{
    $sql="select * from groups where gid={$d['parent']}";
    $q->dosql($sql);
    $e=$q->rtnrlt(0);
?><a href="userlist.php?gid=<?=$e['gid']?>"><?=$e['gname']?></a><?php 
}
?></td>
    <td><?=sp2n(htmlspecialchars($d['memo'])) ?></td>
    <td><a href="../user/detail.php?uid=<?=$d['uid']?>"><?=$d['nickname']?></a></td>
    <?php if(有此权限($q, '普通用户')) { ?>
    <td>
<form id="form" name="form" method="post" action="../mail/index.php">
<input name="fromid" type="hidden" value=<?=$uid?> />
<input name="toid" type="hidden" value=<?=$d['uid']?> />
<input name="title" type="hidden" value="申请加入：<?=$d['gname']?>" />
<input name="text" type="hidden" value="请输入你的加入原因，或你的个人信息，以通过组管理员的验证。" />
<input type="submit" name="Submit" value="申请" class="LinkButton"/>
</form>
    </td>
    <?php } ?>
    <?php if(有此权限($q, '分组管理')) { ?>
    <td class=admin><a href="../admin/group/editgroup.php?action=edit&gid=<?=$d['gid'] ?>">修改</a></td>
    <?php } ?>
  </tr>
<?php
    }
?>
</table>
<?php
    include_once("../include/stdtail.php");
?>
