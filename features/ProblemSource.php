<?php
// 外站题面规则放在 problem-sources，每站一个文件；例 ProblemSource::draft('poj', 1001)。
final class ProblemSource
{
    public static function sources(): array
    {
        static $sources;
        if ($sources !== null) return $sources;
        $sources = [];
        foreach (glob(dirname(__DIR__).'/problem-sources/*.php') as $file) $sources[basename($file, '.php')] = require $file;
        return $sources;
    }

    public static function draft(string $site, int $id, ?string $html = null): array
    {
        $rule = self::sources()[$site] ?? null;
        if (!$rule || $id < ($rule['minimum'] ?? 1000)) throw new DomainException('题目来源或编号无效。');
        $url = strtr($rule['url'], ['{id}'=>(string)$id, '{volume}'=>(string)intdiv($id, 100)]);
        if ($html === null) {
            $http = stream_context_create(['http'=>['timeout'=>5, 'follow_location'=>0, 'user_agent'=>'COGS problem import']]);
            $html = @file_get_contents($url, false, $http, 0, 2097153);
        }
        if (!$html || strlen($html) > 2097152) throw new DomainException('来源站点未返回可用题面。');
        $dom = new DOMDocument();
        if (!$dom->loadHTML('<meta charset="utf-8">'.$html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING)) {
            throw new DomainException('题面解析失败。');
        }
        $xpath = new DOMXPath($dom);
        $title = trim($xpath->query($rule['title'])->item(0)->textContent ?? '');
        if ($title === '') throw new DomainException('来源站点的题面格式已变化。');
        $origin = parse_url($url, PHP_URL_SCHEME).'://'.parse_url($url, PHP_URL_HOST);
        foreach ($xpath->query('//img[@src]') as $img) {
            $src = $img->getAttribute('src');
            if (str_starts_with($src, '//')) $img->setAttribute('src', 'https:'.$src);
            elseif (str_starts_with($src, '/')) $img->setAttribute('src', $origin.$src);
            elseif (!parse_url($src, PHP_URL_SCHEME)) $img->setAttribute('src', substr($url, 0, strrpos($url, '/') + 1).$src);
        }
        $headings = ['题目描述','输入格式','输出格式','样例输入','样例输出','提示','来源'];
        $detail = '';
        foreach ($rule['sections'] as $i=>$selector) {
            $node = $xpath->query($selector)->item(0);
            if ($node) $detail .= '<h3>【'.$headings[$i].'】</h3>'.$dom->saveHTML($node);
        }
        $text = $dom->textContent;
        $time = 1000;
        $memory = 128;
        if (preg_match('/(?:Time Limit\s*:?\s*)?(\d+(?:\.\d+)?)\s*(ms|sec(?:ond)?s?)\b/i', $text, $matches)) {
            $time = (int)round($matches[1] * (strtolower($matches[2]) === 'ms' ? 1 : 1000));
        }
        if (preg_match('/(?:Memory Limit\s*:?\s*)?(\d+)\s*(kb?|mb)\b/i', $text, $matches)) {
            $memory = (int)ceil($matches[1] / (strtolower($matches[2]) === 'mb' ? 1 : 1024));
        }
        return ['probname'=>trim(preg_replace('/^\d+\s*[.:：-]\s*/u', '', $title)), 'filename'=>($rule['prefix'] ?? $site).'_'.$id,
            'timelimit'=>$time,'memorylimit'=>$memory,'detail'=>richText($detail).'<p><a href="'.h($url).'">'.h($rule['name']).' '.$id.'</a></p>',
            'datacnt'=>10,'difficulty'=>2,'readforce'=>0,'plugin'=>1,'group'=>0,'submitable'=>1];
    }
}
