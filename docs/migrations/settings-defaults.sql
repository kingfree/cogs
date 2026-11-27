-- 在旧站所用的数据库执行。按名称补值，由数据库分配 ssid，保留管理页引用的原有编号。
START TRANSACTION;

-- 旧安装将 reg_readforce 拼成 reg_readfroce。新旧键同时存在时，以站长已设置的新键为准。
INSERT INTO settings (name, value)
SELECT 'reg_readforce', legacy.value
FROM settings AS legacy
WHERE legacy.name = 'reg_readfroce'
  AND NOT EXISTS (SELECT 1 FROM settings WHERE name = 'reg_readforce');

INSERT INTO settings (name, value)
SELECT defaults.name, defaults.value
FROM (
    SELECT 'reg_readforce' AS name, '1' AS value
    UNION ALL SELECT 'global_map', ''
    UNION ALL SELECT 'index_problem_size', '10'
    UNION ALL SELECT 'index_rank_icon', '16'
    UNION ALL SELECT 'index_submit_score', '0'
    UNION ALL SELECT 'index_submit_size', '15'
    UNION ALL SELECT 'login_log', '1'
    UNION ALL SELECT 'prob_defmemorylimit', '128'
) AS defaults
WHERE NOT EXISTS (SELECT 1 FROM settings AS existing WHERE existing.name = defaults.name);

COMMIT;
