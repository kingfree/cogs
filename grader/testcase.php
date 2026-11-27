<?php
// 每个测试点都有独立工作目录；一次重测不会读取上次遗留的输出。
// grade 从 1 开始。返回的 score 为 0 到 1；缺输入、缺标准答案和选手没写输出分别报告。
function gradeCase(array $request, string $workspace, string $dataRoot): array
{
    $name = $request['pname'];
    $point = $request['grade'];
    $data = $dataRoot.'/'.$name;
    $input = "$data/$name$point.in";
    $answer = "$data/$name$point.ans";
    $result = ['score'=>0, 'rtime'=>0, 'memory'=>0, 'exitcode'=>0, 'timeout'=>0, 'runerr'=>0,
        'noreport'=>0, 'memoryout'=>0, 'diff'=>'', 'input'=>'',
        'noindata'=>(int)(!is_file($input) || is_link($input)), 'noansdata'=>(int)(!is_file($answer) || is_link($answer))];
    if (is_link($data) || $result['noindata'] || $result['noansdata']) return $result;
    $dir = $workspace.'/case-'.bin2hex(random_bytes(6));
    mkdir($dir, 0700);
    try {
        $result['input'] = file_get_contents($input, false, null, 0, 65536);
        $output = "$dir/$name.out";
        if ((int)$request['language'] === 3) {
            $output = "$workspace/build/$name$point.out";
        } else {
            $binary = "$workspace/build/program";
            if (is_link($binary) || !is_file($binary)) throw new DomainException('源代码尚未编译。');
            copy($binary, "$dir/program");
            chmod("$dir/program", 0700);
            if ((int)$request['plugin'] === -1) {
                $checker = $workspace.'/interactor-'.bin2hex(random_bytes(6));
                mkdir($checker, 0700);
                try {
                    foreach (['cpp'=>2, 'cc'=>2, 'c'=>1, 'pas'=>0, 'pp'=>0] as $ext=>$lang) {
                        $src = 'interactor.'.$ext;
                        if (!is_file("$data/$src") || is_link("$data/$src")) continue;
                        copy("$data/$src", "$checker/$src");
                        $build = Sandbox::run(Language::compiler($lang, $src, 'interactor'), $checker, 20000, 512);
                        if ($build['status'] !== 0) throw new DomainException('交互判题器编译失败。');
                        copy($input, "$checker/input");
                        copy($answer, "$checker/answer");
                        return array_replace($result, Sandbox::interact($dir, $checker, (int)$request['timelimit'], (int)$request['memorylimit']));
                    }
                    throw new DomainException('未配置该题的交互判题器。');
                } finally { Worker::remove($checker); }
            }
            copy($input, "$dir/$name.in");
            $run = Sandbox::run(['./program'], $dir, (int)$request['timelimit'], (int)$request['memorylimit']);
            $result['rtime'] = $run['rtime'];
            $result['memory'] = $run['memory'];
            $result['exitcode'] = $run['status'];
            $result['timeout'] = (int)$run['timeout'];
            $result['runerr'] = (int)($run['status'] !== 0);
            if ($result['runerr']) return $result;
        }
        if (!is_file($output) || is_link($output)) { $result['noreport'] = 1; return $result; }
        $checker = $workspace.'/check-'.bin2hex(random_bytes(6));
        try {
            $result['score'] = checkAnswer($data, $input, $answer, $output, $checker, (int)$request['plugin']);
        } finally { Worker::remove($checker); }
        if ($result['score'] < 1) {
            $result['diff'] = "期望输出\n".file_get_contents($answer, false, null, 0, 2048).
                "\n实际输出\n".file_get_contents($output, false, null, 0, 2048);
        }
        return $result;
    } finally { Worker::remove($dir); }
}
