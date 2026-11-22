<?php
// 练习按每题最好成绩计分，比赛成绩另乘站点设置中的比赛权重。例 Ranking::recalculate(1)。
final class Ranking
{
    public static function recalculate($userId)
    {
        global $SET;
        $practice = Database::rows("SELECT s.pid, COUNT(*) AS attempts, MAX(s.score) AS best, p.difficulty
            FROM submit s JOIN problem p ON p.pid=s.pid WHERE s.uid=? GROUP BY s.pid, p.difficulty", [$userId]);
        $submitted = $accepted = $grade = 0;
        foreach ($practice as $row) {
            $submitted += $row['attempts'];
            $accepted += $row['best'] == 100 ? 1 : 0;
            $grade += ($row['difficulty'] + 1) * $row['best'] * $SET['problem_weight'] / 100;
        }
        foreach (Database::rows("SELECT e.score, p.difficulty FROM compscore e JOIN problem p ON p.pid=e.pid
                                 WHERE e.uid=?", [$userId]) as $row) {
            $grade += ($row['difficulty'] + 1) * $row['score'] * $SET['contest_weight'] / 100;
        }
        Database::execute("UPDATE userinfo SET submited=?, accepted=?, grade=? WHERE uid=?",
            [$submitted, $accepted, (int)$grade, $userId]);
        return [$submitted, $accepted, (int)$grade];
    }
}
