<?php
// 中山大学 SOJ 的题面格式。
// 调用示例：ProblemSource::draft('soj', 1001)。各标题直接指向本站对应的 HTML 部分。
return [
    'name'=>'中山大学 SOJ', 'url'=>'http://www.soj.me/show_problem.php?pid={id}',
    // SOJ 以 description 的出现顺序区分题面与格式说明。
    'title'=>"//div[@class='cent']",
    'sections'=>[
        '题目描述' => "(//div[@class='description'])[1]",
        '输入格式' => "(//div[@class='description'])[2]",
        '输出格式' => "(//div[@class='description'])[3]",
        // 两个预排版区块依次是样例输入和输出。
        '样例输入' => "(//pre)[1]",
        '样例输出' => "(//pre)[2]",
    ],
];
