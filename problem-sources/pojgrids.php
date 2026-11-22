<?php
// 百练 的题面格式。
return [
    'name'=>'百练', 'url'=>'http://poj.grids.cn/practice/{id}',
    'title'=>"//*[@id='pageTitle']",
    'sections'=>[
        "(//dl[@class='problem-content']/dd)[1]",
        "(//dl[@class='problem-content']/dd)[2]",
        "(//dl[@class='problem-content']/dd)[3]",
        "(//dl[@class='problem-content']//pre)[1]",
        "(//dl[@class='problem-content']//pre)[2]",
    ],
    'prefix'=>'poj',
];
