<?php
// 通过环境变量连接测试库；所有写入仅存在于本连接的临时表。
if (PHP_SAPI !== 'cli') exit;
require_once dirname(__DIR__).'/include/db.php';
$cfg = [
    'data_server' => getenv('COGS_TEST_DB_HOST') ?: 'localhost',
    'data_database' => getenv('COGS_TEST_DB_NAME') ?: 'cogs',
    'data_uid' => getenv('COGS_TEST_DB_USER') ?: 'cogs',
    'data_pwd' => getenv('COGS_TEST_DB_PASSWORD') ?: '',
];
$db = new DataAccess();
$db->dosql('CREATE TEMPORARY TABLE binding_test (id INT PRIMARY KEY, name VARCHAR(255))');
$name = "林 O'Reilly \\ 100%";
$db->dosql('INSERT INTO binding_test VALUES (?, ?), (?, ?)', [1, $name, 2, '另一条记录']);
$query = new SqlQuery('SELECT id, name FROM binding_test WHERE id > ?', [0]);
$query->append(' AND name = ?', [$name]);
$query->append(' ORDER BY id LIMIT ?', [1]);
if ($db->dosql($query) !== 1 || $db->rtnrlt()['name'] !== $name) throw new RuntimeException('绑定值或顺序错误');
if ($db->dosql('SELECT id FROM binding_test WHERE name = ?', ['不存在']) !== 0) throw new RuntimeException('检索结果错误');
echo "数据库绑定检查通过\n";
