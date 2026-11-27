-- 编辑题目及交卷使用行锁与事务。升级前先备份。
ALTER TABLE `category` ENGINE=InnoDB;
ALTER TABLE `comments` ENGINE=InnoDB;
ALTER TABLE `compbase` ENGINE=InnoDB;
ALTER TABLE `compscore` ENGINE=InnoDB;
ALTER TABLE `comptime` ENGINE=InnoDB;
ALTER TABLE `grader` ENGINE=InnoDB;
ALTER TABLE `groups` ENGINE=InnoDB;
ALTER TABLE `login` ENGINE=InnoDB;
ALTER TABLE `mail` ENGINE=InnoDB;
ALTER TABLE `page` ENGINE=InnoDB;
ALTER TABLE `problem` ENGINE=InnoDB;
ALTER TABLE `reply` ENGINE=InnoDB;
ALTER TABLE `settings` ENGINE=InnoDB;
ALTER TABLE `submit` ENGINE=InnoDB;
ALTER TABLE `tag` ENGINE=InnoDB;
ALTER TABLE `userinfo` ENGINE=InnoDB;
