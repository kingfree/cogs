<?php
// 把提交写到本次评测的目录。选手源代码由编译器原样读取。
// Worker 传入当前租约的 build 目录。成功时留下 program 或逐点的 .out，供 testcase.php 读取。
function compileSubmission(array $request, string $work): array
{
    $source = $request['src'];
    $name = $request['pname'];
    if (file_put_contents($work.'/'.$source, $request['code']) !== strlen($request['code'])) {
        throw new RuntimeException('源文件写入失败。');
    }
    if ((int)$request['language'] === 3) {
        $zip = Archive::open($work.'/'.$source, 16 * 1048576, 4096);
        try {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $file = $zip->getNameIndex($i);
                if (str_ends_with($file, '/')) continue;
                if (!preg_match('/^'.preg_quote($name, '/').'[1-9][0-9]*\.out$/D', $file)) {
                    throw new DomainException('答案包中应为各测试点的输出文件。');
                }
                $data = $zip->getFromIndex($i);
                if ($data === false || file_put_contents($work.'/'.$file, $data) !== strlen($data)) throw new DomainException('答案文件损坏。');
            }
            return ['compilesucc'=>1, 'msg'=>''];
        } finally {
            $zip->close();
        }
    }
    $result = Sandbox::run(Language::compiler($request['language'], $source, 'program'), $work, 20000, 1024);
    return ['compilesucc'=>(int)($result['status'] === 0 && is_file($work.'/program') && !is_link($work.'/program')),
        'msg'=>$result['msg']];
}
