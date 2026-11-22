<?php
// 中山大学 SOJ 的题面格式。
return [
    'name'=>'中山大学 SOJ', 'url'=>'http://www.soj.me/show_problem.php?pid={id}',
    'title'=>"//div[@class='cent']",
    'sections'=>[
        "(//div[@class='description'])[1]",
        "(//div[@class='description'])[2]",
        "(//div[@class='description'])[3]",
        "(//pre)[1]",
        "(//pre)[2]",
    ],
];
