<?php
// 在仓库根目录运行 php tests/architecture.php 检查结构；加 --steps 列出值得人工复读的连续业务步骤。
if (PHP_SAPI !== 'cli') exit;
$root = dirname(__DIR__);
$count = 0;
$reviewSteps = in_array('--steps', $argv, true);
$findings = [];
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)) as $file) {
    $path = substr($file->getPathname(), strlen($root)+1);
    if ($file->getExtension() !== 'php' || str_starts_with($path, '.git/') || str_starts_with($path, 'tests/')) continue;
    $code = file_get_contents($file);
    $tokens = PhpToken::tokenize($code, TOKEN_PARSE);
    if ($reviewSteps) $findings[$path] = scanSteps($tokens);
    $count++;
    if (str_starts_with($path, 'features/') || str_starts_with($path, 'grader/') || $path === 'utils/database.php') continue;
    foreach ($tokens as $token) {
        if ($token->id === T_STRING && in_array(strtolower($token->text), ['dosql','rtnrlt','sqlquery'])) {
            throw new RuntimeException($path.' 使用了旧查询游标。');
        }
        if (in_array($token->id, [T_CONSTANT_ENCAPSED_STRING,T_ENCAPSED_AND_WHITESPACE]) &&
            preg_match('/^\s*[\x22\x27(]*\s*(?:SELECT\b.+\bFROM\b|INSERT\s+INTO\b|UPDATE\s+\w+\s+SET\b|DELETE\s+FROM\b)/is', $token->text)) {
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

if ($reviewSteps) {
    // 路径、方法和首末行让复核人直接回到上下文；已有注释是否准确仍需对照业务。
    ksort($findings);
    echo "步骤复读位置（连续三条执行语句没有就近步骤说明，需人工判断意图与可读性）\n";
    foreach ($findings as $path => $fileFindings) {
        foreach ($fileFindings as [$scope, $first, $last]) {
            echo "$path:$first  $scope · 请复读第 $first 至 $last 行\n";
        }
    }
}

/** 每段按方法或网页中的 PHP 代码块独立复读。行号只是导航，语义由维护者核对。 */
function scanSteps(array $tokens): array
{
    $findings = [];
    foreach (stepBodies(codeTokens($tokens)) as [$name, $body]) {
        $run = [];
        $explained = false;
        foreach (stepEvents($body) as [$kind, $first, $last]) {
            if ($kind === 'step') {
                if (!$explained) $run[] = [$first, $last];
                $explained = false;
                continue;
            }
            // 同行尾注解释刚结算的最后一条；独立注释或参数间注释解释随后结算的步骤。
            $trailing = $kind === 'comment' && $run && end($run)[1] === $first;
            if ($trailing) array_pop($run);
            if (count($run) >= 3) $findings[] = [$name, $run[0][0], end($run)[1]];
            $run = [];
            $explained = !$trailing;
        }
    }
    return $findings;
}

/** 合并插值字串和 heredoc；内嵌 SQL 或模板表达式里的花括号、分号不会被解释成业务结构。 */
function codeTokens(array $tokens): array
{
    $code = [];
    $closingQuote = null;
    $shortEcho = false;
    foreach ($tokens as $token) {
        if ($closingQuote !== null) {
            if ($token->id === $closingQuote) $closingQuote = null;
            continue;
        }
        if ($token->is(['"', '`', T_START_HEREDOC])) {
            $closingQuote = $token->id === T_START_HEREDOC ? T_END_HEREDOC : $token->id;
        }
        // 模板短输出保留 PHP 结束标记，让两侧的业务步骤各自在自己的代码块中复读。
        if ($token->id === T_OPEN_TAG_WITH_ECHO) $shortEcho = true;
        if ($token->id === T_CLOSE_TAG) $shortEcho = false;
        if ($shortEcho || $token->is([T_WHITESPACE, T_DOC_COMMENT, T_OPEN_TAG, T_INLINE_HTML])) continue;
        $code[] = $token;
    }
    return $code;
}

/** 单独交出每段可执行代码。跳过类属性与方法声明，闭包体也有自己的复读范围。 */
function stepBodies(array $code): Generator
{
    $scope = '入口';
    $parents = [];
    $depth = 0;
    $body = [];
    $declaration = null;
    foreach ($code as $i => $token) {
        if ($token->is([T_CLASS, T_INTERFACE, T_ENUM, T_TRAIT]) && ($code[$i-1]->id ?? null) !== T_DOUBLE_COLON) {
            $declaration = false;
        } elseif ($token->id === T_FUNCTION) {
            $name = $code[$i+1] ?? null;
            $declaration = $name?->id === T_STRING ? $name->text.'()' : '闭包';
        }
        // 进入类体或方法体之前，输出外层已完成的语句；抽象方法在分号处直接结束。
        if ($declaration !== null) {
            if ($body) yield [$scope, $body];
            $body = [];
            if ($token->text === '{') {
                $parents[++$depth] = $scope;
                $scope = $declaration;
                $declaration = null;
            } elseif ($token->text === ';') {
                $declaration = null;
            }
            continue;
        }
        if ($token->text === '{') $depth++;
        if ($token->text === '}' && array_key_exists($depth, $parents)) {
            if ($body) yield [$scope, $body];
            $body = [];
            $scope = $parents[$depth];
            unset($parents[$depth--]);
            continue;
        }
        if ($token->text === '}') $depth--;
        if ($scope !== false) $body[] = $token;
        if ($token->id === T_CLOSE_TAG) {
            if ($body) yield [$scope, $body];
            $body = [];
        }
    }
    if ($body) yield [$scope, $body];
}

/** 产出完整的控制判断、调用或赋值，以及实际位于代码体内的注释。 */
function stepEvents(array $body): Generator
{
    $controls = [T_IF, T_ELSEIF, T_FOR, T_FOREACH, T_WHILE, T_SWITCH, T_CATCH];
    $omit = [T_ECHO, T_REQUIRE, T_REQUIRE_ONCE, T_INCLUDE, T_INCLUDE_ONCE, T_DECLARE, T_USE, T_NAMESPACE, T_GLOBAL, T_CONST,
        T_ELSE, T_TRY, T_FINALLY, T_DO, T_ENDIF, T_ENDFOR, T_ENDFOREACH, T_ENDWHILE, T_ENDSWITCH];
    $first = null;
    $parentheses = $expressionBraces = 0;
    $guardLine = null;
    foreach ($body as $token) {
        if ($token->id === T_COMMENT) {
            if (preg_match('/\p{L}/u', $token->text)) yield ['comment', $token->line, $token->line];
            continue;
        }
        if ($token->is(['(', '['])) $parentheses++;
        if ($token->is([')', ']'])) $parentheses = max(0, $parentheses - 1);
        // 分支的开括号结算一次判断。match 等表达式的花括号则属于外层那一条语句。
        if ($token->text === '{') {
            if ($first && $first->is($controls)) {
                yield ['step', $first->line, $token->line];
                $guardLine = $token->line;
            } elseif ($first && !$first->is($omit)) {
                $expressionBraces++;
                continue;
            }
            $first = null;
            continue;
        }
        if ($token->text === '}') {
            if ($expressionBraces > 0) $expressionBraces--;
            else $first = null;
            $guardLine = null;
            continue;
        }
        // for 的两个分号在括号内；跨行 SQL、数组字面量和整次调用只在最终分号处计入。
        if ($token->id === T_CLOSE_TAG || ($token->text === ';' && !$parentheses && !$expressionBraces)) {
            // 同行的「不满足条件就设状态并退出」是一次拒绝，不因两个分号被报作三个业务步骤。
            $immediateExit = $first && $first->line === $guardLine &&
                ($first->is([T_EXIT, T_BREAK, T_CONTINUE, T_THROW]) || strtolower($first->text) === 'http_response_code');
            if ($first && !$immediateExit && !$first->is($omit)) yield ['step', $first->line, $token->line];
            $first = null;
            continue;
        }
        if (!$token->is([')', ']', ',', ':', ';'])) $first ??= $token;
    }
    yield ['end', 0, 0];
}
