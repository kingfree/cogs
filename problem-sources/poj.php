<?php
// 北京大学 POJ 的题面格式。
// 调用示例：ProblemSource::draft('poj', 1001)。各标题直接指向本站对应的 HTML 部分。
return [
    'name'=>'北京大学 POJ', 'url'=>'http://www.poj.org/problem?id={id}&lang=zh-CN&change=true',
    // POJ 的 ptx 依次存放题意与输入输出格式。
    'title'=>"//div[@class='ptt']",
    'sections'=>[
        '题目描述' => "(//div[@class='ptx'])[1]",
        '输入格式' => "(//div[@class='ptx'])[2]",
        '输出格式' => "(//div[@class='ptx'])[3]",
        // 输入与输出样例是独立的 sio 预排版元素。
        '样例输入' => "(//pre[@class='sio'])[1]",
        '样例输出' => "(//pre[@class='sio'])[2]",
        '提示' => "(//div[@class='ptx'])[4]",
        '来源' => "(//div[@class='ptx'])[5]",
    ],
];
