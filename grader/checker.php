<?php
// 特判器单独运行。选手程序从未挂载判题器、标准答案或其他测试点。
// testcase.php 提供单点的输入、标准答案和实际输出。返回 0～1 的得分，由网页端换算为整题分数。
function checkAnswer(string $data, string $input, string $answer, string $output, string $dir, int $type): float
{
    if ($type === 2) return (float)(hash_file('sha256', $answer) === hash_file('sha256', $output));
    if ($type === 1 || $type === 4) {
        $a = fopen($answer, 'rb');
        $b = fopen($output, 'rb');
        try {
            do {
                do { $x = fgetc($a); } while (in_array($x, [' ', "\t", "\r", "\n"], true));
                do { $y = fgetc($b); } while (in_array($y, [' ', "\t", "\r", "\n"], true));
                if ($x !== $y) return 0.0;
            } while ($x !== false);
            return 1.0;
        } finally { fclose($a); fclose($b); }
    }
    mkdir($dir, 0700);
    foreach (['input'=>$input, 'answer'=>$answer, 'output'=>$output] as $file=>$path) copy($path, "$dir/$file");
    foreach (['spj','cena'] as $kind) {
        foreach (['cc'=>2,'cpp'=>2,'c'=>1,'pas'=>0,'pp'=>0] as $ext=>$lang) {
            $source = "$kind.$ext";
            if (!is_file("$data/$source") || is_link("$data/$source")) continue;
            copy("$data/$source", "$dir/$source");
            $build = Sandbox::run(Language::compiler($lang, $source, 'checker'), $dir, 20000, 512);
            if ($build['status'] !== 0) throw new DomainException('特殊判题器编译失败。');
            // CENA 判题器按原题名打开输入和选手输出。
            $name = basename($data);
            copy($input, "$dir/$name.in");
            copy($output, "$dir/$name.out");
            $args = $kind === 'spj' ? ['./checker','input','output','answer'] : ['./checker','100','answer'];
            $result = Sandbox::run($args, $dir, 5000, 256);
            // SPJ 通过退出码给百分分；CENA 成功退出后从 score.log 读同一单位。
            if ($kind === 'spj') $score = $result['status'];
            else $score = $result['status'] === 0 && is_file("$dir/score.log") && !is_link("$dir/score.log")
                ? (int)file_get_contents("$dir/score.log", false, null, 0, 16) : -1;
            return $score >= 0 && $score <= 100 ? $score / 100 : 0.0;
        }
    }
    if (is_file("$data/plugin.php") && !is_link("$data/plugin.php")) {
        copy("$data/plugin.php", "$dir/plugin.php");
        $entry = '<?php require "plugin.php"; $score = plugin_compare(fopen("input","rb"), fopen("output","rb"), fopen("answer","rb")); echo $score;';
        file_put_contents("$dir/check.php", $entry);
        $result = Sandbox::run(['php','-n','check.php'], $dir, 5000, 256);
        $score = trim($result['msg']);
        return $result['status'] === 0 && is_numeric($score) ? max(0.0, min(1.0, (float)$score)) : 0.0;
    }
    throw new DomainException('找不到特殊判题器。');
}
