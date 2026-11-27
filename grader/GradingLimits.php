<?php
/**
 * Web 接收题目和源文件、评测机接收任务时共用的硬上限。
 * 例：用 $bytes <= GradingLimits::SOURCE_BYTES 判断上传是否超出协议上限。题目本身可指定更低的限制。
 */
final class GradingLimits
{
    // 点号从 1 开始；题包导入和在线建题也使用同一上界。
    public const CASES = 4096;
    // 单个测试点的墙钟时限。编译器的运行预算另由编译阶段给出。
    public const TIME_MS = 30000;
    // 请求以 MiB 表示地址空间，Sandbox 启动时换算成 prlimit 的字节数。
    public const MEMORY_MIB = 2048;
    // 比较解码后的源文件/答案 ZIP 大小；传输用的 Base64 本身会更长。
    public const SOURCE_BYTES = 102400;
}
