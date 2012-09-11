
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- 数据库: `cojs`
--

-- --------------------------------------------------------

--
-- 表的结构 `category`
--

CREATE TABLE IF NOT EXISTS `category` (
  `caid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cname` varchar(24) NOT NULL DEFAULT '',
  `memo` text,
  PRIMARY KEY (`caid`),
  UNIQUE KEY `cname` (`cname`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `comments`
--

CREATE TABLE IF NOT EXISTS `comments` (
  `cid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT '0',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `detail` text,
  `stime` int(11) DEFAULT '0',
  `showcode` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`cid`),
  KEY `pid` (`pid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `compbase`
--

CREATE TABLE IF NOT EXISTS `compbase` (
  `cbid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cname` varchar(24) NOT NULL DEFAULT '',
  `contains` text,
  `ouid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`cbid`),
  UNIQUE KEY `cname` (`cname`),
  KEY `ouid` (`ouid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `compscore`
--

CREATE TABLE IF NOT EXISTS `compscore` (
  `csid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ctid` int(10) unsigned NOT NULL DEFAULT '0',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `pid` int(10) unsigned NOT NULL DEFAULT '0',
  `subtime` int(11) DEFAULT '0',
  `lang` int(11) DEFAULT '0',
  `score` int(11) DEFAULT '0',
  `result` text,
  `memory` int(11) NOT NULL DEFAULT '0',
  `runtime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`csid`),
  KEY `ctid` (`ctid`),
  KEY `uid` (`uid`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `comptime`
--

CREATE TABLE IF NOT EXISTS `comptime` (
  `ctid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cbid` int(10) unsigned NOT NULL DEFAULT '0',
  `intro` text,
  `starttime` int(10) unsigned NOT NULL DEFAULT '0',
  `endtime` int(10) unsigned NOT NULL DEFAULT '0',
  `showscore` int(10) unsigned NOT NULL DEFAULT '0',
  `readforce` int(10) unsigned NOT NULL DEFAULT '0',
  `group` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ctid`),
  KEY `cbid` (`cbid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `grader`
--

CREATE TABLE IF NOT EXISTS `grader` (
  `grid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `address` text NOT NULL,
  `enabled` tinyint(4) NOT NULL DEFAULT '1',
  `priority` int(11) NOT NULL DEFAULT '1',
  `memo` text,
  PRIMARY KEY (`grid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `grader`
--

INSERT INTO `grader` (`grid`, `address`, `enabled`, `priority`, `memo`) VALUES
(1, 'grader', 1, 0, '默认自带的评测机');

-- --------------------------------------------------------

--
-- 表的结构 `groups`
--

CREATE TABLE IF NOT EXISTS `groups` (
  `gid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gname` varchar(24) NOT NULL DEFAULT '',
  `memo` text,
  `adminuid` int(11) NOT NULL,
  `parent` int(11) NOT NULL,
  PRIMARY KEY (`gid`),
  UNIQUE KEY `gname` (`gname`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `login`
--

CREATE TABLE IF NOT EXISTS `login` (
  `lid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `ua` text NOT NULL,
  `ip` varchar(20) NOT NULL,
  `ltime` datetime NOT NULL,
  `version` varchar(20) NOT NULL,
  PRIMARY KEY (`lid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `mail`
--

CREATE TABLE IF NOT EXISTS `mail` (
  `mid` int(10) NOT NULL AUTO_INCREMENT,
  `fromid` int(10) NOT NULL,
  `toid` int(10) NOT NULL,
  `time` int(11) NOT NULL,
  `readed` int(2) NOT NULL,
  `title` varchar(200) CHARACTER SET utf8 NOT NULL,
  `msg` text CHARACTER SET utf8,
  PRIMARY KEY (`mid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='站内邮件';

-- --------------------------------------------------------

--
-- 表的结构 `page`
--

CREATE TABLE IF NOT EXISTS `page` (
  `aid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `etime` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `force` int(2) NOT NULL DEFAULT '0',
  `group` int(11) NOT NULL DEFAULT '0',
  `text` text NOT NULL,
  PRIMARY KEY (`aid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `privilege`
--

CREATE TABLE IF NOT EXISTS `privilege` (
  `prid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `pri` int(11) NOT NULL,
  `def` tinyint(1) NOT NULL DEFAULT '0' COMMENT '该权限是否可用',
  PRIMARY KEY (`prid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `problem`
--

CREATE TABLE IF NOT EXISTS `problem` (
  `pid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `probname` varchar(50) NOT NULL DEFAULT '',
  `filename` varchar(24) NOT NULL DEFAULT '',
  `detail` text,
  `readforce` smallint(6) NOT NULL DEFAULT '0',
  `submitable` tinyint(4) NOT NULL DEFAULT '1',
  `lastacid` int(10) unsigned NOT NULL DEFAULT '1',
  `addtime` int(11) DEFAULT '0',
  `addid` int(10) unsigned NOT NULL DEFAULT '1',
  `datacnt` int(11) DEFAULT '0',
  `submitcnt` int(11) DEFAULT '0',
  `acceptcnt` int(11) DEFAULT '0',
  `timelimit` int(11) DEFAULT '1000',
  `difficulty` int(11) DEFAULT '0',
  `memorylimit` int(11) DEFAULT NULL,
  `plugin` int(11) DEFAULT '1',
  `group` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`pid`),
  UNIQUE KEY `probname` (`probname`),
  UNIQUE KEY `filename` (`filename`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `reply`
--

CREATE TABLE IF NOT EXISTS `reply` (
  `rid` int(11) NOT NULL AUTO_INCREMENT,
  `aid` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `content` text NOT NULL,
  `status` int(2) NOT NULL DEFAULT '0',
  `ip` varchar(30) NOT NULL,
  PRIMARY KEY (`rid`),
  KEY `author_id` (`author_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `settings`
--

CREATE TABLE IF NOT EXISTS `settings` (
  `ssid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(24) NOT NULL DEFAULT '',
  `value` text,
  PRIMARY KEY (`ssid`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `settings`
--

INSERT INTO `settings` (`ssid`, `name`, `value`) VALUES
(22, 'dir_competition', '/home/syzx/cogs_data/comp/'),
(20, 'dir_databackup', '/home/syzx/cogs_data/backup/'),
(21, 'dir_source', '/home/syzx/cogs_data/source/'),
(25, 'global_about', '<h2>\r\n	<div style="text-align:center;">\r\n		<img src="../style/cogs.png" alt="" /><br />\r\n	</div>\r\n</h2>\r\n<!--\r\n<p>\r\n	全国青少年信息学奥林匹克竞赛 (National Olympiad in Informatics, 简称 NOI) 是一项面向全国青少年的信息学竞赛和普及活动，旨在向那些在中学阶段学习的青少年普及计算机科学知识；给学校的信息技术教育课程提供动力和新的思路；给那些有才华的学生提供相互交流和学习的机会；通过竞赛和相关的活动培养和选拔优秀的计算机人才。\r\n</p>\r\n-->\r\n<p style="text-align:left;">\r\n	CmYkRgB123 Online Grading System (COGS) 是由 <a href="http://www.byvoid.com/">CmYkRgB123</a> 开发的一款信息学竞赛在线评测系统，基于 LAMP  ( Linux + Apache + MySQL + PHP ) ，为实验中学各 OIer 提供了方便。目前仅限于内网访问。\r\n</p>\r\n<p>\r\n	开发者：<a href="http://www.byvoid.com/">CmYkRgB123</a>\r\n</p>\r\n<p>\r\n	维护：<a href="user/detail.php?uid=524" target="_blank">王者自由</a> \r\n</p>\r\n<p>\r\n	指导老师：<a href="/cogs/user/detail.php?uid=12" target="_blank">常庆卫</a> \r\n</p>\r\n<p>\r\n	<br />\r\n</p>'),
(3, 'global_adminaddress', 'http://www.byvoid.com'),
(2, 'global_adminname', 'CmYkRgB123'),
(29, 'global_bulletin', '顶部公告栏'),
(4, 'global_constructiontime', '<p>1213859886</p>'),
(23, 'global_head', ''),
(26, 'global_help', '<table border="1">\r\n	<tbody>\r\n		<tr>\r\n			<td>\r\n				<li>\r\n					为什么程序在我的电脑上能够正常运行，而在评测系统上不能?\r\n					<ol>\r\n						<li>\r\n							评测系统建立在Linux下，编译器采用Gcc,G++,Free Pascal.评测系统在比较你的输出时默认采用忽略一切无效字符(空格,回车等)的策略。\r\n						</li>\r\n						<li>\r\n							评测系统对你的程序内存的使用进行限制，默认为128MB，同时也对你的程序堆栈的使用进行限制。如果你的程序使用递归多达100,000层(甚至更多)，那么你的程序很可能运行时出错。\r\n						</li>\r\n						<li>\r\n							对于C和C++语言，主函数一定要定义为int main()而不是void main()。如果你的程序运行正常结束，应向系统返回一个整型值0，而不是其他的东西。\r\n						</li>\r\n						<li>\r\n							评测系统和你的电脑使用的内存安排方式可能不同。某些在你的电脑上没有经过初始化，理应为0的变量在评测系统上有可能并不如你所想的那样。\r\n						</li>\r\n						<li>\r\n							Linux对内存的访问控制更为严格，因此在Windows上可能正常运行的无效指针或数组下标访问越界，在评测系统上无法运行。\r\n						</li>\r\n						<li>\r\n							严重的内存泄露的问题很可能会引起系统的保护模块杀死你的进程。因此，凡是使用malloc(或calloc,realloc,new)分配而得的内存空间，请使用free(或delete)完全释放。\r\n						</li>\r\n						<li>\r\n							在极少数情况下，你的程序运行错误(或编译失败)是因为你使用的某些变量与编译系统的变量名或函数名重复(例如:mmap,fork,pipe,exec,system,socket)。对于这种问题，你只好尝试替换某些可能与系统变量名重复的变量名。\r\n						</li>\r\n						<li>\r\n							注意浮点运算，二进制浮点数运算的时候很有可能会造成意想不到的差异。例如a=0.00001+0.000001;\r\n						</li>\r\n						<li>\r\n							如果你会两种以上的语言，不妨将你的代码“翻译”成另一种语言然后提交，或许在翻译的时候你会发现你的程序的错误。如果翻译以后能够正常通过，那么请仔细检查你原来的程序。\r\n						</li>\r\n						<li>\r\n							如果以上都无法解决问题，请与管理员联系。或者联系作者：<a href="http://www.byvoid.com/" target="_blank">CmYkRgB123</a>。\r\n						</li>\r\n					</ol>\r\n				</li>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n</table>'),
(33, 'contest_weight', '3'),
(18, 'global_index', '主页显示的内容'),
(5, 'global_root', 'cogs/'),
(1, 'global_sitename', 'CmYkRgB123 Online Grading System'),
(24, 'global_tail', '底部公告栏'),
(15, 'limit_checker', '0'),
(27, 'limit_memory', '131070'),
(13, 'limit_regallow', '1'),
(14, 'limit_siteopen', '1'),
(17, 'prob_defdifficulty', '1'),
(16, 'prob_deftimelimit', '1000'),
(11, 'reg_defgroup', '1'),
(12, 'reg_readfroce', '1'),
(10, 'reg_eula', '<h1>\r\n	COGS 用户注册许可协议\r\n</h1>\r\n<p>\r\n	我们对注册用户的要求十分简单。\r\n</p>\r\n<p>\r\n	<strong>不得提交有害代码，不得以任何形式对系统进行破坏！</strong>\r\n</p>\r\n<p>\r\n	由于当前维护者水平不足，系统存在大量已知未知Bug，发现Bug请进行反馈，有能力者可参与开发。\r\n</p>\r\n<p>\r\n	利用系统Bug行不良之事，我们将会采取相关措施进行惩罚。\r\n</p>'),
(7, 'style_jumptime', '0.5'),
(8, 'style_pagesize', '30'),
(6, 'style_profile', 'cogs.css'),
(9, 'style_ranksize', '12'),
(28, 'style_single_ranksize', '10'),
(30, 'gravatar_server', 'http://en.gravatar.com/avatar/'),
(31, 'user_style', 'bootstrap'),
(32, 'problem_weight', '3');

-- --------------------------------------------------------

--
-- 表的结构 `submit`
--

CREATE TABLE IF NOT EXISTS `submit` (
  `sid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT '0',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `lang` int(11) DEFAULT '0',
  `result` text,
  `score` int(11) DEFAULT '0',
  `memory` int(11) DEFAULT '0',
  `accepted` int(11) DEFAULT '0',
  `subtime` int(11) DEFAULT '0',
  `IP` varchar(24) NOT NULL,
  `runtime` int(11) NOT NULL DEFAULT '0',
  `srcname` varchar(256) NOT NULL,
  PRIMARY KEY (`sid`),
  KEY `pid` (`pid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `tag`
--

CREATE TABLE IF NOT EXISTS `tag` (
  `tid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT '0',
  `caid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`tid`),
  KEY `pid` (`pid`),
  KEY `caid` (`caid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `userinfo`
--

CREATE TABLE IF NOT EXISTS `userinfo` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `usr` varchar(24) NOT NULL DEFAULT '',
  `pwdhash` char(32) NOT NULL DEFAULT '3b46d8d37a513c4a1f36bfa95aca77d3',
  `pwdtipques` varchar(64) NOT NULL DEFAULT '',
  `pwdtipanshash` char(32) NOT NULL DEFAULT '3b46d8d37a513c4a1f36bfa95aca77d3',
  `nickname` varchar(16) NOT NULL DEFAULT '',
  `readforce` smallint(6) NOT NULL DEFAULT '0',
  `accepted` int(11) NOT NULL DEFAULT '0',
  `memo` text,
  `regtime` int(11) DEFAULT '0',
  `realname` varchar(16) NOT NULL DEFAULT '',
  `style` int(4) NOT NULL DEFAULT '1',
  `gbelong` int(10) unsigned NOT NULL DEFAULT '1',
  `submited` int(11) NOT NULL DEFAULT '0',
  `grade` int(11) NOT NULL,
  `email` varchar(256) NOT NULL DEFAULT '',
  `lastip` varchar(16) NOT NULL,
  `admin` int(4) NOT NULL DEFAULT '0',
  `user_style` varchar(20) NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `usr` (`usr`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
