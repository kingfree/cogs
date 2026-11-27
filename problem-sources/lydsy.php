<?php
// BZOJ 的题面格式。
// 调用示例：ProblemSource::draft('lydsy', 1001)。各标题直接指向本站对应的 HTML 部分。
return [
    'name'=>'BZOJ', 'url'=>'http://www.lydsy.com/JudgeOnline/problem.php?id={id}',
    // BZOJ 把正文和格式说明放在分别编号的 content 区块。
    'title'=>"//h2",
    'sections'=>[
        '题目描述' => "(//div[@class='content'])[1]",
        '输入格式' => "(//div[@class='content'])[2]",
        '输出格式' => "(//div[@class='content'])[3]",
        // 样例单独使用 sampledata，提示与来源仍按正文区块编号。
        '样例输入' => "(//span[@class='sampledata'])[1]",
        '样例输出' => "(//span[@class='sampledata'])[2]",
        '提示' => "(//div[@class='content'])[6]",
        '来源' => "(//div[@class='content'])[7]",
    ],
    'prefix'=>'bzoj',
];
