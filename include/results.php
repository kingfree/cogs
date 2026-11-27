<?php
/**
 * 成绩页用 评测结果('AW', 10) 显示两个测试点，用 评测信息('AW') 展开中文判定。
 * 新增判定时只需在 JUDGE_LABELS 填一个字母和名称。每个字母沿用已有样式 jA、jW 等。
 */
const JUDGE_LABELS = [
    'A'=>'答案正确', 'W'=>'答案错误', 'T'=>'超过时间限制', 'M'=>'超过内存限制',
    'E'=>'运行时错误', 'R'=>'没有输出文件', 'C'=>'编译错误', 'D'=>'没有测试点数据',
    'N'=>'没有源代码', 'P'=>'答案部分正确',
];

/** 题面和比赛共用编程语言选项。每个页面只有一组带键盘快捷键所依赖的元素 ID。 */
function renderLanguageChoices(bool $shortcuts): void
{
    foreach (Language::TYPES as [$extension, $name, $compiler]) {      // 新语言在评测语言表添加后，普通与比赛题面一起生效。
        if ($compiler === null) continue;                             // 提交答案的题目在单独的表单中上传 ZIP。
        $id = $shortcuts ? ' id="'.h($extension).'"' : '';            // 弹窗和页内表单不能有重复的 HTML id。
        $checked = $extension === 'cpp' ? ' checked="checked"' : '';  // 两个入口都沿用默认 C++。
        echo '<label class="radio inline"><input type="radio" name="lang" value="'.h($extension).'"'.$id.$checked.'/>'.h($name).'</label>';
    }
}

/** 将数据库里的语言编号转回扩展名，未知编号返回空串。 */
function langnumtostr($id) { return Language::TYPES[$id][0] ?? ''; }

/** 题目每两级显示一颗实心星，剩下一级显示空心星。 */
function 难度($level)
{
    $level = max(0, (int)$level);
    return str_repeat('★', intdiv($level, 2)).str_repeat('☆', $level % 2);
}

/** 按给定列数排列判定字母；列表预览可只显示第一行。 */
function 评测结果($codes, $columns = 10, $firstRowOnly = false)
{
    echo "<span class='judge'>";
    foreach (str_split((string)$codes) as $index => $code) {           // 字母的位置就是测试点的次序。
        if (isset(JUDGE_LABELS[$code])) echo "<span class='j$code'>$code</span>"; // 仅已知判定能进入 CSS 类名。
        if (($index + 1) % $columns !== 0) continue;                  // 一行未满时紧接着显示下一个点。
        echo '<br />';
        if ($firstRowOnly) break;                                     // 摘要页只保留首行，详情页继续展开。
    }
    echo '</span>';
}

/** 返回各点的中文判定 HTML；保存分数的调用方可把它嵌入评测进度。 */
function 评测信息a($codes)
{
    if ($codes === '' || $codes === null) return '未评测';              // 新提交还没有测试点结果。
    $lines = '';
    foreach (str_split((string)$codes) as $code) {                       // 每一行对应原提交中一个测试点。
        if (isset(JUDGE_LABELS[$code])) $lines .= "<span class='j$code'>".JUDGE_LABELS[$code].'</span>';
        $lines .= '<br />';                                           // 旧数据的未知判定也占一行，保持测试点对齐。
    }
    return $lines;
}

/** 把逐点中文说明输出到页面的评测结果区域。 */
function 评测信息($codes) { echo "<span class='judge'>".评测信息a($codes).'</span>'; }

/** 显示当前账号对一道题的解题进度，图标链接到本人最近提交。 */
function 是否通过($submission)
{
    if (empty($_SESSION['ID'])) return;                               // 游客没有个人解题记录。
    if (!$submission) { echo "<span class='icon-minus'>－</span>"; return; }
    $icon = $submission['accepted'] ? 'ok' : 'pause';                  // 已通过显示勾，做过但未通过显示暂停。
    echo '<a href="'.路径('submit/code.php?id='.(int)$submission['sid']).'"><span class="icon-'.$icon.'"></span></a>';
}
