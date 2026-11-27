<?php
// 接收 editcomptime.php 的排期；日期、题单与分组校验在 ContestRound::save。
// 例：管理员从 contest/editcomptime.php?action=add 选择题单、分组和比赛起止时间后保存。
require_once __DIR__.'/../include/header.php';
gethead(0, '修改比赛', ''); // 先验证排期管理权限，避免无权限用户修改开始时间。
requirePost(); // 保存排期需要管理员表单令牌。
$action = $_POST['action'] ?? $_GET['action'] ?? ''; // 兼容旧排期表单把操作放在查询字符串的写法。
if (!in_array($action, ['add','edit'], true)) throw new DomainException('未知的场次操作。', 400);
$id = $action === 'edit' ? (int)($_POST['ctid'] ?? $_GET['ctid'] ?? 0) : null; // 新场次由数据库分配编号，编辑必须指定原场次。
ContestRound::save($id, $_POST); // 校验比赛时间、题单和分组后才保存。
i提示($id === null ? '添加比赛场次成功！' : '修改比赛场次成功！', 取路径('contest/compbase.php'));
