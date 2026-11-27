<?php
/**
 * 管理员在 problem/problem_import_xml.php 导入 FPS 题包。每个 item 对应一题，样例、测试点、图片和特判随题保存。
 * 例：$ids = Fps::import($_FILES['fps']['tmp_name'], (int)$_SESSION['ID']);
 */
final class Fps
{
    /** 将选中的题目依次写到当前 HTTP 输出流。测试点文件按题目文件名和 1 起始的编号查找。 */
    public static function export($rows): void
    {
        // 题包可包含很多题，直接把 XML 写给浏览器，逐题释放写入缓冲。
        $xml = new XMLWriter();
        $xml->openURI('php://output');
        $xml->startDocument('1.0', 'UTF-8');
        // FPS 1.1 用一个 fps 根节点包住所有 item。
        $xml->startElement('fps');
        $xml->writeAttribute('version', '1.1');
        foreach ($rows as $problem) {
            // 每个 item 从本站题目标题和完整 HTML 题面开始。
            $xml->startElement('item');
            $xml->writeElement('title', $problem['probname']);
            $xml->startElement('description');
            // 标记完整题面，导回本站时不用再往题面里追加一次样例。
            $xml->writeAttribute('cogs_detail', 'full');
            $xml->text($problem['detail']);
            $xml->endElement();
            // 把题面中本站托管的图片和本站的评测模式一起带走。
            self::writeImages($xml, $problem['detail']);
            $xml->writeElement('cogs_plugin', (string)$problem['plugin']);
            foreach (['time_limit' => ['timelimit', 'ms'], 'memory_limit' => ['memorylimit', 'mb']] as $tag => [$field, $unit]) {
                // FPS 由字段的 unit 告诉接收站点时间和内存的计量单位。
                $xml->startElement($tag);
                $xml->writeAttribute('unit', $unit);
                // 数值保持数据库中的单位，接收端据上一个属性换算。
                $xml->text((string)$problem[$field]);
                $xml->endElement();
            }
            // 旧库可能有尚未配置数据文件名的题，依然可导出题面。
            $name = $problem['filename'];
            if (TestData::name($name)) {
                $files = TestData::files($name);
                // sample 文件是给做题者看的例子，和评测用例分别导出。
                foreach (['sample.in' => 'sample_input', 'sample.out' => 'sample_output'] as $file => $tag) {
                    if (isset($files[$file])) $xml->writeElement($tag, file_get_contents($files[$file]));
                }
                for ($i = 1; $i <= $problem['datacnt']; $i++) {
                    // 按同一点号写输入和标准答案，导入端按出现顺序配对。
                    foreach (['in' => 'test_input', 'ans' => 'test_output'] as $ext => $tag) {
                        $file = $name.$i.'.'.$ext;
                        // 只随包带实际存在的点，缺失文件不能用空答案代替。
                        if (isset($files[$file])) $xml->writeElement($tag, file_get_contents($files[$file]));
                    }
                }
                foreach ($files as $file => $path) {
                    // 目录里还有答案和说明，只有评测机支持的源码才写成特判。
                    if (!TestData::checkerFile($file)) continue;
                    $extension = pathinfo($file, PATHINFO_EXTENSION);
                    $language = ['c'=>'C', 'cc'=>'C++', 'cpp'=>'C++', 'pas'=>'Pascal', 'pp'=>'Pascal', 'php'=>'PHP'][$extension];
                    // 标准特判用 spj，交互器等本站格式保留原文件名。
                    $xml->startElement(str_starts_with($file, 'spj.') ? 'spj' : 'cogs_checker');
                    $xml->writeAttribute('language', $language);
                    $xml->writeAttribute('filename', $file);
                    // 放进包中的是源文件内容，接收站点根据语言自行编译。
                    $xml->text(file_get_contents($path));
                    $xml->endElement();
                }
            }
            $xml->endElement();
            // 一题写完即发送，浏览器可边收边存。
            $xml->flush();
        }
        $xml->endElement();
        $xml->endDocument();
    }

    /** 把当前题面引用的站内图片写进 XML；同一地址只写一次，外站图片保留原链接。 */
    private static function writeImages(XMLWriter $xml, string $detail): void
    {
        $document = new DOMDocument();
        // 从题面 HTML 中找图，只解析已有字符串。
        if ($detail === '' || !@$document->loadHTML('<?xml encoding="UTF-8">'.$detail, LIBXML_NONET)) return;
        // 同一张图片可以在题面里出现多次，附件字节只需随题包带一份。
        $written = [];
        foreach ($document->getElementsByTagName('img') as $image) {
            $src = $image->getAttribute('src');
            // 仅匹配本站 images 的普通文件名，远程图片不随包下载。
            if (isset($written[$src]) || !preg_match('~^(?:\.\./)?images/([A-Za-z0-9_-]+\.(?:png|jpe?g|gif|bmp))$~iD', $src, $match)) continue;
            $path = publicFilePath('images').'/'.$match[1];
            if (!is_file($path) || is_link($path) || filesize($path) > 16 * 1048576) continue;
            // 把原引用和图片字节成对保存，导入时可替换为新的地址。
            $xml->startElement('img');
            $xml->writeElement('src', $src);
            // 用 Base64 表示二进制图片，避免 XML 对图片字节做字符转换。
            $xml->writeElement('base64', base64_encode(file_get_contents($path)));
            $xml->endElement();
            // 内容写出后才登记地址，重复引用继续共用同一附件。
            $written[$src] = true;
        }
    }

    /** 返回本次新增题目的 ID 列表，遇到同名题目跳过。每道题单独提交，某题失败时已导入的前序题目保留。 */
    public static function import(string $file, int $author): array
    {
        if (filesize($file) > 64 * 1048576) throw new DomainException('题包超过 64 MiB。');
        $content = file_get_contents($file);
        // FPS 不需要 DTD。禁止外部实体，同时保留常见的 CDATA 题面。
        if (stripos($content, '<!DOCTYPE') !== false) throw new DomainException('FPS 不支持 DTD。');
        $xml = simplexml_load_string($content, SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOCDATA | LIBXML_NOERROR | LIBXML_NOWARNING);
        if ($xml === false || $xml->getName() !== 'fps') throw new DomainException('FPS XML 格式错误。');
        // 结果列表稍后供上传页面报告新增题目的数量。
        $imported = [];
        // 按题处理，导入结果只收录本次真正新增的题号。
        foreach ($xml->item as $item) {
            $title = trim((string)$item->title);
            if ($title === '' || mb_strlen($title) > 50) throw new DomainException('题目标题无效。');
            // 同名题会跳过；重新导入已经成功的部分不会覆盖已有题。
            if (Problem::byTitle($title)) continue;
            // 先换算为本站保存的毫秒和 MiB，再按评测机的边界检查。
            $time = (float)$item->time_limit * (strtolower((string)$item->time_limit['unit']) === 's' ? 1000 : 1);
            $memory = (float)$item->memory_limit / (strtolower((string)$item->memory_limit['unit']) === 'kb' ? 1024 : 1);
            $count = count($item->test_input);
            // 每个测试输入都必须有同序号的标准答案。
            if ($time <= 0 || $time > GradingLimits::TIME_MS || $memory <= 0 || $memory > GradingLimits::MEMORY_MIB || $count > GradingLimits::CASES || $count !== count($item->test_output)) {
                throw new DomainException('资源限制无效，或测试输入输出未配对。');
            }
            // 资源校验通过才组装富文本，避免留下不完整题面。
            $detail = self::description($item);
            $db = Database::connection();
            // 一题一个事务。测试点和图片在文件系统里，需要在失败分支手工清理。
            $db->beginTransaction();
            // 记录本次题目新建的图片和数据目录，供失败分支清理。
            $created = [];
            $dir = null;
            try {
                // 老 FPS 包没有本站标志，带标准特判时选特判，否则选普通评测。
                $plugin = isset($item->cogs_plugin) ? (string)$item->cogs_plugin : (trim((string)$item->spj) !== '' ? '0' : '1');
                if (!in_array($plugin, ['-1','0','1','2','3','4'], true)) throw new DomainException('无法识别的评测方式。');
                // 先取得新题号，用它关联稍后落盘的数据和图片。
                Problem::importTitle($title, (int)ceil($time), (int)ceil($memory), $detail, time(), $author, (int)$plugin);
                $id = (int)$db->lastInsertId();
                $name = (string)$id;
                // 老站可能已经用题号做过目录名或文件名；保留旧数据，为新题另选名称。
                if (is_dir(TestData::directory($name)) || Problem::byFilename($name)) $name = 'fps-'.bin2hex(random_bytes(7));
                $dir = TestData::directory($name, true);
                // 先安装测试点与判题器，然后将图片引用改为本站地址。
                self::importCases($item, $name, $count);
                self::importCheckers($item, $name);
                self::importImages($item, $id, $created);
                // 等题目依赖的文件都就位，再保存最终目录名和用例数。
                Problem::setData($count, $name, $id);
                $db->commit();
                // 仅已提交的题号才算本次导入成功。
                $imported[] = $id;
            } catch (Throwable $error) {
                // 数据库回滚只撤销本题记录；接下来删除这次生成的目录和图片。
                $db->rollBack();
                if ($dir) {
                    // 目录由本次导入新建，清掉里面已写出的测试点后再删除目录。
                    foreach (new FilesystemIterator($dir) as $entry) unlink($entry->getPathname());
                    rmdir($dir);
                }
                // 图片放在共享目录，只移除本题创建的具体文件。
                foreach ($created as $image) if (is_file($image)) unlink($image);
                throw $error;
            }
        }
        return $imported;
    }

    /** 导入 FPS 的说明和样例；本站导出的完整题面已经含样例，无需重复添加。 */
    private static function description(SimpleXMLElement $item): string
    {
        $detail = (string)$item->description;
        foreach (['input'=>'输入格式','output'=>'输出格式','sample_input'=>'样例输入',
            'sample_output'=>'样例输出','hint'=>'提示','source'=>'来源'] as $tag=>$heading) {
            // 外站有时没有提示或来源，没有内容的栏目不生成空标题。
            $text = (string)$item->$tag;
            // 有些站点单独给出样例；本站导出的完整题面已有同样内容。
            if ($text === '' || ((string)$item->description['cogs_detail'] === 'full' && str_starts_with($tag, 'sample_'))) continue;
            // 样例按原始空白排版，其他说明保留题目的富文本标记。
            $detail .= '<h3>【'.$heading.'】</h3>'.(str_starts_with($tag, 'sample_') ? '<pre>'.h($text).'</pre>' : $text);
        }
        return $detail;
    }

    /** 官方 FPS 用 test_input/test_output 对应成对测试点；落盘改为评测机识别的文件名。 */
    private static function importCases(SimpleXMLElement $item, string $name, int $count): void
    {
        // 给选手展示的样例用固定文件名，独立于计分用例。
        foreach (['sample_input'=>'sample.in','sample_output'=>'sample.out'] as $tag=>$leaf) {
            if (isset($item->$tag)) TestData::store($name, $leaf, (string)$item->$tag);
        }
        // XML 下标从零开始，评测机从第一号测试点开始。
        for ($n=0; $n<$count; $n++) {
            TestData::store($name, $name.($n+1).'.in', (string)$item->test_input[$n]);
            TestData::store($name, $name.($n+1).'.ans', (string)$item->test_output[$n]);
        }
    }

    /** 导入标准 SPJ 或本站附带文件名的特判器；只保存源码，由评测机隔离编译。 */
    private static function importCheckers(SimpleXMLElement $item, string $name): void
    {
        foreach (['spj', 'cogs_checker'] as $tag) {
            foreach ($item->$tag as $checker) {
                // 空节点在一些导出器中表示普通评测，不创建特判文件。
                if (trim((string)$checker) === '') continue;
                $leaf = (string)$checker['filename'];
                if ($leaf === '' && $tag === 'spj') {
                    // 标准 FPS 可只有语言没有文件名，补成评测机支持的 spj 源码名。
                    $language = strtolower((string)$checker['language']);
                    $ext = ['c'=>'c','pascal'=>'pas','pas'=>'pas','c++'=>'cpp','cpp'=>'cpp',''=>'cpp'][$language] ?? null;
                    if (!$ext) throw new DomainException('无法识别的特判语言。');
                    // 标准特判器使用固定文件名前缀，语言决定编译器。
                    $leaf = 'spj.'.$ext;
                }
                // 扩展名和整个文件名都必须属于允许的判题器。
                if (!TestData::checkerFile($leaf)) throw new DomainException('特判文件名无效。');
                TestData::store($name, $leaf, (string)$checker);
            }
        }
    }

    /** 写入本题内嵌图片并替换题面引用。先登记路径，以便中途出错时清理已创建文件。 */
    private static function importImages(SimpleXMLElement $item, int $id, array &$created): void
    {
        foreach ($item->img as $img) {
            // 严格解码内嵌数据并根据图片签名确定文件类型。
            $bytes = base64_decode((string)$img->base64, true);
            $info = $bytes !== false ? @getimagesizefromstring($bytes) : false;
            $ext = [IMAGETYPE_PNG=>'png',IMAGETYPE_JPEG=>'jpg',IMAGETYPE_GIF=>'gif',IMAGETYPE_BMP=>'bmp'][$info[2] ?? 0] ?? null;
            // 存图的同时必须能从原题面识别出要替换的引用。
            if (!$ext || (string)$img->src === '') throw new DomainException('题面图片格式错误。');
            // 图片放入管理员配置的公开目录，旧 XML 的路径仅用于匹配题面。
            $folder = publicFilePath('images');
            if (!is_dir($folder)) mkdir($folder, 0750);
            // 一题可有多张图，编号和随机名避免与既有公开图片重名。
            $leaf = 'pimg'.$id.'_'.bin2hex(random_bytes(6)).'.'.$ext;
            $path = $folder.'/'.$leaf;
            $created[] = $path;
            // 完整写入图片后才把其公开 URL 写回题面。
            if (file_put_contents($path, $bytes) !== strlen($bytes)) throw new RuntimeException('图片写入失败。');
            Problem::replaceImage((string)$img->src, '../images/'.$leaf, $id);
        }
    }
}
