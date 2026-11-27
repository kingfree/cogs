<?php
/**
 * problem/editprob.php 按站点和题号生成可编辑的题面草稿。适配一个新站点时，在 problem-sources/ 增加该站的规则文件。
 * 例：$draft = ProblemSource::draft('poj', 1001);
 */
final class ProblemSource
{
    /** 按规则文件名生成来源标识；同一请求中只加载一次规则。 */
    public static function sources(): array
    {
        static $sources;
        // 一个页面会先画来源列表，再根据选择取题，直接共用本次请求已加载的规则。
        if ($sources !== null) return $sources;
        $sources = [];
        // 文件名即表单中的站点标识；增加一个来源只需添加对应规则文件。
        foreach (glob(dirname(__DIR__).'/problem-sources/*.php') as $file) $sources[basename($file, '.php')] = require $file;
        return $sources;
    }

    /** 传入站点标识和题号获得编辑页的初始字段。测试可传入该站 HTML 样本，跳过 HTTP 获取。 */
    public static function draft(string $site, int $id, ?string $html = null): array
    {
        // 只接受已登记的来源，并遵守各站点题号的起始范围。
        $rule = self::sources()[$site] ?? null;
        if (!$rule || $id < ($rule['minimum'] ?? 1000)) throw new DomainException('题目来源或编号无效。');
        // UVa 以题号的百位划分卷目录，普通来源仅代入题号。
        $url = strtr($rule['url'], ['{id}'=>(string)$id, '{volume}'=>(string)intdiv($id, 100)]);
        // 只从规则文件指定的地址取题，不跟随外站重定向；本地格式测试直接传 HTML。
        if ($html === null) {
            $http = stream_context_create(['http'=>['timeout'=>5, 'follow_location'=>0, 'user_agent'=>'COGS problem import']]);
            // 额外读取一个字节，用来区分正好达到 2 MiB 和超过限额。
            $html = @file_get_contents($url, false, $http, 0, 2097153);
        }
        if (!$html || strlen($html) > 2097152) throw new DomainException('来源站点未返回可用题面。');
        // 以 UTF-8 解析来源页面，禁止解析器向外部资源发请求。
        $dom = new DOMDocument();
        if (!$dom->loadHTML('<meta charset="utf-8">'.$html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING)) {
            throw new DomainException('题面解析失败。');
        }
        // 各站点的标题和内容规则都使用 XPath，查询同一份已解析文档。
        $xpath = new DOMXPath($dom);
        // 每站首先验证自己的标题选择器。取不到标题时应让管理员修改规则，不生成空题。
        $title = trim($xpath->query($rule['title'])->item(0)->textContent ?? '');
        if ($title === '') throw new DomainException('来源站点的题面格式已变化。');
        // 本站编辑器打开的是另一地址，图片路径须相对于来源站补全。
        $origin = parse_url($url, PHP_URL_SCHEME).'://'.parse_url($url, PHP_URL_HOST);
        foreach ($xpath->query('//img[@src]') as $img) {
            $src = $img->getAttribute('src');
            // 无协议但自带域名的图片优先使用 HTTPS。
            if (str_starts_with($src, '//')) $img->setAttribute('src', 'https:'.$src);
            // 根路径相对站点域名，普通路径相对于来源题面所在目录。
            elseif (str_starts_with($src, '/')) $img->setAttribute('src', $origin.$src);
            elseif (!parse_url($src, PHP_URL_SCHEME)) $img->setAttribute('src', substr($url, 0, strrpos($url, '/') + 1).$src);
        }
        // 早期规则只写 XPath 列表；保留旧顺序，新规则直接声明标题。
        $legacyHeadings = ['题目描述','输入格式','输出格式','样例输入','样例输出','提示','来源'];
        $detail = '';
        foreach ($rule['sections'] as $heading => $selector) {
            // 新规则自带栏目标题，只有旧规则需要由序号找标题。
            if (is_int($heading)) $heading = $legacyHeadings[$heading] ?? '';
            // 缺少可选段落时略过，仅把命中的首个元素带入草稿。
            $node = $xpath->query($selector)->item(0);
            if ($node) $detail .= '<h3>【'.h($heading).'】</h3>'.$dom->saveHTML($node);
        }
        $text = $dom->textContent;
        // 外站没有标明资源限制时，草稿默认 1 秒、128 MiB。
        $time = 1000;
        $memory = 128;
        if (preg_match('/(?:Time Limit\s*:?\s*)?(\d+(?:\.\d+)?)\s*(ms|sec(?:ond)?s?)\b/i', $text, $matches)) {
            // 时间统一存毫秒；来源使用秒时才乘一千。
            $time = (int)round($matches[1] * (strtolower($matches[2]) === 'ms' ? 1 : 1000));
        }
        if (preg_match('/(?:Memory Limit\s*:?\s*)?(\d+)\s*(kb?|mb)\b/i', $text, $matches)) {
            // 内存统一存 MiB；以 KB 给出的限制向上取整。
            $memory = (int)ceil($matches[1] / (strtolower($matches[2]) === 'mb' ? 1 : 1024));
        }
        // 移除标题开头重复的外站编号，题面附回链以便出题人校对。
        return ['probname'=>trim(preg_replace('/^\d+\s*[.:：-]\s*/u', '', $title)), 'filename'=>($rule['prefix'] ?? $site).'_'.$id,
            'timelimit'=>$time,'memorylimit'=>$memory,'detail'=>richText($detail).'<p><a href="'.h($url).'">'.h($rule['name']).' '.$id.'</a></p>',
            'datacnt'=>10,'difficulty'=>2,'readforce'=>0,'plugin'=>1,'group'=>0,'submitable'=>1];
    }
}
