<?php
// Web 端和评测机共用的键值编码，保持已有评测结果的格式。
function array_encode($fields) {
    $parts = array();
    foreach ($fields as $key => $value) {
        $parts[] = base64_encode((string)$key);
        $parts[] = base64_encode((string)$value);
    }
    return base64_encode(implode('?', $parts));
}

function array_decode($packet) {
    if (!is_string($packet)) return array();
    $decoded = base64_decode($packet, true);
    if ($decoded === false || $decoded === '') return array();
    $parts = explode('?', $decoded);
    if (count($parts) % 2) return array();
    $fields = array();
    for ($i = 0; $i < count($parts); $i += 2) {
        $key = base64_decode($parts[$i], true);
        $value = base64_decode($parts[$i + 1], true);
        if ($key === false || $key === '' || $value === false || isset($fields[$key])) return array();
        $fields[$key] = $value;
    }
    return $fields;
}

function graderRequestAllowed($fields) {
    if (!isset($fields['action'])) return false;
    $action = $fields['action'];
    if (in_array($action, array('state', 'start', 'shutdown', 'lock'), true)) return true;
    if (!isset($fields['uid']) || !preg_match('/^[1-9][0-9]*$/D', $fields['uid'])) return false;
    if ($action === 'unlock') return true;
    if (!isset($fields['pname'], $fields['language']) ||
        !preg_match('/^[A-Za-z0-9_][A-Za-z0-9_-]*$/D', $fields['pname']) ||
        !preg_match('/^[0-3]$/D', $fields['language'])) return false;
    if ($action === 'compile') {
        $extensions = array('pas', 'c', 'cpp', 'zip');
        return isset($fields['src'], $fields['code']) &&
            $fields['src'] === $fields['pname'].'.'.$extensions[$fields['language']];
    }
    if ($action !== 'grade') return false;
    foreach (array('grade', 'memorylimit', 'timelimit', 'plugin') as $key) {
        if (!isset($fields[$key]) || !preg_match('/^[0-9]+$/D', $fields[$key])) return false;
    }
    return true;
}

function graderSignatureValid($form, $key, $now) {
    if (!is_string($key) || strlen($key) < 32 ||
        !isset($form['query'], $form['time'], $form['signature']) ||
        !is_string($form['query']) || !is_string($form['signature']) || !is_string($form['time']) ||
        !preg_match('/^[0-9]{10}$/D', $form['time']) || abs($now - (int)$form['time']) > 30) return false;
    return hash_equals(hash_hmac('sha256', $form['time']."\n".$form['query'], $key), $form['signature']);
}
