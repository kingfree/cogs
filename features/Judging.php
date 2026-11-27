<?php
/**
 * 一次普通交题或比赛交卷的评测。每个测试点只记录一次；页面读成绩、数据库保存成绩都从这些点计算。
 * 例：$job = new Judging($problem, $owner, $lang, $root);  // $problem 为已授权题目的数据库记录。
 * acquire(0) 自动选机，prepareSource() 定位源码；compile() 成功后，逐点 run($point)，最后 unlock()。
 */
final class Judging
{
    private array $options;
    private string $lease;
    private ?string $address = null;
    private string $sourceDir;
    private string $sourceName;
    private ?string $temporaryPath = null;
    private ?bool $compiled = null;                                     // null 为尚未编译，false 为已确认的编译失败。
    private array $points = [];
    public array $machine = [];
    public string $compileMessage = '';
    public int $failedPoint = 0;
    public string $failedInput = '';
    public string $difference = '';

    /** 题目文件名、语言编号和资源限制来自本站记录；普通提交和比赛使用各自的源码根目录。 */
    public function __construct(array $problem, int $owner, int $language, string $sourceRoot, ?int $submissionId = null, bool $testOnly = false)
    {
        // 两种交题入口都从数据库的同一组题目字段确定测试规则。
        $this->options = [
            'pid'=>$problem['pid'], 'pname'=>$problem['filename'], 'datacnt'=>(int)$problem['datacnt'],   // 点号和文件名前缀均随题目确定。
            'timelimit'=>$problem['timelimit'], 'memorylimit'=>$problem['memorylimit'], 'plugin'=>$problem['plugin'],
            'uid'=>$owner, 'language'=>$language, 'compiledir'=>$sourceRoot,
            'rejudge'=>$submissionId !== null, 'sid'=>$submissionId, 'mode'=>$testOnly ? 'test' : 'normal',   // 重判带回原 SID；试跑独立于是否重判。
        ];
        if ($this->options['datacnt'] < 1 || $this->options['datacnt'] > GradingLimits::CASES) {
            throw new RuntimeException('题目测试点数量无效。');
        }
        $this->lease = bin2hex(random_bytes(16));   // 每份任务独占一个编号，编译和评分都凭它续用同一机位。
    }

    /** 指定机位编号，传 0 时按优先级选机；只有收到锁定回执才取得租约。 */
    public function acquire(int $judgeId): bool
    {
        $candidates = $judgeId ? Judge::enabledById($judgeId) : Judge::enabled();   // 手选也须从启用的机器里寻找。
        foreach ($candidates as $candidate) {
            $state = Judge::request($candidate['address'], ['action'=>'state']);   // 先查询，不占用已停机或正在工作的机器。
            if (($state['state'] ?? '') !== 'free') continue;

            // 空闲状态只是快照，同时看到它的两个请求仍要争用同一把锁。
            $lock = Judge::request($candidate['address'], ['action'=>'lock', 'job'=>$this->lease]);
            if (($lock['state'] ?? '') !== 'successful') continue;   // 其他请求先获得租约时继续下一台。
            $this->address = $candidate['address'];   // 编译、运行和解锁均使用这台机器。
            $this->machine = ['grid'=>$candidate['grid'], 'memo'=>$candidate['memo']] + $state;
            register_shutdown_function([$this, 'unlock']);   // 即使浏览器中断也尝试归还租约。
            return true;
        }
        return false;
    }

    /** 创建作者专用的源码目录；比赛按题目约定的固定文件名读交卷，普通上传另加历史版本号。 */
    public function prepareSource(): void
    {
        $owner = (int)$this->options['uid'];
        $this->sourceDir = rtrim($this->options['compiledir'], '/').'/'.$owner;   // 比赛根目录还包含场次编号，再按作者隔开。
        if ($owner <= 0 || !TestData::name($this->options['pname']) || !isset(Language::TYPES[$this->options['language']])) {
            throw new RuntimeException('评测题目、作者或语言无效。');         // 未核准的题名不用于生成本地文件路径。
        }
        if (is_link($this->sourceDir) || (!is_dir($this->sourceDir) && !mkdir($this->sourceDir, 0750, true) && !is_dir($this->sourceDir))) {
            throw new RuntimeException('无法打开选手的源码目录。');   // 拒绝链接和无法创建的目录，防止写到选手目录之外。
        }
        $this->sourceName = $this->options['pname'].'.'.langnumtostr($this->options['language']);   // 比赛交卷固定以题名和语言扩展名保存。
    }

    /** 保存表单里的 file，返回是否成功；正式交题保留历史，试跑只保留到评测结束。 */
    public function upload(array $file): bool
    {
        $size = $file['size'] ?? 0;
        if (($file['error'] ?? null) !== UPLOAD_ERR_OK || $size < 1 || $size > GradingLimits::SOURCE_BYTES) return false;
        $this->sourceName .= '.'.bin2hex(random_bytes(10));   // 练习的每份上传留一份历史，重交不覆盖旧文件。
        $path = $this->sourceDir.'/'.$this->sourceName;
        if (!move_uploaded_file($file['tmp_name'], $path)) return false;
        if ($this->options['mode'] === 'test') $this->temporaryPath = $path;   // 只清理本请求上传的确切路径；重判历史不进入清理名单。
        return true;
    }

    /** 重判复用原提交登记的文件名；读取时和查看源码使用相同的本地路径检查。 */
    public function useSource(string $file): void
    {
        $this->sourceName = $file;
    }

    /** 普通评测从作者目录读源码；比赛可传已核验的交卷快照。编译失败返回 false，连接或存储故障抛异常。 */
    public function compile(?string $source = null): bool
    {
        if ($this->compiled !== null) throw new LogicException('每份任务只编译一次。');   // 任务保留上一编译的测试结果，同一任务内不得替换程序。
        $options = $this->options;
        if ($source === null) {
            $file = Submission::sourcePath($options['compiledir'], (int)$options['uid'], $this->sourceName);  // 普通判题从已核准的作者目录读文件。
            $source = $file === null ? false : file_get_contents($file, false, null, 0, GradingLimits::SOURCE_BYTES + 1);   // 多读一字节即可区分刚好达到上限和超限。
        }
        if ($source === false || strlen($source) > GradingLimits::SOURCE_BYTES) throw new RuntimeException('源文件不存在、不可读或超过限制。');
        $reply = Judge::request($this->address, [
            'action'=>'compile', 'job'=>$this->lease, 'pname'=>$options['pname'], 'uid'=>$options['uid'],
            'language'=>$options['language'], 'src'=>$options['pname'].'.'.langnumtostr($options['language']), 'code'=>$source,   // 本地可能有历史后缀，发给编译器的文件名仍按题名约定。
        ]);
        if (($reply['state'] ?? '') === 'error' || !in_array($reply['compilesucc'] ?? null, ['0','1'], true)) {
            throw new RuntimeException('评测机未返回有效的编译结果。');
        }
        $this->compileMessage = $reply['msg'] ?? '';   // 保留编译器诊断，页面按纯文本展示。
        $this->compiled = $reply['compilesucc'] === '1';   // 比赛需要把真正的编译失败记作 C。
        return $this->compiled;
    }

    /** 运行从 1 开始的测试点并返回一个判定字符。再次读取同一点时复用结果，避免重复评分。 */
    public function run(int $point): string
    {
        if ($this->compiled !== true) throw new LogicException('编译成功后才可运行测试点。');
        if (isset($this->points[$point])) return $this->points[$point]['verdict'];   // 同一点已经评分时直接复用，不再累计。
        if ($point !== count($this->points)+1 || $point > $this->options['datacnt']) throw new LogicException('测试点必须按题目顺序运行。');
        $options = $this->options;
        $reply = Judge::request($this->address, [
            'action'=>'grade', 'job'=>$this->lease, 'grade'=>$point, 'uid'=>$options['uid'], 'pname'=>$options['pname'],   // 同一租约逐点取结果。
            'language'=>$options['language'], 'timelimit'=>$options['timelimit'], 'memorylimit'=>$options['memorylimit'], 'plugin'=>$options['plugin'],
        ]);
        if (($reply['state'] ?? '') === 'error' || !is_numeric($reply['score'] ?? null) || !is_numeric($reply['rtime'] ?? null)
            || !is_numeric($reply['memory'] ?? null) || !is_numeric($reply['exitcode'] ?? null) || $reply['score'] < 0 || $reply['score'] > 1) {
            throw new RuntimeException('评测机未返回有效的测试点结果。');   // 坏回执或服务故障不能变成选手零分。
        }
        if (!empty($reply['noindata']) || !empty($reply['noansdata'])) throw new RuntimeException("第 {$point} 个测试点缺少数据，成绩未保存。");

        $score = (float)$reply['score'];   // 保存评测机返回的 0 到 1 的比例，避免提前取整。
        $verdict = match (true) {
            !empty($reply['timeout']) => 'T',                              // 超时往往也有退出码，先显示超时。
            !empty($reply['memoryout']) => 'M',                            // 达到内存限制优先于普通运行错误。
            !empty($reply['runerr']) => 'E',                               // 程序异常退出。
            !empty($reply['noreport']) => 'R',                             // 程序结束但未产生约定的输出文件。
            $score === 0.0 => 'W', $score === 1.0 => 'A', default => 'P',   // 程序正常结束后才按答案分辨错误、通过和部分分。
        };
        $this->points[$point] = ['verdict'=>$verdict, 'score'=>$score, 'time'=>(float)$reply['rtime'],
            'memory'=>(int)$reply['memory'], 'exitCode'=>(int)$reply['exitcode']];
        if ($score === 0.0 && !$this->failedPoint) {
            // 网页只说明首个零分点，后续测试数据不留存在作业里。
            $this->failedPoint = $point;   // 后面的测试点只累计成绩，不覆盖首次失败的提示。
            $this->failedInput = $reply['input'] ?? '';
            $this->difference = $reply['diff'] ?? '';
        }
        return $verdict;
    }

    /** 取已运行的一点，score 为它对整题的百分制分数贡献，time 为毫秒，memory 为 KiB。 */
    public function pointResult(int $point): array
    {
        $result = $this->points[$point];
        $result['score'] = (int)($result['score'] / $this->options['datacnt'] * 100);  // 只换算这一行的显示值，后台仍保留原始比例。
        return $result;
    }

    /** 百分制总分按照原规则在最后取整，多个部分分不会逐点丢失小数。 */
    public function score(): int
    {
        return (int)(array_sum(array_column($this->points, 'score')) / $this->options['datacnt'] * 100);   // 先合计、最后取整，重复查看不会改变原始分数。
    }

    /** 每点平均内存，单位 KiB。 */
    public function averageMemory(): int
    {
        return (int)(array_sum(array_column($this->points, 'memory')) / $this->options['datacnt']);   // 始终从逐点 KiB 重新计算，不原地除以点数。
    }

    /** 所有运行点的总耗时，单位毫秒。 */
    public function totalTime(): float
    {
        return array_sum(array_column($this->points, 'time'));
    }

    /** 按测试点顺序拼成提交记录所用的判定串；编译失败单独记 C。 */
    public function verdicts(): string
    {
        return $this->compiled === false ? 'C' : implode('', array_column($this->points, 'verdict'));
    }

    /** 只有全部测试点均为 A 才算通过。 */
    public function accepted(): bool
    {
        return $this->verdicts() === str_repeat('A', $this->options['datacnt']);
    }

    /** 保存完整评测的普通提交，返回用于查看记录的 SID；重判保留原 SID。 */
    public function savePractice(): int
    {
        if ($this->options['mode'] === 'test' || count($this->points) !== $this->options['datacnt']) {
            throw new LogicException('只有完整的正式提交可以保存。');   // 试跑和中断都保留原始成绩。
        }
        return Submission::saveResult($this->options, $this->verdicts(), $this->score(), $this->averageMemory(),
            (int)$this->accepted(), (int)round($this->totalTime()), $this->sourceName);
    }

    /** 将成绩写回指定比赛交卷；校验管理员可操作的比赛由 contest/compile.php 在派发前完成。 */
    public function saveContest(int $entryId): void
    {
        if ($this->compiled !== false && count($this->points) !== $this->options['datacnt']) {
            throw new LogicException('比赛评测尚未完成。');   // 只有真正的编译失败才可以在未评分时写回。
        }
        ContestEntry::saveScore($this->score(), $this->verdicts(), $this->averageMemory(), $this->totalTime(), $entryId);
    }

    /** 归还已经取得的租约并清理本次试跑上传的文件；同时用于正常结束和 PHP 请求退出。 */
    public function unlock(): void
    {
        if ($this->temporaryPath !== null) {
            unlink($this->temporaryPath);                              // 试跑产生的临时文件在正常和异常结束时统一删除。
            $this->temporaryPath = null;
        }
        if ($this->address === null) return;   // 结束回调可能再执行一次，已归还时不重复计次。
        $address = $this->address;
        $this->address = null;
        Judge::request($address, ['action'=>'unlock', 'job'=>$this->lease, 'uid'=>$this->options['uid']]);   // 带原租约归还机位，不能解开其他选手的锁。
    }
}
