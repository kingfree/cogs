<?php
require_once("../include/header.php");
gethead(1,"","比赛成绩");

$rounds = ContestRound::scoreboardRules($_GET['ctid']);
$cnt = count($rounds);
if (!$cnt)
    异常("未查询到记录！");
$d=($rounds[0] ?? false);
if (!$d['showscore'] && !Privilege::allows('查看比赛'))
    异常("成绩还未公布！");
if(time() < $d['starttime'] && !Privilege::allows('查看比赛'))
    异常("比赛尚未开始，不能查看关于题目的任何信息！");
$end = time() > $d['endtime'];
$pbs = array_filter(explode(':', $d['contains']));
$problems = array_column(Problem::selected($pbs), null, 'pid');
$entrants = ContestEntry::roundResults($_GET['ctid']);
?>
<div class='row-fluid'>
<table id="contest_report" class='table table-striped table-condensed table-bordered fiexd'>
<thead>
  <tr>
    <th style="min-width: 5ex;">名次</th>
    <th>用户</th>
<?php
    $cnt_prob=0;
    foreach($pbs as $k=>$v) {
        $v=(int)$v;
        $mbarray[$v]['score']=0;
        $mbarray[$v]['result']="N";
        $d = $problems[$v] ?? [];
        $cnt_prob++;
?>
    <th><a href="problem.php?pid=<?=$v?>&ctid=<?=h($_GET['ctid'] ?? '')?>" target="_blank"><?=shortname($d['probname'])?></a>
    <?php  if($end || Privilege::allows('查看比赛')) { ?><a href="../problem/problem.php?pid=<?=$v?>" target="_blank" title="跳转到题目 <?=h($d['probname'])?>"><i class='icon-share'></i></a><?php  } ?>
    </th>
    <th onclick="sortTable('contest_report', <?=$cnt_prob*2+1?>, 'int')" style="min-width: 5ex; cursor: pointer; color:navy;">得分</th>
<?php
    }
?>
    <th onclick="sortTable('contest_report', <?=$cnt_prob*2+2?>, 'int')" style="min-width: 6ex; cursor: pointer; color:navy;">总分</th>
  </tr>
</thead>
<?php
    $rowcnt = 0;
    foreach ($entrants as $entrant) {
        $d = $entrant['user'];
        $rowcnt++;
?>
  <tr>
    <td id="rank<?=$rowcnt?>"></td>
    <td id="nickname<?=$rowcnt ?>"><a href="../user/detail.php?uid=<?=$d['uid'] ?>" target="_blank">
<?=gravatar::showImage($d['email'], 14);?>
<?=h(Privilege::allows('查看用户') ? $d['realname'] : $d['nickname'])?>
</a></td>
<?php
        $sum = $entrant['total'];
        $rank = $entrant['marks'] + $mbarray;
        foreach($pbs as $k=>$v) {
?>
    <td id="result<?=$v?>_<?=$rowcnt?>" class='wrap'>
    <?php
    if($rank[$v]['result'] =="N")
        评测信息($rank[$v]['result']);
    else {
        if((Privilege::allows('查看比赛') || $end))
            echo "<a href='code.php?csid={$rank[$v]['csid']}'>";
        if($rank[$v]['result'] == "") echo "未评测";
        else if((Privilege::allows('查看比赛') || $end)) 评测结果($rank[$v]['result'], 30);
        else echo "不显示";
        if((Privilege::allows('查看比赛') || $end))
            echo "</a>";
    } ?></td>
    <td id="score<?=$v ?>_<?=$rowcnt ?>" ><?php if($end || (Privilege::allows('查看比赛'))) echo $rank[$v]['score']; ?></td>
<?php
        }
?>
    <td id="sum<?=$rowcnt ?>" ><?php if($end || (Privilege::allows('查看比赛'))) echo $sum; ?></td>
  </tr>
<?php
    }
?>
</table>
</div>
<script language="javascript">
var sortsx=true;
var rowcnt=<?=$rowcnt ?>;

function qsort(key) {
    sortsx=!sortsx;
    vmin=10000;
    vmax=-1;
    if (sortsx) {
        for (i=1;i<=rowcnt-1;i++) {
            vi=new Number(document.getElementById(key+i).innerHTML);
            pmin=vmin;
            for (j=i;j<=rowcnt;j++) {
                vj=new Number(document.getElementById(key+j).innerHTML);
                if (vj<pmin) {
                    pmin=vj;
                    k=j;
                }
            }
            if (pmin<vi)
                swap(i,k);
        }
    } else {
        for (i=1;i<=rowcnt-1;i++) {
            vi=new Number(document.getElementById(key+i).innerHTML);
            pmax=vmax;
            for (j=i;j<=rowcnt;j++) {
                vj=new Number(document.getElementById(key+j).innerHTML);
                if (vj>pmax) {
                    pmax=vj;
                    k=j;
                }
            }
            if (pmax>vi)
                swap(i,k);
        }
    }
}

function swap(a,b) {
    t=document.getElementById("nickname"+a).innerHTML;
    document.getElementById("nickname"+a).innerHTML=document.getElementById("nickname"+b).innerHTML;
    document.getElementById("nickname"+b).innerHTML=t;
    t=document.getElementById("sum"+a).innerHTML;
    document.getElementById("sum"+a).innerHTML=document.getElementById("sum"+b).innerHTML;
    document.getElementById("sum"+b).innerHTML=t;

    t=document.getElementById("rank"+a).innerHTML;
    document.getElementById("rank"+a).innerHTML=document.getElementById("rank"+b).innerHTML;
    document.getElementById("rank"+b).innerHTML=t;
<?php
foreach($pbs as $k=>$v) {
?>
    t=document.getElementById("result<?=$v ?>_"+a).innerHTML;
    document.getElementById("result<?=$v ?>_"+a).innerHTML=document.getElementById("result<?=$v ?>_"+b).innerHTML;
    document.getElementById("result<?=$v ?>_"+b).innerHTML=t;

    t=document.getElementById("score<?=$v ?>_"+a).innerHTML;
    document.getElementById("score<?=$v ?>_"+a).innerHTML=document.getElementById("score<?=$v ?>_"+b).innerHTML;
    document.getElementById("score<?=$v ?>_"+b).innerHTML=t;
<?php
}
?>

}

qsort("sum");
var last="";

for (i=1;i<=rowcnt;i++) {
    if (last==document.getElementById("sum"+i).innerHTML) {
        document.getElementById("rank"+i).innerHTML=document.getElementById("rank"+(i-1)).innerHTML;
    } else {
        document.getElementById("rank"+i).innerHTML=i;
        last=document.getElementById("sum"+i).innerHTML;
    }
}

</script>

<?php
    include_once("../include/footer.php");
?>
