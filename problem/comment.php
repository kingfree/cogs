<?php
require_once("../include/header.php");
gethead(1,"","题目评论");
$p=new DataAccess();
$uid=(int)$_SESSION['ID'];
$pid=(int)$_GET['pid'];
$aid=(int)$_GET['aid'];
if($pid<0) { $aid=-$pid; $pid=0; }
$cid=(int)$_GET['cid'];
if(!$uid)
    异常("非注册用户！", 取路径("problem/comments.php?pid={$pid}"));

if($pid) {
    $sql="select probname from problem where pid={$pid} limit 1";
    $cnt=$p->dosql($sql);
    if($cnt) {
        $d=$p->rtnrlt(0);
    } else
        异常("无此题目！", 取路径("problem/commentlist.php"));
} else if($cid) {
    $sql="select comments.* from comments where comments.cid={$cid} limit 1";
    $cnt=$p->dosql($sql);
    if($cnt) {
        $d=$p->rtnrlt(0);
        $pid=$d['pid'];
        if($uid != $d['uid'])
            异常("不可更改他人评论！", 取路径("problem/comments.php?pid={$pid}"));
    } else
        异常("无此评论！", 取路径("problem/comments.php?pid={$pid}"));
} else if($aid) {
    $sql="select title from page where aid={$aid} limit 1";
    $cnt=$p->dosql($sql);
    if($cnt) {
        $d=$p->rtnrlt(0);
    } else
        异常("无此页面！", 取路径("page/index.php"));
    $pid=0;
} else {
    异常("什么也没找到！");
}
?>
<div class='container-fluid'>
<form method="post" action="sendcomments.php" class='form-horizontal'>
<div class='modal-header'>
<h3>发表
<? if($pid) { ?>
<a href="problem.php?pid=<?=$pid?>" target="_blank"><?=$d['probname']?></a>
<? } else if($aid) { ?>
<a href="../page/page.php?aid=<?=$aid?>" target="_blank"><?=$d['title']?></a>
<? } ?>
的评论</h3>
</div>
<div class='modal-body'>
<textarea name="detail" class='textarea'><?php echo $d['detail'] ?></textarea>
<br />
<? if($pid) { ?>
    <label><input id="showcode" name="showcode" type="checkbox" value="1" <?php if($d['showcode']){ ?> checked="checked" <?php } ?> />允许查看你提交的代码</label>
<? } else { ?>
    <input name="showcode" type="hidden"  value="0" />
<? } ?>
</div>
<div class='modal-footer'>
<input name="pid" type="hidden" id="pid" value="<?php echo $pid?$pid:-$aid ?>" />
<input name="cid" type="hidden" id="cid" value="<?php echo $cid ?>" />
<? if($pid) { ?>
<a class='btn' href='comments.php?pid=<?=$pid?>'>返回评论列表</a>
<? } else if($aid) { ?>
<a class='btn' href='comments.php?aid=<?=$aid?>'>返回评论列表</a>
<? } ?>
<button type="submit" class='btn btn-primary'>发表评论</button>
</div>
</form>
</div>
</div>

<?php
include_once("../include/footer.php");
?>
