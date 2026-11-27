-- 早期数据库缺少页面评论使用的 aid 字段。已有此字段时跳过。
SET @add_aid = IF(EXISTS(SELECT 1 FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='comments' AND column_name='aid'),
    'SELECT 1', 'ALTER TABLE comments ADD aid INT UNSIGNED NOT NULL DEFAULT 0');
PREPARE migration FROM @add_aid;
EXECUTE migration;
DEALLOCATE PREPARE migration;
