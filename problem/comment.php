<?php
require_once("../include/header.php");
gethead(1,"","题目评论");
$p=new DataAccess();
$uid=(int)$_SESSION['ID'];
$pid=(int)$_GET['pid'];
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
    $sql="select comments.*,problem.probname from comments,problem where comments.cid={$cid} AND comments.pid=problem.pid limit 1";
    $cnt=$p->dosql($sql);
    if($cnt) {
        $d=$p->rtnrlt(0);
        $pid=$d['pid'];
        if($uid != $d['uid'])
            异常("不可更改他人评论！", 取路径("problem/comments.php?pid={$pid}"));
    } else
        异常("无此评论！", 取路径("problem/comments.php?pid={$pid}"));
} else {
    $pid=0;
}
?>
<div class='container-fluid'>
<form method="post" action="sendcomments.php" class='form-horizontal'>
<div class='modal-header'>
<h3>发表 <a href="problem.php?pid=<?=$pid?>" target="_blank"><?=$d['probname']?></a> 的评论</h3>
</div>
<div class='modal-body'>
<textarea name="detail" class='textarea'><?php echo $d['detail'] ?></textarea>
<br />
<label><input id="showcode" name="showcode" type="checkbox" value="1" <?php if($d['showcode']){ ?> checked="checked" <?php } ?> />允许查看你提交的代码</label>
</div>
<div class='modal-footer'>
<input name="pid" type="hidden" id="pid" value="<?php echo $pid ?>" />
<input name="cid" type="hidden" id="cid" value="<?php echo $cid ?>" />
<a class='btn' href='comments.php?pid=<?=$pid?>'>返回评论列表</a>
<button type="submit" class='btn btn-primary'>发表评论</button>
</div>
</form>
</div>
</div>

<?php
include_once("../include/footer.php");
?>
