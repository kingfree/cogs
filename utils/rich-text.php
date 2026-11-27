<?php
/**
 * echo richText('<p>样例输出：<b>42</b></p>'); 返回可嵌入页面的题面、公告或评论 HTML。
 * 只复制可显示的节点及其允许的属性。原始标记不会直接送回浏览器。
 */
function richText($html): string
{
    $source = new DOMDocument();
    $source->loadHTML('<meta charset="utf-8"><body>'.(string)$html.'</body>',
        LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);             // 允许旧题面的残缺 HTML，解析时禁止取网络资源。
    $body = $source->getElementsByTagName('body')->item(0);
    if (!$body) return '';                                            // 输入里没有正文，页面不显示额外内容。
    $output = new DOMDocument('1.0', 'UTF-8');                         // 在新文档重建，输入中的属性不会自动继承。
    $root = $output->appendChild($output->createElement('div'));
    foreach ($body->childNodes as $node) copyRichTextNode($node, $root); // 保留正文中行文、列表与表格的原有次序。
    $result = '';
    foreach ($root->childNodes as $node) $result .= $output->saveHTML($node); // 最外层 div 只为重建服务。
    return $result;
}

/** 把正文的一棵子树复制到展示文档。普通的未知容器展开，禁止的嵌入内容连同子树丢弃。 */
function copyRichTextNode(DOMNode $node, DOMElement $parent): void
{
    $document = $parent->ownerDocument;
    if ($node instanceof DOMText) {
        $parent->appendChild($document->createTextNode($node->nodeValue)); // 文字中的尖括号按文字保存。
        return;
    }
    if (!($node instanceof DOMElement)) return;                       // 跳过注释、处理指令等非正文节点。
    $tag = $node->tagName;
    if (in_array($tag, ['script','style','iframe','object','embed','svg','math','template'], true)) return; // 整个嵌入块不进入页面。
    static $allowed = ['p','div','section','br','hr','h1','h2','h3','h4','h5','h6','b','strong','i','em','u','s',
        'sub','sup','pre','code','blockquote','ul','ol','li','span','a','img','table','thead','tbody','tfoot',
        'tr','td','th','caption','dl','dt','dd','center','font'];
    if (in_array($tag, $allowed, true)) {
        $parent = $parent->appendChild($document->createElement($tag)); // 只在名单内保留标签本身。
        foreach (['href','src','alt','title','class','colspan','rowspan','width','height','style'] as $name) {
            if (!$node->hasAttribute($name)) continue;
            $value = trim($node->getAttribute($name));                 // 判断浏览器最终会看到的属性值。
            if (richTextAttributeAllowed($tag, $name, $value)) $parent->setAttribute($name, $value);
        }
    }
    foreach ($node->childNodes as $child) copyRichTextNode($child, $parent); // 被展开的容器仍保留可显示的文字和子标签。
}

/** 检查链接、图片及样式的属性值；输入的事件名从未列入复制清单。 */
function richTextAttributeAllowed(string $tag, string $name, string $value): bool
{
    if ($name === 'href' || $name === 'src') {
        if ($tag !== ($name === 'href' ? 'a' : 'img')) return false;  // 链接只属于 a，图片地址只属于 img。
        return !preg_match('/[[:cntrl:]\\\\]/', $value)
            && !preg_match('~^(?!https?:)[a-z][a-z0-9+.-]*:~i', $value); // 仅保留相对地址与 HTTP(S)。
    }
    if ($name === 'style') return (bool)preg_match('/^(?:(?:color|font-size|text-align)\s*:\s*[a-z0-9#.-]+\s*;?\s*)+$/iD', $value); // 题面只需文字排版。
    if ($name === 'class') return (bool)preg_match('/^[a-z0-9 _-]+$/iD', $value); // 样式名允许空格分隔。
    return true;                                                      // 剩余的是已列出的文字描述或表格、图片尺寸。
}
