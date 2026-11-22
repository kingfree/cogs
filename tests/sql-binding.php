<?php
// 只在当前连接的临时表内检查参数绑定。
if (PHP_SAPI !== 'cli') exit;
require dirname(__DIR__).'/include/db.php';
$cfg = ['data_server'=>getenv('COGS_TEST_DB_HOST'), 'data_database'=>getenv('COGS_TEST_DB_NAME'),
    'data_uid'=>getenv('COGS_TEST_DB_USER'), 'data_pwd'=>getenv('COGS_TEST_DB_PASSWORD')];
Database::execute('CREATE TEMPORARY TABLE binding_test (id INT PRIMARY KEY, name VARCHAR(255))');
$name = "林 O'Reilly \\ 100%";
Database::execute('INSERT INTO binding_test VALUES (?, ?), (?, ?)', [1, $name, 2, '另一条记录']);
$rows = Database::rows('SELECT id, name FROM binding_test WHERE id > ? AND name = ? ORDER BY id LIMIT ?', [0, $name, 1]);
if (count($rows) !== 1 || $rows[0]['name'] !== $name) throw new RuntimeException('绑定的值或顺序错误');
if (Database::rows('SELECT id FROM binding_test WHERE name=?', ['不存在'])) throw new RuntimeException('空结果错误');
echo "数据库绑定检查通过\n";
