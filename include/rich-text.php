<?php
// 题面和文章允许排版、图片和链接；DOM 重建保证输出只使用下列元素和属性。
function richText($html): string
{
    $source = new DOMDocument();
    $source->loadHTML('<meta charset="utf-8"><body>'.(string)$html.'</body>', LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
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
