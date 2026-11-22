<?php
// 北京大学 POJ 的题面格式。
return [
    'name'=>'北京大学 POJ', 'url'=>'http://www.poj.org/problem?id={id}&lang=zh-CN&change=true',
    'title'=>"//div[@class='ptt']",
    'sections'=>[
        "(//div[@class='ptx'])[1]",
        "(//div[@class='ptx'])[2]",
        "(//div[@class='ptx'])[3]",
        "(//pre[@class='sio'])[1]",
        "(//pre[@class='sio'])[2]",
        "(//div[@class='ptx'])[4]",
        "(//div[@class='ptx'])[5]",
    ],
];
