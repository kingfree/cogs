<?php
// 把提交写到本次评测的目录。选手源代码由编译器原样读取。
// Worker 传入当前租约的 build 目录。成功时留下 program 或逐点的 .out，供 testcase.php 读取。
// 例：在 Worker 已锁定任务后调用 compileSubmission($request, $build)（$build 是当前任务的编译目录），按 compilesucc 决定是否继续逐点评测。
function compileSubmission(array $request, string $work): array
{
    // 协议已核对题名、语言和文件名。按原始字节保存，可同时处理文本源码与 ZIP。
    $source = $request['src'];
    $name = $request['pname'];
    // 短写属于工作盘故障，不能报成选手的编译错误。
    if (file_put_contents($work.'/'.$source, $request['code']) !== strlen($request['code'])) {
        throw new RuntimeException('源文件写入失败。');
    }
    if ((int)$request['language'] === 3) {
        // 答案题无需启动编译器。先检查解压后总大小和条目数，再读取单个文件。
        $zip = null;
        try {
            $zip = Archive::open($work.'/'.$source, 16 * 1048576, 4096); // 限制解压后的字节和成员数，再读取成员内容。
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $file = $zip->getNameIndex($i);
                if (str_ends_with($file, '/')) continue; // ZIP 目录项没有答案字节，跳过后再核对文件名。
                // 只接受当前题的编号输出，例如 sum1.out；带路径的条目不能写到 build 之外。
                if (!preg_match('/^'.preg_quote($name, '/').'[1-9][0-9]*\.out$/D', $file)) {
                    throw new DomainException('答案包中应为各测试点的输出文件。');
                }
                // 解码失败说明选手需要重新打包；工作盘写入失败则交给评测机报告故障。
                $data = $zip->getFromIndex($i);
                if ($data === false) throw new DomainException('答案文件损坏。');
                // 磁盘没有完整保存这个测试点时，后续评测不应给出成绩。
                if (file_put_contents($work.'/'.$file, $data) !== strlen($data)) throw new RuntimeException('答案文件写入失败。');
            }
            return ['compilesucc'=>1, 'msg'=>''];
        } catch (DomainException $error) {
            // 无效压缩包与语法错误一样结束这次提交，网页可把具体原因显示给选手。
            return ['compilesucc'=>0, 'msg'=>$error->getMessage()];
        } finally {
            $zip?->close();
        }
    }
    // 普通题在租约的 build 目录编译。后续测试点只拿编译产物，不需要再见到源码。
    $result = Sandbox::run(Language::compiler($request['language'], $source, 'program'), $work, 20000, 1024);
    // 退出码正常且留下普通文件才算成功；诊断原样随结果返回给网页。
    return ['compilesucc'=>(int)($result['status'] === 0 && is_file($work.'/program') && !is_link($work.'/program')),
        'msg'=>$result['msg']];
}
