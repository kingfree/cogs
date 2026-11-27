<?php
/**
 * 网页展示判题语言、状态与评测方式时读取 $STR。语言名称的下标沿用评测协议里的编号。
 * 例：$label = $STR['lang'][1];  // C。
 */
global $STR;

$STR['lang'] = array_column(Language::TYPES, 1); // 详情页用完整语言名，和运行时采用同一编号。
$STR['lng'] = array_column(Language::TYPES, 3);  // 个人得分榜的单字符简写保存在同一条语言记录。

// 旧库用 -1 表示交互题，3、4 表示上传答案包；这些编号必须保留。
$STR['plugin'] = [
    -1 => "<span class='label label-warning'>交互式</span>",
     0 => "<span class='label label-success'>评测插件</span>",
     1 => "<span class='label label-default'>简单对比</span>",
     2 => "<span class='label label-info'>逐字节对比</span>",
     3 => "<span class='label label-success'>提交答案 + 评测插件</span>",
     4 => "<span class='label label-default'>提交答案 + 简单对比</span>",
];
