# 升级 Web 端和评测机

先备份数据库和题目、比赛、源码目录。在停写窗口中为旧数据库执行尚未应用的迁移。新库直接导入 `docs/cojs.sql`。

1. `remember-login.sql` 建立长期登录令牌表。
2. `page-comments.sql` 给页面评论增加文章编号。
3. `passwords.sql` 扩大密码和找回答案字段。旧密码在用户成功登录时自动升级。新装的 root 账号需要在主机运行 `php bin/set-password.php root`，妥善保存终端输出的随机密码。
4. `transactional-tables.sql` 把旧 MyISAM 表转换为 InnoDB。批量转换需要额外磁盘空间，耗时取决于数据量。
5. `settings-defaults.sql` 补齐首页题目数、提交数、成绩门槛、排行头像尺寸、登录日志、默认题目内存、站点地图和注册阅读等级。旧站的 `reg_readfroce` 会复制到缺失的 `reg_readforce`。迁移保留已有设置的值和编号，可以重复执行。

在 `include/env.inc.php` 设置数据库和测试数据根目录。将原有图片与公开附件迁入 `images/`、`upload/`，或为它们设置自己的静态文件映射。两个目录需要允许 Web 进程写入。部署时将 PHP 等脚本执行限制在应用代码目录，Nginx 需为这两个静态目录设置 `^~` location。旧站若单独配置了 `problem/testdata` 的静态别名，应和原符号链接一起移除。测试数据现由 `problem/testdata.php` 校验权限并返回。

旧版本随安装创建的 root 账号可能仍在使用公开的初始口令及找回提示。对沿用该账号的站点，升级时运行密码命令重置密码并清空旧的找回问题。

Web 和评测机需要同时升级。旧配置中的 `grader` 使用本机回环地址和站点子路径；独立部署、仅 HTTPS 或非 80 端口的评测机请在管理页填写完整 URL。给两端的 PHP 进程配置同一个至少 32 字节的随机 `COGS_GRADER_KEY`。评测机目录中保留 `grader/`、`include/grader-protocol.php`、`features/Archive.php`；通过 HTTPS 连接，并同步两端时间。评测机以专用非特权账号运行，部署主机需要允许非特权用户、网络和 PID 命名空间。编译器、选手程序和特判程序均通过 Bubblewrap 启动。隔离启动失败会返回评测失败。

评测机通过 `COGS_GRADER_CONFIG` 读取三个独立行：机器名称、可写的工作目录、只读的题目数据目录。两个目录须为已存在的绝对路径。也兼容旧 `grader/settings.php` 三行配置。Apache 已附访问规则，使用 Nginx 的站点需禁止直接读取此文件。工作目录须位于 Web 根目录以外。新状态文件记录任务租约和已完成次数，首次启动时会读取原 mode 和 cnt。若编译器安装在独立前缀，可在评测机设置 `COGS_GRADER_TOOLCHAIN`，该目录会只读挂到隔离环境的 `/opt/toolchain`，可执行文件放在其 `bin/` 下。

交互题的编号为 `-1`。在题目数据目录提供 `interactor.c`、`.cpp` 或 `.pas`。交互器的两个参数分别是输入文件和标准答案文件名，通过标准输入输出与选手程序通信，写出消息后刷新缓冲区；退出码 0 至 100 表示得分百分比。普通 SPJ 的三个参数依次为输入、选手输出、标准答案。CENA 写入 `score.log`，PHP 特判通过 `plugin_compare($input, $output, $answer)` 返回 0 到 1 的分数。
