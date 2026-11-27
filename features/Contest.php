<?php
/**
 * 比赛题单保存名称、出题人及题目的顺序，一张题单可以安排多场比赛。
 * contest/editcompbase.php 编辑题单，比赛起止时间由 ContestRound 单独保存。
 * 在已加载站点配置的入口读取题单：$contest = Contest::byId(1)[0] ?? null;
 */
final class Contest
{
    /** 最近在线竞赛页读取固定的外站赛程。远端不可用时返回空列表，所有标题均按普通文本展示。 */
    public static function recentElsewhere(): array
    {
        $context = stream_context_create(['http'=>['timeout'=>3, 'follow_location'=>0]]); // 外站离线不能长时间阻塞本站页面。
        $json = @file_get_contents('http://contests.acmicpc.info/contests.json', false, $context, 0, 524288); // 赛程摘要限制在半 MiB。
        $rows = $json === false ? null : json_decode($json, true);
        if (!is_array($rows)) return []; // 短响应、维护页面和无效 JSON 都没有可列出的场次。
        $events = [];
        foreach ($rows as $row) {
            if (!is_array($row) || !is_string($row['name'] ?? null)) continue; // 每一项至少要有赛事名称。
            $url = $row['link'] ?? null;
            if (!is_string($url) || !filter_var($url, FILTER_VALIDATE_URL) || !in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true)) continue; // 外站数据只能生成网页链接。
            $events[] = array_map(static fn($value) => is_scalar($value) ? (string)$value : '', // 容忍旧数据漏掉周几或赛事开放条件。
                array_intersect_key($row, array_flip(['oj', 'name', 'link', 'start_time', 'week', 'access'])));
        }
        return $events;
    }

    /** 列出题单及其组织者，供比赛管理页一次读取。 */
    public static function withOrganizers()
    {
        return Database::rows(
            "SELECT compbase.*, userinfo.nickname, userinfo.realname, userinfo.email
             FROM compbase, userinfo
             WHERE userinfo.uid=compbase.ouid
             ORDER BY compbase.cbid DESC"
        );
    }

    /** 旧库用冒号存储赛题顺序。读者应使用这个方法解析，历史空项和重复题号会被略过。 */
    public static function problemIds(?string $stored): array
    {
        $ids = [];
        foreach (explode(':', $stored ?? '') as $number) { // 旧站点留空的题单也可进入编辑页。
            if (ctype_digit($number) && (int)$number > 0) $ids[(int)$number] = (int)$number;
        }
        return array_values($ids);
    }

    /** $id 为 null 时建立新题单。改名或改题单顺序会同时影响引用它的所有场次。 */
    public static function save(?int $id, string $name, array $selection, int $owner): void
    {
        $name = trim($name); // 名称限制按编辑表单去除首尾空格后的字符数计算。
        if ($name === '' || mb_strlen($name) > 24) throw new DomainException('比赛名须为 1 至 24 个字符。');
        $ids = []; // 以题号为键保留首次勾选的次序。
        foreach ($selection as $number) {
            if (!is_scalar($number) || !ctype_digit((string)$number) || (int)$number < 1) throw new DomainException('题目编号无效。');
            $ids[(int)$number] = (int)$number; // 复选框重复提交同一题时只保留第一次的位置。
        }
        if ($ids) {
            $marks = implode(',', array_fill(0, count($ids), '?'));
            $known = Database::rows("SELECT pid FROM problem WHERE pid IN ($marks)", array_values($ids)); // 一次核对全部题号，缺少任一题便退回表单。
            if (count($known) !== count($ids)) throw new DomainException('选中的题目已不存在。');
        }
        $stored = implode(':', $ids); // 现有场次与旧数据库继续读取冒号分隔的有序题单。
        try {
            if ($id === null) {
                Database::execute('INSERT INTO compbase(cname, contains, ouid) VALUES(?, ?, ?)', [$name, $stored, $owner]);
            } else {
                if (!self::byId($id)) throw new DomainException('比赛题单不存在。'); // 不为不存在的旧题单分配新编号。
                Database::execute('UPDATE compbase SET cname=?, contains=? WHERE cbid=?', [$name, $stored, $id]); // 原出题人和关联场次继续保留。
            }
        } catch (PDOException $error) {
            if (($error->errorInfo[1] ?? null) === 1062) throw new DomainException('已有同名比赛题单。'); // 唯一索引也能识别同时创建的同名题单。
            throw $error;
        }
    }

    /** 按题单编号 cbid 读取，找不到时为空数组。 */
    public static function byId($id)
    {
        return Database::rows(
            "SELECT * FROM compbase WHERE cbid=?",
            [$id]
        );
    }

    /** 返回安排场次所需的题单编号和名称。 */
    public static function choices()
    {
        return Database::rows(
            "SELECT cname, cbid FROM compbase"
        );
    }
}
