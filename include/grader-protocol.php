<?php
/**
 * 网站和评测机共用同一种编码。接收端先验签，再解码和检查操作字段，最后由 Worker 核对任务租约。
 * 例：$packet = array_encode(['action'=>'state']);
 */
require_once dirname(__DIR__).'/grader/Language.php';
// Web 端和评测机共用的键值编码，保持已有评测结果的格式。
/** 将一个字段表封成报文；字段内容可包含二进制源文件。 */
function array_encode($fields) {
    $parts = array();
    foreach ($fields as $key => $value) {
        $parts[] = base64_encode((string)$key);
        $parts[] = base64_encode((string)$value);
    }
    return base64_encode(implode('?', $parts));
}

/** 将报文解成字符串键值对；损坏、缺值或重复键均返回空数组。 */
function array_decode($packet) {
    if (!is_string($packet)) return array();
    $decoded = base64_decode($packet, true);
    if ($decoded === false || $decoded === '') return array();
    // 外层解码后，每两个字段为一组。要求偶数长度，以免值错配给下一个字段。
    $parts = explode('?', $decoded);
    if (count($parts) % 2) return array();
    $fields = array();
    for ($i = 0; $i < count($parts); $i += 2) {
        $key = base64_decode($parts[$i], true);
        $value = base64_decode($parts[$i + 1], true);
        // 一个名称只能出现一次，使校验动作和执行动作读取同一组字段。
        if ($key === false || $key === '' || $value === false || isset($fields[$key])) return array();
        $fields[$key] = $value;
    }
    return $fields;
}

/** 根据操作核验必需字段。编译检查文件名和源码大小，评测检查点号、单位及资源上限。 */
function graderRequestAllowed($fields) {
    if (!isset($fields['action'])) return false;
    $action = $fields['action'];
    if (in_array($action, array('state', 'start', 'shutdown'), true)) return true;
    if (!preg_match('/^[a-f0-9]{32}$/D', $fields['job'] ?? '')) return false;
    if ($action === 'lock') return true;
    if (!isset($fields['uid']) || !preg_match('/^[1-9][0-9]*$/D', $fields['uid'])) return false;
    if ($action === 'unlock') return true;
    if (!isset($fields['pname'], $fields['language']) ||
        !preg_match('/^[A-Za-z0-9_][A-Za-z0-9_-]*$/D', $fields['pname']) ||
        !preg_match('/^[0-3]$/D', $fields['language'])) return false;
    // 源文件名由题目的英文名和语言编号推导，与 Web 端及评测机的编译命令一致。
    if ($action === 'compile') {
        $extensions = array_column(Language::TYPES, 0);
        return isset($fields['src'], $fields['code']) && strlen($fields['code']) <= 102400 &&
            $fields['src'] === $fields['pname'].'.'.$extensions[$fields['language']];
    }
    if ($action !== 'grade') return false;
    foreach (array('grade', 'memorylimit', 'timelimit') as $key) {
        if (!isset($fields[$key]) || !preg_match('/^[0-9]+$/D', $fields[$key])) return false;
    }
    return $fields['grade'] >= 1 && $fields['grade'] <= 4096 &&
        $fields['timelimit'] >= 1 && $fields['timelimit'] <= 30000 &&
        $fields['memorylimit'] >= 1 && $fields['memorylimit'] <= 2048 &&
        in_array($fields['plugin'] ?? null, ['-1','0','1','2','3','4'], true);
}

/** 核对共享密钥、30 秒时差和消息认证码。签名覆盖原始 query 以及发送时刻。 */
function graderSignatureValid($form, $key, $now) {
    if (!is_string($key) || strlen($key) < 32 ||
        !isset($form['query'], $form['time'], $form['signature']) ||
        !is_string($form['query']) || !is_string($form['signature']) || !is_string($form['time']) ||
        !preg_match('/^[0-9]{10}$/D', $form['time']) || abs($now - (int)$form['time']) > 30) return false;
    return hash_equals(hash_hmac('sha256', $form['time']."\n".$form['query'], $key), $form['signature']);
}
