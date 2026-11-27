-- 新装 COGS 的空库。旧库升级时使用 docs/migrations。
-- 安装后在主机终端运行 php bin/set-password.php root。
SET NAMES utf8;

CREATE TABLE IF NOT EXISTS `category` (
  `caid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cname` varchar(24) NOT NULL DEFAULT '',
  `memo` text,
  PRIMARY KEY (`caid`),
  UNIQUE KEY `cname` (`cname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `comments` (
  `cid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `aid` int(10) unsigned NOT NULL DEFAULT 0,
  `pid` int(10) unsigned NOT NULL DEFAULT '0',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `detail` text,
  `stime` int(11) DEFAULT '0',
  `showcode` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`cid`),
  KEY `pid` (`pid`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `compbase` (
  `cbid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cname` varchar(24) NOT NULL DEFAULT '',
  `contains` text,
  `ouid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`cbid`),
  UNIQUE KEY `cname` (`cname`),
  KEY `ouid` (`ouid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `grader` (
  `grid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `address` text NOT NULL,
  `enabled` tinyint(4) NOT NULL DEFAULT '1',
  `priority` int(11) NOT NULL DEFAULT '1',
  `memo` text,
  PRIMARY KEY (`grid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `groups` (
  `gid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gname` varchar(24) NOT NULL DEFAULT '',
  `memo` text,
  `adminuid` int(11) NOT NULL,
  `parent` int(11) NOT NULL,
  PRIMARY KEY (`gid`),
  UNIQUE KEY `gname` (`gname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `login` (
  `lid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `ua` text NOT NULL,
  `ip` varchar(20) NOT NULL,
  `ltime` datetime NOT NULL,
  `version` varchar(20) NOT NULL,
  PRIMARY KEY (`lid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mail` (
  `mid` int(10) NOT NULL AUTO_INCREMENT,
  `fromid` int(10) NOT NULL,
  `toid` int(10) NOT NULL,
  `time` int(11) NOT NULL,
  `readed` int(2) NOT NULL,
  `title` varchar(200) CHARACTER SET utf8 NOT NULL,
  `msg` text CHARACTER SET utf8,
  PRIMARY KEY (`mid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='站内邮件';

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `privilege` (
  `prid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `pri` int(11) NOT NULL,
  `def` tinyint(1) NOT NULL DEFAULT '0' COMMENT '该权限是否可用',
  PRIMARY KEY (`prid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `settings` (
  `ssid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(24) NOT NULL DEFAULT '',
  `value` text,
  PRIMARY KEY (`ssid`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `tag` (
  `tid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT '0',
  `caid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`tid`),
  KEY `pid` (`pid`),
  KEY `caid` (`caid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `userinfo` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `usr` varchar(24) NOT NULL DEFAULT '',
  `pwdhash` varchar(255) NOT NULL DEFAULT '',
  `pwdtipques` varchar(64) NOT NULL DEFAULT '',
  `pwdtipanshash` varchar(255) NOT NULL DEFAULT '',
  `nickname` varchar(16) NOT NULL DEFAULT '',
  `readforce` smallint(6) NOT NULL DEFAULT '0',
  `accepted` int(11) NOT NULL DEFAULT '0',
  `memo` text,
  `regtime` int(11) DEFAULT '0',
  `realname` varchar(16) NOT NULL DEFAULT '',
  `style` int(4) NOT NULL DEFAULT '1',
  `gbelong` int(10) unsigned NOT NULL DEFAULT '1',
  `submited` int(11) NOT NULL DEFAULT '0',
  `grade` int(11) NOT NULL DEFAULT 0,
  `email` varchar(256) NOT NULL DEFAULT '',
  `lastip` varchar(45) NOT NULL DEFAULT '',
  `admin` int(4) NOT NULL DEFAULT '0',
  `user_style` varchar(20) NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `usr` (`usr`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS login_token (
    digest CHAR(64) CHARACTER SET ascii PRIMARY KEY,
    uid INT NOT NULL,
    expires_at BIGINT NOT NULL,
    password_version CHAR(64) CHARACTER SET ascii NOT NULL,
    INDEX (uid), INDEX (expires_at)
) ENGINE=InnoDB;

-- 必需的分组、评测机名称和未设置密码的管理员。
INSERT INTO `groups` (gid, gname, memo, adminuid, parent)
VALUES (1, '注册用户', '', 1, 0);

INSERT INTO grader (grid, address, enabled, priority, memo)
VALUES (1, 'grader', 1, 0, '默认评测机');

INSERT INTO userinfo (uid, usr, nickname, readforce, gbelong, regtime, user_style)
VALUES (1, 'root', '站点管理员', 2, 1, UNIX_TIMESTAMP(), '');

INSERT INTO privilege (prid, uid, pri, def)
VALUES (1, 1, 2, 1);

-- 这些是新站点的通用设置。文件目录位于网页根目录外。
INSERT INTO settings (ssid, name, value) VALUES
(1, 'global_sitename', 'COGS 在线评测系统'),
(2, 'global_adminname', '站点管理员'),
(3, 'global_adminaddress', ''),
(4, 'global_constructiontime', UNIX_TIMESTAMP()),
(5, 'global_root', 'cogs/'),
(6, 'style_profile', 'cogs.css'),
(7, 'style_jumptime', '1'),
(8, 'style_pagesize', '30'),
(9, 'style_ranksize', '12'),
(10, 'reg_eula', '请遵守本站的训练和比赛规则。'),
(11, 'reg_defgroup', '1'),
(12, 'reg_readforce', '1'),
(13, 'limit_regallow', '1'),
(14, 'limit_siteopen', '1'),
(15, 'limit_checker', '0'),
(16, 'prob_deftimelimit', '1000'),
(17, 'prob_defdifficulty', '1'),
(18, 'global_index', ''),
(20, 'dir_databackup', '/var/lib/cogs/backup/'),
(21, 'dir_source', '/var/lib/cogs/source/'),
(22, 'dir_competition', '/var/lib/cogs/competition/'),
(23, 'global_head', ''),
(24, 'global_tail', ''),
(25, 'global_about', 'COGS 提供题库、在线评测和比赛。'),
(26, 'global_help', '从题库选择题目，按题面指定的文件名读写数据并提交程序。'),
(27, 'limit_memory', '131070'),
(28, 'style_single_ranksize', '10'),
(29, 'global_bulletin', ''),
(30, 'gravatar_server', ''),
(31, 'user_style', 'bootstrap'),
(32, 'problem_weight', '3'),
(33, 'contest_weight', '3'),
(34, 'global_map', ''),
(35, 'index_problem_size', '10'),
(36, 'index_rank_icon', '16'),
(37, 'index_submit_score', '0'),
(38, 'index_submit_size', '15'),
(39, 'login_log', '1'),
(40, 'prob_defmemorylimit', '128');
