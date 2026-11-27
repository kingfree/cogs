<?php
// 每个测试点都有独立工作目录；一次重测不会读取上次遗留的输出。
// grade 从 1 开始。返回的 score 为 0 到 1；缺输入、缺标准答案和选手没写输出分别报告。
// 例：gradeCase($request, $workspace, '/srv/cogs/testdata') 读取任务指定的单个测试点，返回这一点的判定。
function gradeCase(array $request, string $workspace, string $dataRoot): array
{
    // 题库只读，编号从 1 开始。例如 sum 的第 2 点读取 sum2.in 和 sum2.ans。
    $name = $request['pname'];
    $point = $request['grade'];
    $data = $dataRoot.'/'.$name;
    // 只按该点编号取成对的输入与标准答案，避免串用其他点的数据。
    $input = "$data/$name$point.in";
    $answer = "$data/$name$point.ans";
    // 老协议用不同的标志区分缺输入、缺答案、没输出和运行错误；尚未评分时分数为 0。
    $result = ['score'=>0, 'rtime'=>0, 'memory'=>0, 'exitcode'=>0, 'timeout'=>0, 'runerr'=>0,
        'noreport'=>0, 'memoryout'=>0, 'diff'=>'', 'input'=>'',
        'noindata'=>(int)(!is_file($input) || is_link($input)), 'noansdata'=>(int)(!is_file($answer) || is_link($answer))];
    if (is_link($data) || $result['noindata'] || $result['noansdata']) return $result;
    // 同一提交可以重测，新建空目录可避免把前一轮的 .out 当作这次的答案。
    $dir = $workspace.'/case-'.bin2hex(random_bytes(6));
    if (!mkdir($dir, 0700)) throw new RuntimeException('无法创建测试点工作目录。');
    try {
        // 评测反馈只截取输入的前 64 KiB，选手程序读到的仍是完整测试点。
        $result['input'] = file_get_contents($input, false, null, 0, 65536);
        $output = "$dir/$name.out";
        if ((int)$request['language'] === 3) {
            // 答案包已在编译阶段拆好，直接比较对应编号的输出，不执行选手程序。
            $output = "$workspace/build/$name$point.out";
        } else {
            // 普通题只复制本提交的程序。先排除链接，再放入当前点的空目录并赋执行权限。
            $binary = "$workspace/build/program";
            if (is_link($binary) || !is_file($binary)) throw new DomainException('源代码尚未编译。');
            stageGradingFile($binary, "$dir/program");
            // 无法设置执行权限属于工作目录故障，不能记作选手程序运行错误。
            if (!chmod("$dir/program", 0700)) throw new RuntimeException('无法设置程序执行权限。');
            if ((int)$request['plugin'] === -1) {
                // 交互模式由交互器自己给分，不再检查文件输出或执行第二次比较。
                $interaction = runInteractiveCase($workspace, $dir, $data, $input, $answer, $request);
                return array_replace($result, $interaction);
            }
            // 选手按题名读写文件，这里只给它本点的输入，标准答案留给判题器。
            stageGradingFile($input, "$dir/$name.in");
            $run = Sandbox::run(['./program'], $dir, (int)$request['timelimit'], (int)$request['memorylimit']);
            // 保留实际耗时、内存和退出原因。运行出错的点不能用残留的部分输出继续评分。
            $result['rtime'] = $run['rtime'];
            $result['memory'] = $run['memory'];
            $result['exitcode'] = $run['status'];
            // 超时会产生非零退出码，保留两个标志，网页据此优先显示超时。
            $result['timeout'] = (int)$run['timeout'];
            $result['runerr'] = (int)($run['status'] !== 0);
            if ($result['runerr']) return $result;
        }
        // 正常退出仍可能没有生成结果文件；输出链接也不能作为结果读取。
        if (!is_file($output) || is_link($output)) { $result['noreport'] = 1; return $result; }
        // 自定义比较器有自己的目录；标准答案不会出现在已经运行过选手程序的目录。
        $checker = $workspace.'/check-'.bin2hex(random_bytes(6));
        try {
            $result['score'] = checkAnswer($data, $input, $answer, $output, $checker, (int)$request['plugin']);
        } finally {
            Worker::remove($checker); // 即使判题器缺失或抛错也清理已复制的数据。
        }
        // 未得满分时只附两端各 2 KiB 供网页显示，避免把整个答案装入反馈。
        if ($result['score'] < 1) {
            $result['diff'] = "期望输出\n".file_get_contents($answer, false, null, 0, 2048).
                "\n实际输出\n".file_get_contents($output, false, null, 0, 2048);
        }
        return $result;
    } finally {
        Worker::remove($dir); // 本点结束后，不让下个测试点复用选手留下的文件。
    }
}


/** 交互器独占 input、answer 和自己的二进制；选手进程只经标准输入输出和它交换消息。 */
function runInteractiveCase(string $workspace, string $solution, string $data, string $input, string $answer, array $request): array
{
    $checker = $workspace.'/interactor-'.bin2hex(random_bytes(6));
    if (!mkdir($checker, 0700)) throw new RuntimeException('无法创建交互评测目录。');
    try {
        // 先在交互器目录编译，再提供 input 和 answer。基础设施缺失和选手答错有不同的反馈。
        if (!compileChecker('interactor', $data, $checker)) throw new DomainException('未配置该题的交互判题器。');
        stageGradingFile($input, "$checker/input");
        stageGradingFile($answer, "$checker/answer");
        // 通过套接字开始对话；选手和交互器仍拥有各自的 /work。
        return Sandbox::interact($solution, $checker, (int)$request['timelimit'], (int)$request['memorylimit']);
    } finally {
        Worker::remove($checker);
    }
}
