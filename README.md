# COGS 在线评测系统

COGS 提供题库、文件输入输出评测、比赛和成绩单、用户分组、讨论与站内信。网页和评测机可以分开部署。

| 要改的功能 | 从这里开始 |
| --- | --- |
| 题目、分类和测试数据 | `features/Problem.php`、`Category.php`、`TestData.php` |
| 编辑器上传的图片、媒体和文档 | `features/Attachment.php` |
| FPS 题包和外站题面 | `features/Fps.php`、`ProblemSource.php`。外站解析规则在 `problem-sources/`，每站一个文件 |
| 比赛题单、场次和交卷 | `features/Contest.php`、`ContestRound.php`、`ContestEntry.php` |
| 普通提交、选机和计分 | `features/Submission.php`、`Judging.php`、`Ranking.php` |
| 编译、单测试点和特判 | `grader/compiler.php`、`testcase.php`、`checker.php`。资源与文件隔离在 `Sandbox.php` |
| 账号、登录、权限、分组 | `features/Account.php`、`Login.php`、`Privilege.php`、`UserGroup.php` |
| 文章、讨论、私信、备份 | `features/Page.php`、`Comment.php`、`Message.php`、`Backup.php` |

原有 `problem/`、`contest/`、`submit/`、`user/` 等目录保留网页地址。入口读取会话并校验表单，再调用对应主体。例如交卷从 `contest/submit.php` 进入，由服务端读取场次时间和题单，文件与提交记录由 `ContestEntry::submitSource()` 保存。SQL 的实际值一律使用 PDO 参数；排序字段和可选条件由业务主体定义。

## 运行

Web 端使用 PHP 8.1 或更高版本、PDO MySQL、DOM、SimpleXML、XMLWriter、mbstring、GD、ZIP 和 MariaDB。复制 `include/default.env.inc.php` 到 `include/env.inc.php`，填写数据库账号、站点子路径和数据目录。导入 `docs/cojs.sql`，执行 `php bin/set-password.php root` 设置首个管理员密码，然后设置备份、普通提交与比赛源码目录。沿用原站点的 Bootstrap、jQuery、KindEditor、代码高亮和 MathJax 静态资源映射。

Linux 评测机使用 util-linux（unshare、prlimit）、Bubblewrap、GNU time、coreutils、GCC、G++ 和 Free Pascal；PHP 特判还需要 PHP CLI。管理页的数据库备份需要 mariadb-dump，文件备份需要 tar。第一次部署和老站升级的目录、密钥及数据库操作见 [升级说明](docs/upgrade.md)。

## 验证

`php tests/architecture.php` 检查 PHP 语法、业务入口中的 SQL 和业务主体公开方法的 PHPDoc。`python3 tests/integration.py` 在本地 MariaDB 创建一个随机命名的临时库，启动独立 Web 和评测进程，执行 HTTP 与编译器回归，结束后删除该临时库。连接参数为 `COGS_TEST_HOST`、`COGS_TEST_PORT`、`COGS_TEST_USER` 和 `MYSQL_PWD`；测试账号需要临时建库权限。设置 `PHP`、`MARIADB` 可以指定可执行文件。若提供 `COGS_TEST_LEGACY_SCHEMA`（原版 SQL 文件的路径），还会在另一临时库复测旧库升级。已安装 Playwright 时，设置 `COGS_TEST_BROWSER` 为 Chromium 可执行文件路径，可同时运行浏览器用例。
