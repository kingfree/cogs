<?php
// 在仓库根目录执行 php tests/architecture.php，检查入口中的 SQL、遗留的查询游标和业务接口说明。
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

// 业务类只在这里加载定义。公开方法的说明同时供编辑器和第一次接手的维护者查阅。
$subjects = glob($root.'/features/*.php');
$methods = 0;
foreach ($subjects as $subject) {
    require_once $subject;
    $class = new ReflectionClass(basename($subject, '.php'));
    foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        if (!$method->getDocComment()) {
            throw new RuntimeException($class->getName().'::'.$method->getName().' 缺少接口说明。');
        }
        $methods++;
    }
}
echo count($subjects)." 个业务主体、$methods 个公开方法具有可供编辑器读取的接口说明\n";
