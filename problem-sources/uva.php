<?php
// UVa 的题面格式。
// 调用示例：ProblemSource::draft('uva', 1001)。各标题直接指向本站对应的 HTML 部分。
return [
    'name'=>'UVa', 'url'=>'https://onlinejudge.org/external/{volume}/{id}.html',
    // UVa 的卷号由题号除以 100 得到，编号可小于 1000。
    'title'=>"//h1",
    'sections'=>[
        '题目描述' => "(//div[@class='content'])[1]",
        '输入格式' => "(//div[@class='content'])[2]",
        '输出格式' => "(//div[@class='content'])[3]",
        // 样例按页面中的先后顺序取两个 pre。
        '样例输入' => "(//pre)[1]",
        '样例输出' => "(//pre)[2]",
    ],
    'minimum'=>1,
];
