<?php
/**
 * 网站和评测机共用同一种编码。接收端先验签，再解码和检查操作字段，最后由 Worker 核对任务租约。
 * 例：$packet = array_encode(['action'=>'state']);
 */
require_once dirname(__DIR__).'/grader/Language.php';
require_once dirname(__DIR__).'/grader/GradingLimits.php';

/** 将一个字段表封成报文；字段内容可包含二进制源文件。 */
function array_encode($fields) {
    $parts = [];
    // 先逐个编码键和值，因而即使源码含问号或二进制字节，也不会撞上字段分隔符。
    foreach ($fields as $key => $value) {
        $parts[] = base64_encode((string)$key);
        $parts[] = base64_encode((string)$value);
    }
    // 再包一层 Base64，将整条消息作为旧 HTTP 表单中的一个字段传输。
    return base64_encode(implode('?', $parts));
}

/** 将报文解成字符串键值对；损坏、缺值或重复键均返回空数组。 */
function array_decode($packet) {
    if (!is_string($packet)) return [];
    // 严格解外层，损坏或空报文返回空表，交由接收方判为无有效动作。
    $decoded = base64_decode($packet, true);
    if ($decoded === false || $decoded === '') return [];
    // 外层解码后，每两个字段为一组。要求偶数长度，以免值错配给下一个字段。
    $parts = explode('?', $decoded);
    $length = count($parts);
    if ($length % 2) return []; // 最后一个键缺值时，不能把截断的消息交给评测机。
    $fields = [];
    for ($i = 0; $i < $length; $i += 2) {
        // 每一对按键、值的顺序分别严格解码，包含空字符串的值也是有效字段。
        $key = base64_decode($parts[$i], true);
        $value = base64_decode($parts[$i + 1], true);
        // 一个名称只能出现一次，使校验动作和执行动作读取同一组字段。
        if ($key === false || $key === '' || $value === false || isset($fields[$key])) return [];
        $fields[$key] = $value;
    }
    return $fields;
}

/** 根据操作核验必需字段。编译检查文件名和源码大小，评测检查点号、单位及资源上限。 */
function graderRequestAllowed($fields) {
    $action = $fields['action'] ?? '';
    // 状态查询与开停机都是机器级操作；外部请求仍已用整包签名验证身份。
    if (in_array($action, ['state', 'start', 'shutdown'], true)) return true;
    // 以下动作必须关联到固定长度的租约号；从 lock 往后，工作目录仅按这个号定位。
    if (!preg_match('/^[a-f0-9]{32}$/D', $fields['job'] ?? '')) return false;
    if ($action === 'lock') return true;
    // 编译、单点和解锁沿用旧协议的正整数用户编号。解锁不传题名或源码。
    if (!preg_match('/^[1-9][0-9]*$/D', $fields['uid'] ?? '')) return false;
    if ($action === 'unlock') return true;

    // 只有存在的语言编号才能推导源文件后缀，题名也只能由协议允许的文件名字符组成。
    $name = $fields['pname'] ?? '';
    $language = $fields['language'] ?? '';
    if (!preg_match('/^[A-Za-z0-9_][A-Za-z0-9_-]*$/D', $name) || !isset(Language::TYPES[$language])) return false;
    // 通过题名和语言检查后，才分别检查编译请求与单点评测的字段。
    if ($action === 'compile') {
        // 题名与语言共同决定文件名，例如 sum.cpp。拒绝覆盖其他文件名或超过上限的提交。
        $source = $name.'.'.Language::TYPES[$language][0];
        return ($fields['src'] ?? null) === $source && isset($fields['code']) &&
            strlen($fields['code']) <= GradingLimits::SOURCE_BYTES;
    }
    if ($action !== 'grade') return false;

    // 线上报文里的值都是字符串。先检查十进制整数，避免接受小数、符号或指数写法。
    $maximum = ['grade'=>GradingLimits::CASES, 'timelimit'=>GradingLimits::TIME_MS, 'memorylimit'=>GradingLimits::MEMORY_MIB];
    foreach ($maximum as $field => $limit) {
        $value = $fields[$field] ?? '';
        // 第 0 点以及为 0 的时间/内存额度没有有效评测含义。
        if (!preg_match('/^[0-9]+$/D', $value) || $value < 1 || $value > $limit) return false;
    }
    // 对每点仅接受既有的比较、答案、特判以及交互模式；未知编号不能传给执行端。
    return in_array($fields['plugin'] ?? null, ['-1','0','1','2','3','4'], true);
}

/** 签名覆盖传输时的原始字节。时间在前、报文在后，中间固定一个换行；两端共用同一编码约定。 */
function graderSignature(string $timestamp, string $packet, string $key): string
{
    return hash_hmac('sha256', $timestamp."\n".$packet, $key);
}

/** 核对共享密钥、30 秒时差和消息认证码。签名覆盖原始 query 以及发送时刻。 */
function graderSignatureValid($form, $key, $now) {
    // 逐项核对密钥、时间戳格式和窗口，签名不能替过期消息续期。
    if (!is_string($key) || strlen($key) < 32 ||
        !isset($form['query'], $form['time'], $form['signature']) ||
        !is_string($form['query']) || !is_string($form['signature']) || !is_string($form['time']) ||
        !preg_match('/^[0-9]{10}$/D', $form['time']) || abs($now - (int)$form['time']) > 30) return false;
    // 对原始报文字节重算签名；使用固定时间比较后才允许解码任务字段。
    return hash_equals(graderSignature($form['time'], $form['query'], $key), $form['signature']);
}
