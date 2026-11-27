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
        $xml = new XMLWriter();
        $xml->openURI('php://output');
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('fps');
        $xml->writeAttribute('version', '1.1');
        foreach ($rows as $problem) {
            $xml->startElement('item');
            $xml->writeElement('title', $problem['probname']);
            $xml->startElement('description');
            // 标记完整题面，导回本站时不用再往题面里追加一次样例。
            $xml->writeAttribute('cogs_detail', 'full');
            $xml->text($problem['detail']);
            $xml->endElement();
            self::writeImages($xml, $problem['detail']);
            $xml->writeElement('cogs_plugin', (string)$problem['plugin']);
            foreach (['time_limit' => ['timelimit', 'ms'], 'memory_limit' => ['memorylimit', 'mb']] as $tag => [$field, $unit]) {
                $xml->startElement($tag);
                $xml->writeAttribute('unit', $unit);
                $xml->text((string)$problem[$field]);
                $xml->endElement();
            }
            $name = $problem['filename'];
            if (TestData::name($name)) {
                $files = TestData::files($name);
                foreach (['sample.in' => 'sample_input', 'sample.out' => 'sample_output'] as $file => $tag) {
                    if (isset($files[$file])) $xml->writeElement($tag, file_get_contents($files[$file]));
                }
                for ($i = 1; $i <= $problem['datacnt']; $i++) {
                    foreach (['in' => 'test_input', 'ans' => 'test_output'] as $ext => $tag) {
                        $file = $name.$i.'.'.$ext;
                        if (isset($files[$file])) $xml->writeElement($tag, file_get_contents($files[$file]));
                    }
                }
                foreach ($files as $file => $path) {
                    if (!self::checkerFile($file)) continue;
                    $extension = pathinfo($file, PATHINFO_EXTENSION);
                    $language = ['c'=>'C', 'cc'=>'C++', 'cpp'=>'C++', 'pas'=>'Pascal', 'pp'=>'Pascal', 'php'=>'PHP'][$extension];
                    $xml->startElement(str_starts_with($file, 'spj.') ? 'spj' : 'cogs_checker');
                    $xml->writeAttribute('language', $language);
                    $xml->writeAttribute('filename', $file);
                    $xml->text(file_get_contents($path));
                    $xml->endElement();
                }
            }
            $xml->endElement();
            $xml->flush();
        }
        $xml->endElement();
        $xml->endDocument();
    }

    /** 识别 COGS 能运行的判题器源码文件名，导入与导出使用同一名单。 */
    private static function checkerFile(string $file): bool
    {
        return $file === 'plugin.php' || preg_match('/^(?:spj|cena|interactor)\.(?:c|cc|cpp|pas|pp)$/D', $file);
    }

    /** 把当前题面引用的站内图片写进 XML；同一地址只写一次，外站图片保留原链接。 */
    private static function writeImages(XMLWriter $xml, string $detail): void
    {
        $document = new DOMDocument();
        if ($detail === '' || !@$document->loadHTML('<?xml encoding="UTF-8">'.$detail, LIBXML_NONET)) return;
        // 同一张图片可以在题面里出现多次，附件字节只需随题包带一份。
        $written = [];
        foreach ($document->getElementsByTagName('img') as $image) {
            $src = $image->getAttribute('src');
            if (isset($written[$src]) || !preg_match('~^(?:\.\./)?images/([A-Za-z0-9_-]+\.(?:png|jpe?g|gif|bmp))$~iD', $src, $match)) continue;
            $path = dirname(__DIR__).'/images/'.$match[1];
            if (!is_file($path) || is_link($path) || filesize($path) > 16 * 1048576) continue;
            $xml->startElement('img');
            $xml->writeElement('src', $src);
            $xml->writeElement('base64', base64_encode(file_get_contents($path)));
            $xml->endElement();
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
        $imported = [];
        foreach ($xml->item as $item) {
            $title = trim((string)$item->title);
            if ($title === '' || mb_strlen($title) > 50) throw new DomainException('题目标题无效。');
            // 同名题会跳过；重新导入已经成功的部分不会覆盖已有题。
            if (Problem::byTitle($title)) continue;
            $time = (float)$item->time_limit * (strtolower((string)$item->time_limit['unit']) === 's' ? 1000 : 1);
            $memory = (float)$item->memory_limit / (strtolower((string)$item->memory_limit['unit']) === 'kb' ? 1024 : 1);
            $count = count($item->test_input);
            if ($time <= 0 || $time > 30000 || $memory <= 0 || $memory > 2048 || $count > 4096 || $count !== count($item->test_output)) {
                throw new DomainException('资源限制无效，或测试输入输出未配对。');
            }
            $detail = (string)$item->description;
            foreach (['input'=>'输入格式','output'=>'输出格式','sample_input'=>'样例输入',
                'sample_output'=>'样例输出','hint'=>'提示','source'=>'来源'] as $tag=>$heading) {
                $text = (string)$item->$tag;
                if ($text === '' || ((string)$item->description['cogs_detail'] === 'full' && str_starts_with($tag, 'sample_'))) continue;
                $detail .= '<h3>【'.$heading.'】</h3>'.(str_starts_with($tag, 'sample_') ? '<pre>'.h($text).'</pre>' : $text);
            }
            $db = Database::connection();
            // 一题一个事务。测试点和图片在文件系统里，需要在失败分支手工清理。
            $db->beginTransaction();
            $created = [];
            $dir = null;
            try {
                $plugin = isset($item->cogs_plugin) ? (string)$item->cogs_plugin : (trim((string)$item->spj) !== '' ? '0' : '1');
                if (!in_array($plugin, ['-1','0','1','2','3','4'], true)) throw new DomainException('无法识别的评测方式。');
                Problem::importTitle($title, (int)ceil($time), (int)ceil($memory), $detail, time(), $author, (int)$plugin);
                $id = (int)$db->lastInsertId();
                $name = (string)$id;
                // 老站可能已经用题号做过目录名或文件名；保留旧数据，为新题另选名称。
                if (is_dir(TestData::directory($name)) || Problem::byFilename($name)) $name = 'fps-'.bin2hex(random_bytes(7));
                $dir = TestData::directory($name, true);
                foreach (['sample_input'=>'sample.in','sample_output'=>'sample.out'] as $tag=>$leaf) {
                    if (isset($item->$tag)) TestData::store($name, $leaf, (string)$item->$tag);
                }
                for ($n=0; $n<$count; $n++) {
                    TestData::store($name, $name.($n+1).'.in', (string)$item->test_input[$n]);
                    TestData::store($name, $name.($n+1).'.ans', (string)$item->test_output[$n]);
                }
                foreach (['spj', 'cogs_checker'] as $tag) {
                    foreach ($item->$tag as $checker) {
                        if (trim((string)$checker) === '') continue;
                        $leaf = (string)$checker['filename'];
                        if ($leaf === '' && $tag === 'spj') {
                            $language = strtolower((string)$checker['language']);
                            $ext = ['c'=>'c','pascal'=>'pas','pas'=>'pas','c++'=>'cpp','cpp'=>'cpp',''=>'cpp'][$language] ?? null;
                            if (!$ext) throw new DomainException('无法识别的特判语言。');
                            $leaf = 'spj.'.$ext;
                        }
                        if (!self::checkerFile($leaf)) throw new DomainException('特判文件名无效。');
                        TestData::store($name, $leaf, (string)$checker);
                    }
                }
                foreach ($item->img as $img) {
                    $bytes = base64_decode((string)$img->base64, true);
                    $info = $bytes !== false ? @getimagesizefromstring($bytes) : false;
                    $ext = [IMAGETYPE_PNG=>'png',IMAGETYPE_JPEG=>'jpg',IMAGETYPE_GIF=>'gif',IMAGETYPE_BMP=>'bmp'][$info[2] ?? 0] ?? null;
                    if (!$ext || (string)$img->src === '') throw new DomainException('题面图片格式错误。');
                    $folder = dirname(__DIR__).'/images';
                    if (!is_dir($folder)) mkdir($folder, 0750);
                    $leaf = 'pimg'.$id.'_'.bin2hex(random_bytes(6)).'.'.$ext;
                    $path = $folder.'/'.$leaf;
                    $created[] = $path;
                    if (file_put_contents($path, $bytes) !== strlen($bytes)) throw new RuntimeException('图片写入失败。');
                    Problem::replaceImage((string)$img->src, '../images/'.$leaf, $id);
                }
                Problem::setData($count, $name, $id);
                $db->commit();
                $imported[] = $id;
            } catch (Throwable $error) {
                // 数据库回滚只撤销本题记录；接下来删除这次生成的目录和图片。
                $db->rollBack();
                if ($dir) {
                    foreach (new FilesystemIterator($dir) as $entry) unlink($entry->getPathname());
                    rmdir($dir);
                }
                foreach ($created as $image) if (is_file($image)) unlink($image);
                throw $error;
            }
        }
        return $imported;
    }
}
