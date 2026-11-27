<?php
/**
 * 按用户重算练习提交次数、通过题数和站点积分；改判时仍可从提交记录重建汇总。
 * 普通提交落库和管理员重算排行榜时调用。例：[$attempts, $accepted, $grade] = Ranking::recalculate(1);
 */
final class Ranking
{
    /**
     * 从练习和比赛的原始分数重新计算用户资料上的三项统计。
     * @param int|string $userId 要重算的用户编号。
     * @return array{0:int,1:int,2:int} 练习提交次数、练习通过题数、包含比赛的整数积分。
     */
    public static function recalculate($userId): array
    {
        global $SET;
        // 同题多次练习合并为一行；attempts 用于计数，best 用于积分。
        $practice = Database::rows("SELECT s.pid, COUNT(*) AS attempts, MAX(s.score) AS best, p.difficulty
            FROM submit s JOIN problem p ON p.pid=s.pid WHERE s.uid=? GROUP BY s.pid, p.difficulty", [$userId]);
        $submitted = $accepted = $grade = 0;                              // 没有交题记录的账号也要能重置到零。
        foreach ($practice as $row) {
            $submitted += $row['attempts'];                               // 尝试次数包含低分和重复提交。
            // 同题多次满分只记一道通过题。
            $accepted += $row['best'] == 100 ? 1 : 0;
            $grade += ($row['difficulty'] + 1) * $row['best'] * $SET['problem_weight'] / 100;  // 练习积分按该题最高分折算。
        }
        // 比赛按每份交卷记分，与练习使用不同的站点权重。
        foreach (Database::rows("SELECT e.score, p.difficulty FROM compscore e JOIN problem p ON p.pid=e.pid
                                 WHERE e.uid=?", [$userId]) as $row) {
            $grade += ($row['difficulty'] + 1) * $row['score'] * $SET['contest_weight'] / 100; // 比赛积分按每份交卷折算。
        }
        // 保留分数计算的小数部分直到最后写入用户积分。
        Database::execute("UPDATE userinfo SET submited=?, accepted=?, grade=? WHERE uid=?",
            [$submitted, $accepted, (int)$grade, $userId]);
        return [$submitted, $accepted, (int)$grade];
    }
}
