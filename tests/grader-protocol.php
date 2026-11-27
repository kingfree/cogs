<?php
// 在仓库根目录执行 php tests/grader-protocol.php，检查评测消息编解码、签名和有效期。
require dirname(__DIR__).'/include/grader-protocol.php';
$checks = 0;
function check($condition, $name) {
    global $checks;
    $checks++;
    if (!$condition) throw new RuntimeException($name);
}
$key = str_repeat('k', 32);
$now = 1700000000;
$fields = array('action'=>'compile', 'uid'=>'42', 'job'=>str_repeat('a', 32), 'pname'=>'sum', 'language'=>'2', 'src'=>'sum.cpp', 'code'=>"中文\0?+");
$packet = array_encode($fields);
check(array_decode($packet) === $fields, '评测协议往返');
check(graderRequestAllowed($fields), '接受合法编译请求');
$grade = array_replace($fields, ['action'=>'grade', 'grade'=>'4096', 'memorylimit'=>'2048', 'timelimit'=>'30000', 'plugin'=>'-1']);
check(graderRequestAllowed($grade), '允许边界值和交互题');
foreach (['grade'=>'0', 'timelimit'=>'30001', 'memorylimit'=>'2049', 'plugin'=>'5', 'language'=>'02'] as $keyName=>$invalid) {
    check(!graderRequestAllowed(array_replace($grade, [$keyName=>$invalid])), '拒绝无效字段 '.$keyName);
}
check(!graderRequestAllowed(array_replace($fields, ['src'=>'sum.c'])), '拒绝语言与源码扩展名不一致');
$fields['uid'] = '0';
check(!graderRequestAllowed($fields), '拒绝无效用户编号');
check(array_decode('invalid') === array(), '拒绝损坏的编码');
$form = array('time'=>(string)$now, 'query'=>$packet, 'signature'=>hash_hmac('sha256', $now."\n".$packet, $key));
check(graderSignatureValid($form, $key, $now), '验证签名');
check(!graderSignatureValid($form, $key, $now+31), '拒绝过期请求');
check(!graderSignatureValid($form, '', $now), '拒绝未配置密钥');
$form['query'] = '';
check(!graderSignatureValid($form, $key, $now), '拒绝修改过的请求');
echo "$checks 项评测协议检查通过\n";
