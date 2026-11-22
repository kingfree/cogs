<?php
// 防止入口重新引入 SQL、可变的查询游标或直接启动选手程序。
if (PHP_SAPI !== 'cli') exit;
$root = dirname(__DIR__);
$count = 0;
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)) as $file) {
    $path = substr($file->getPathname(), strlen($root)+1);
    if ($file->getExtension() !== 'php' || str_starts_with($path, '.git/') || str_starts_with($path, 'tests/')) continue;
    $code = file_get_contents($file);
    token_get_all($code, TOKEN_PARSE);
    $count++;
    if (str_starts_with($path, 'features/') || str_starts_with($path, 'grader/') || $path === 'include/db.php') continue;
    foreach (token_get_all($code) as $token) {
        if (!is_array($token)) continue;
        if ($token[0] === T_STRING && in_array(strtolower($token[1]), ['dosql','rtnrlt','sqlquery'])) {
            throw new RuntimeException($path.' 使用了旧查询游标。');
        }
        if (in_array($token[0], [T_CONSTANT_ENCAPSED_STRING,T_ENCAPSED_AND_WHITESPACE]) &&
            preg_match('/^\s*[\x22\x27(]*\s*(?:SELECT\b.+\bFROM\b|INSERT\s+INTO\b|UPDATE\s+\w+\s+SET\b|DELETE\s+FROM\b)/is', $token[1])) {
            throw new RuntimeException($path.' 包含应由业务主体执行的 SQL。');
        }
    }
}
echo "$count 个应用 PHP 文件通过语法和业务入口检查\n";
