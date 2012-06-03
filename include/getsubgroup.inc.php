<?php
$sql="select * from groups order by gname";
$cnt=$p->dosql($sql);
for ($i=0;$i<$cnt;$i++)
{
    $d=$p->rtnrlt($i);
    $groups[$i]=$d['gid'];
    $parent[$i]=$d['parent'];
}

$N=1;
$garray[0]=$gid;
$i=0;
$exit=false;
for ($w=0;$w<$cnt;$w++)
{
    $gid=$garray[$i];
    for ($j=0;$j<$cnt;$j++)
    {
        if ($parent[$j]==$gid)
        {
            $add=true;
            for ($k=0;$k<$N;$k++)
                if ($garray[$k]==$groups[$j])
                {
                    $add=false;
                    break;
                }
            if ($add)
            {
                $garray[$N]=$groups[$j];
                $N++;
            }
        }
    }
    $i++;
    if ($i==$N)
        $i=0;
}

$g=array();

for ($i=1;$i<$N;$i++)
$g[$i]=$garray[$i];

?>

