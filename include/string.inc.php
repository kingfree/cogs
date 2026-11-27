<?php
/**
 * 网页展示判题语言、状态与评测方式时读取 $STR。语言名称的下标沿用评测协议里的编号。
 * 例：$label = $STR['lang'][1];  // C。
 */
global $STR;

$STR['lang'] = array_column(Language::TYPES, 1);

$STR['lng'][0]="P";
$STR['lng'][1]="C";
$STR['lng'][2]="+";
$STR['lng'][3]="A";

$STR['plugin'][-1]="<span class='label label-warning'>交互式</span>";
$STR['plugin'][0]="<span class='label label-success'>评测插件</span>";
$STR['plugin'][1]="<span class='label label-default'>简单对比</span>";
$STR['plugin'][2]="<span class='label label-info'>逐字节对比</span>";
$STR['plugin'][3]="<span class='label label-success'>提交答案 + 评测插件</span>";
$STR['plugin'][4]="<span class='label label-default'>提交答案 + 简单对比</span>";
