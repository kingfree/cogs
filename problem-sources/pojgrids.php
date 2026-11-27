<?php
// 百练 的题面格式。
// 调用示例：ProblemSource::draft('pojgrids', 1001)。各标题直接指向本站对应的 HTML 部分。
return [
    'name'=>'百练', 'url'=>'http://poj.grids.cn/practice/{id}',
    // 百练在 problem-content 的定义列表中存放题意与格式。
    'title'=>"//*[@id='pageTitle']",
    'sections'=>[
        '题目描述' => "(//dl[@class='problem-content']/dd)[1]",
        '输入格式' => "(//dl[@class='problem-content']/dd)[2]",
        '输出格式' => "(//dl[@class='problem-content']/dd)[3]",
        // 样例取列表中的两个 pre，保留换行和空白。
        '样例输入' => "(//dl[@class='problem-content']//pre)[1]",
        '样例输出' => "(//dl[@class='problem-content']//pre)[2]",
    ],
    'prefix'=>'poj',
];
