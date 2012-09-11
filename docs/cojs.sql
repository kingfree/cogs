-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=72 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=707 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=156 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5327 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=170 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=21 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6025 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='站内邮件' AUTO_INCREMENT=261 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=25 ;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1071 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=34 ;

--
-- 转存表中的数据 `settings`
--

INSERT INTO `settings` (`ssid`, `name`, `value`) VALUES
(22, 'dir_competition', '/home/syzx/cogs_data/comp/'),
(20, 'dir_databackup', '/home/syzx/cogs_data/backup/'),
(21, 'dir_source', '/home/syzx/cogs_data/source/'),
(25, 'global_about', ''),
(3, 'global_adminaddress', 'http://www.byvoid.com'),
(2, 'global_adminname', 'CmYkRgB123'),
(29, 'global_bulletin', ''),
(4, 'global_constructiontime', '<p>1213859886</p>'),
(23, 'global_head', ''),
(26, 'global_help', ''),
(33, 'contest_weight', '3'),
(18, 'global_index', ''),
(5, 'global_root', 'cogs/'),
(1, 'global_sitename', 'CmYkRgB123 Online Grading System'),
(24, 'global_tail', ''),
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=42042 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1770 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=757 ;

