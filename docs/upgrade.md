# 升级 Web 端和评测机

Web 端使用 PDO MySQL。已有数据库执行一次 `docs/migrations/remember-login.sql`。新安装使用 `docs/cojs.sql`。旧的自动登录 Cookie 随此次升级失效，用户重新登录后可重新勾选保存登录。

Web 端与评测机同时升级。分别为 PHP 进程设置相同的 `COGS_GRADER_KEY`，使用至少 32 字节的随机密钥，并保持两端时钟同步。独立部署评测机时保留同级的 `include/grader-protocol.php`。使用 HTTPS 连接评测机，签名请求在 30 秒窗口内可重放。签名只校验调度请求；运行选手程序仍需独立的进程和文件系统隔离。

运行 `php tests/grader-protocol.php` 可检查消息编码、参数校验和签名。
已有数据库还需执行 `docs/migrations/page-comments.sql` 补齐页面评论字段。FPS 导出使用 PHP XMLWriter 扩展。
