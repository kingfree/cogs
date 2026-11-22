<?php
require_once("../include/header.php");
if (!有此权限('修改题目')) {
    http_response_code(403);
    exit;
}

$ids = $_POST['in'] ?? '';
if ($ids !== '') {
    if (!is_string($ids) || !preg_match('/^[0-9]+(?:,[0-9]+)*$/D', $ids)) 异常("题号列表无效");
    $params = explode(',', $ids);
    $marks = implode(',', array_fill(0, count($params), '?'));
    $rows = DataAccess::statement("select * from problem where pid in ($marks) order by pid", $params);
} else {
    $rows = DataAccess::statement("select * from problem where pid between ? and ? order by pid",
        [(int)($_POST['start'] ?? 0), (int)($_POST['end'] ?? 0)]);
}

header('Content-Type: application/xml; charset=UTF-8');
if (($_POST['submit'] ?? '') !== 'Export') header('Content-Disposition: attachment; filename="cogs-fps.xml"');
$xml = new XMLWriter();
$xml->openURI('php://output');
$xml->startDocument('1.0', 'UTF-8');
$xml->startElement('fps');
$xml->writeAttribute('version', '1.1');
while ($problem = $rows->fetch(PDO::FETCH_ASSOC)) {
    $xml->startElement('item');
    $xml->writeElement('title', $problem['probname']);
    $xml->writeElement('description', $problem['detail']);
    foreach (['time_limit' => ['timelimit', 'ms'], 'memory_limit' => ['memorylimit', 'mb']] as $tag => [$field, $unit]) {
        $xml->startElement($tag);
        $xml->writeAttribute('unit', $unit);
        $xml->text((string)$problem[$field]);
        $xml->endElement();
    }
    $name = $problem['filename'];
    if (preg_match('/^[a-zA-Z0-9_][a-zA-Z0-9_-]*$/D', $name)) {
        $dir = $cfg['testdata'].'/'.$name.'/';
        foreach (['sample.in' => 'sample_input', 'sample.out' => 'sample_output'] as $file => $tag) {
            if (is_file($dir.$file)) $xml->writeElement($tag, file_get_contents($dir.$file));
        }
        for ($i = 1; $i <= $problem['datacnt']; $i++) {
            foreach (['in' => 'test_input', 'ans' => 'test_output'] as $ext => $tag) {
                $file = $dir.$name.$i.'.'.$ext;
                if (is_file($file)) $xml->writeElement($tag, file_get_contents($file));
            }
        }
        foreach (['spj.c' => 'C', 'spj.cc' => 'C++', 'spj.cpp' => 'C++'] as $file => $language) {
            if (!is_file($dir.$file)) continue;
            $xml->startElement('spj');
            $xml->writeAttribute('language', $language);
            $xml->text(file_get_contents($dir.$file));
            $xml->endElement();
            break;
        }
    }
    $xml->endElement();
    $xml->flush();
}
$xml->endElement();
$xml->endDocument();
