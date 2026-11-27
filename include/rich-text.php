<?php
/**
 * 题面、文章和编辑器提供的内容可以保留表格、图片和排版。这个函数解析 HTML，再构造可显示的节点树。
 * 例：echo richText('<p>样例输出：<b>42</b></p>');
 */
function richText($html): string
{
    $source = new DOMDocument();
    $source->loadHTML('<meta charset="utf-8"><body>'.(string)$html.'</body>', LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
    // 重新生成展示用节点；输入 DOM 上未允许的事件属性、脚本节点不会一起复制。
    $output = new DOMDocument('1.0', 'UTF-8');
    $root = $output->appendChild($output->createElement('div'));
    $allowed = explode(' ', 'p div section br hr h1 h2 h3 h4 h5 h6 b strong i em u s sub sup pre code blockquote ul ol li span a img table thead tbody tfoot tr td th caption dl dt dd center font');
    $copy = function ($node, $parent) use (&$copy, $allowed, $output) {
        if ($node instanceof DOMText) { $parent->appendChild($output->createTextNode($node->nodeValue)); return; }
        if (!($node instanceof DOMElement) || in_array($node->tagName, ['script','style','iframe','object','embed','svg','math','template'], true)) return;
        if (in_array($node->tagName, $allowed, true)) {
            $parent = $parent->appendChild($output->createElement($node->tagName));
            foreach (['href','src','alt','title','class','colspan','rowspan','width','height','style'] as $attr) {
                if (!$node->hasAttribute($attr)) continue;
                $value = trim($node->getAttribute($attr));
                // 链接只在 a 上保留，图片地址只在 img 上保留；两者都只接受相对地址或 HTTP(S)。
                if ($attr === 'href' || $attr === 'src') {
                    if ((($attr === 'href') !== ($node->tagName === 'a')) || ($attr === 'src' && $node->tagName !== 'img')) continue;
                    if (preg_match('/[[:cntrl:]\\\\]/', $value) || preg_match('~^(?!https?:)[a-z][a-z0-9+.-]*:~i', $value)) continue;
                } elseif ($attr === 'style') {
                    if (!preg_match('/^(?:(?:color|font-size|text-align)\s*:\s*[a-z0-9#.-]+\s*;?\s*)+$/iD', $value)) continue;
                } elseif ($attr === 'class' && !preg_match('/^[a-z0-9 _-]+$/iD', $value)) continue;
                $parent->setAttribute($attr, $value);
            }
        }
        foreach ($node->childNodes as $child) $copy($child, $parent);
    };
    $body = $source->getElementsByTagName('body')->item(0);
    if ($body) foreach ($body->childNodes as $node) $copy($node, $root);
    $result = '';
    foreach ($root->childNodes as $node) $result .= $output->saveHTML($node);
    return $result;
}
