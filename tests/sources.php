<?php
if (PHP_SAPI !== 'cli') exit;
require dirname(__DIR__).'/features/ProblemSource.php';
require dirname(__DIR__).'/include/forms.php';
require dirname(__DIR__).'/include/rich-text.php';
foreach (['poj'=>'<div class="ptt">Example</div><div class="ptx">section</div>',
    'pojgrids'=>'<div id="pageTitle">Example</div><dl class="problem-content"><dd>section</dd></dl>',
    'soj'=>'<div class="cent">1001. Example</div><div class="description">section</div>',
    'lydsy'=>'<h2>1001. Example</h2><div class="content">section</div>',
    'uva'=>'<h1>Example</h1><div class="content">section</div>'] as $site=>$html) {
    $draft = ProblemSource::draft($site, 1001, $html);
    if ($draft['probname'] !== 'Example' || !str_contains($draft['detail'], 'section')) throw new RuntimeException($site);
}
echo "5 种外站题面格式检查通过\n";
