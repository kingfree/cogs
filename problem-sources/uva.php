<?php
// UVa 的题面格式。
return [
    'name'=>'UVa', 'url'=>'https://onlinejudge.org/external/{volume}/{id}.html',
    'title'=>"//h1",
    'sections'=>[
        "(//div[@class='content'])[1]",
        "(//div[@class='content'])[2]",
        "(//div[@class='content'])[3]",
        "(//pre)[1]",
        "(//pre)[2]",
    ],
    'minimum'=>1,
];
