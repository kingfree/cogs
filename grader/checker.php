<?php
// 特判器单独运行。选手程序从未挂载判题器、标准答案或其他测试点。
// testcase.php 提供单点的输入、标准答案和实际输出。返回 0～1 的得分，由网页端换算为整题分数。
// 例：checkAnswer('/srv/data/sum', $input, $answer, $output, $work.'/checker', 1) 按文本模式比较这一点。
function checkAnswer(string $data, string $input, string $answer, string $output, string $dir, int $type): float
{
    // 二进制模式逐字节等价才通过，用文件哈希比较；文本模式只跳过约定的四种空白。
    if (in_array($type, [1, 2, 4], true)) {
        $ignoreWhitespace = $type !== 2;
        return (float)(answerDigest($answer, $ignoreWhitespace) === answerDigest($output, $ignoreWhitespace));
    }
    // 自定义判题需要输入、答案和选手输出的各一份副本，在单独目录中运行。
    if (!mkdir($dir, 0700)) throw new RuntimeException('无法创建特判工作目录。');
    foreach (['input'=>$input, 'answer'=>$answer, 'output'=>$output] as $file=>$path) stageGradingFile($path, "$dir/$file");
    // 同时安装多种旧判题器时，先选 SPJ，再选 CENA，最后才考虑 PHP 插件。
    foreach (['spj','cena'] as $kind) {
        if (!compileChecker($kind, $data, $dir)) continue;
        // 老题还按原题名读写文件；三个固定参数是 SPJ 约定，CENA 在 score.log 中给出百分分。
        $name = basename($data);
        stageGradingFile($input, "$dir/$name.in");
        stageGradingFile($output, "$dir/$name.out"); // 这里的 .out 是选手输出，标准答案另存为 answer。
        if ($kind === 'spj') {
            // SPJ 直接以进程退出码返回百分分。三个文件名按旧接口的顺序传入。
            $run = Sandbox::run(['./checker', 'input', 'output', 'answer'], $dir, 5000, 256);
            $score = $run['status'];
        } else {
            // CENA 以 100 为本点满分，按题名自行打开 .in 和 .out。
            $run = Sandbox::run(['./checker', '100', 'answer'], $dir, 5000, 256);
            // CENA 约定正常退出后写 score.log；失败退出或链接不能作为评分依据。
            $scoreFile = "$dir/score.log";
            if ($run['status'] !== 0 || !is_file($scoreFile) || is_link($scoreFile)) return 0.0;
            $reported = file_get_contents($scoreFile, false, null, 0, 16); // CENA 计分只需一个百分数，无需载入整份日志。
            if ($reported === false) throw new RuntimeException('无法读取 CENA 评分文件。');
            $score = (int)$reported;
        }
        // 原生判题器返回百分分，网页累加前须换算到 0～1，范围外按 0 分。
        return $score >= 0 && $score <= 100 ? $score / 100 : 0.0;
    }
    if (is_file("$data/plugin.php") && !is_link("$data/plugin.php")) {
        stageGradingFile("$data/plugin.php", "$dir/plugin.php");
        // 保留插件接收三个文件句柄的旧接口；生成的短入口只在判题沙箱中由 PHP CLI 执行。
        $entry = '<?php require "plugin.php"; $score = plugin_compare(fopen("input","rb"), fopen("output","rb"), fopen("answer","rb")); echo $score;';
        if (file_put_contents("$dir/check.php", $entry) !== strlen($entry)) {
            throw new RuntimeException('无法写入完整的 PHP 特判入口。');
        }
        $result = Sandbox::run(['php','-n','check.php'], $dir, 5000, 256); // 入口完整写出后启动，-n 避免载入主机 PHP 配置。
        // PHP 特判使用 0～1 的小数分。异常退出和非数字输出都不能算通过。
        $score = trim($result['msg']);
        return $result['status'] === 0 && is_numeric($score) ? max(0.0, min(1.0, (float)$score)) : 0.0;
    }
    throw new DomainException('找不到特殊判题器。');
}


/** 逐块读完整个文件才产生摘要。文本模式只删除旧比较方式约定的四个 ASCII 空白。 */
function answerDigest(string $path, bool $ignoreWhitespace): string
{
    $stream = fopen($path, 'rb');
    if ($stream === false) throw new RuntimeException('无法打开用于比较的答案文件。');
    try {
        // 用固定大小的缓冲区同时兼容二进制输出与大文本答案。
        $digest = hash_init('sha256');
        while (!feof($stream)) {
            $chunk = fread($stream, 65536);
            // 读取出错或者没有读到数据又尚未到 EOF 时，此前的部分内容不足以评分。
            if ($chunk === false || ($chunk === '' && !feof($stream))) {
                throw new RuntimeException('无法读完用于比较的答案文件。');
            }
            if ($ignoreWhitespace) $chunk = str_replace([' ', "\t", "\r", "\n"], '', $chunk);
            hash_update($digest, $chunk); // 按原次序累计本块，跨块的输出也能和答案整体比较。
        }
        return hash_final($digest);
    } finally {
        fclose($stream); // 打开成功的句柄无论是否读完都由本次比较关闭。
    }
}

/** 评测程序或数据必须已复制成功才启动沙箱；宿主目录无法读写时本次评分中止。 */
function stageGradingFile(string $source, string $destination): void
{
    if (!copy($source, $destination)) throw new RuntimeException('无法复制评测所需文件。');
}

/**
 * 在独立目录编译出题人的 SPJ、CENA 或交互器。缺源码返回 false，已有源码编译失败则报错。
 * 同时存在 cc 和 cpp 时按旧站顺序选择。编译器只看到复制过来的源码，不挂载题库目录。
 */
function compileChecker(string $kind, string $data, string $directory): bool
{
    $interactive = $kind === 'interactor';
    $extensions = $interactive ? ['cpp', 'cc', 'c', 'pas', 'pp'] : ['cc', 'cpp', 'c', 'pas', 'pp'];
    // 旧题可用 .cc 表示 C++、.pp 表示 Pascal；编译开关继续取同一份语言表。
    $aliases = ['cc'=>'cpp', 'pp'=>'pas'];
    foreach ($extensions as $extension) {
        $source = $kind.'.'.$extension; // 只在题目的固定判题器名称中寻找源码。
        if (!is_file("$data/$source") || is_link("$data/$source")) continue;
        // 只把选中的普通源文件交给编译器。源码存在但编译失败属于题目配置错误，不回退到其他判题器。
        stageGradingFile("$data/$source", "$directory/$source");
        $language = Language::id($aliases[$extension] ?? $extension);
        $command = Language::compiler($language, $source, $interactive ? 'interactor' : 'checker');
        $build = Sandbox::run($command, $directory, 20000, 512); // 判题器源码也在沙箱中编译。
        if ($build['status'] !== 0) {
            throw new DomainException($interactive ? '交互判题器编译失败。' : '特殊判题器编译失败。');
        }
        return true; // 第一个找到且编译成功的源码决定本次采用哪一种实现。
    }
    return false;
}
