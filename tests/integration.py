"""Run the web and worker regressions against two local PHP servers and an isolated MariaDB database."""
import contextlib
import hashlib
import os
from pathlib import Path
import secrets
import shutil
import socket
import subprocess
import sys
import tempfile
import time

source = Path(__file__).resolve().parents[1]
php = os.environ.get('PHP', 'php')
host = os.environ.get('COGS_TEST_HOST', '127.0.0.1')
port = os.environ.get('COGS_TEST_PORT', '3306')
user = os.environ.get('COGS_TEST_USER', 'root')
db = [os.environ.get('MARIADB', 'mariadb'), '--no-defaults', '--protocol=tcp',
      '--host='+host, '--port='+port, '--user='+user]
name = 'cogs_test_' + secrets.token_hex(8)


def sql(statement, database=None):
    return subprocess.run(db + ([database] if database else []), input=statement.encode(), check=True,
                          stdout=subprocess.PIPE).stdout


def serve(directory, env, log, sessions):
    with socket.socket() as listener:
        listener.bind(('127.0.0.1', 0))
        port = listener.getsockname()[1]
    address = f'127.0.0.1:{port}'
    process = subprocess.Popen([php, '-d', 'display_errors=0', '-d', 'log_errors=1', '-d',
                                'session.save_path='+str(sessions), '-S', address, '-t', str(directory)],
                               env=env, stdout=log, stderr=log)
    try:
        for _ in range(100):
            if process.poll() is not None:
                raise RuntimeError('PHP 服务启动失败')
            try:
                with socket.create_connection(('127.0.0.1', port), timeout=.15):
                    break
            except OSError:
                time.sleep(.05)
        else:
            raise RuntimeError('PHP 服务未响应')
        yield 'http://'+address
    finally:
        process.terminate()
        process.wait(timeout=10)


with tempfile.TemporaryDirectory(prefix='cogs-') as temp, contextlib.ExitStack() as stack:
    root = Path(temp)
    site = root / 'site'
    shutil.copytree(source, site, ignore=shutil.ignore_patterns('.git','env.inc.php','__pycache__','*.pyc'))
    for part in ('sessions','work','data','source','comp','backup'):
        (root / part).mkdir(mode=0o700)
    env = dict(os.environ, COGS_TEST_ROOT=temp, COGS_TEST_DB_HOST=f'{host};port={port}', COGS_TEST_DB_NAME=name,
               COGS_TEST_DB_USER=user, COGS_TEST_DB_PASSWORD=os.environ.get('MYSQL_PWD',''),
               COGS_GRADER_KEY=secrets.token_hex(32))
    (site/'include/env.inc.php').write_text("""<?php
$cfg=['data_server'=>getenv('COGS_TEST_DB_HOST'),'data_database'=>getenv('COGS_TEST_DB_NAME'),
'data_uid'=>getenv('COGS_TEST_DB_USER'),'data_pwd'=>getenv('COGS_TEST_DB_PASSWORD'),'dir_root'=>'',
'testdata'=>getenv('COGS_TEST_ROOT').'/data'];
""")
    (site/'grader/settings.php').write_text(f'test\n{root}/work\n{root}/data\n')
    sql('CREATE DATABASE '+name)
    stack.callback(sql, 'DROP DATABASE '+name)
    sql((source/'docs/cojs.sql').read_text(), name)
    # Optionally exercise the real legacy dump without rewriting the source data in place.
    if os.environ.get('COGS_TEST_LEGACY_SCHEMA'):
        legacy = name + '_old'
        sql('CREATE DATABASE '+legacy)
        stack.callback(sql, 'DROP DATABASE '+legacy)
        sql(Path(os.environ['COGS_TEST_LEGACY_SCHEMA']).read_text(), legacy)
        original = sql('SELECT uid, usr, pwdhash FROM userinfo ORDER BY uid', legacy)
        # Model a site that customized the old registration setting and chose its own page size.
        sql("""INSERT INTO settings (name, value) VALUES ('reg_readfroce', '2'), ('style_pagesize', '17')
               ON DUPLICATE KEY UPDATE value=VALUES(value)""", legacy)
        def setting_values(database):
            # HEX keeps tabs and multiline HTML in old settings from splitting a result row.
            rows = sql('SELECT name, HEX(value) FROM settings', database).splitlines()[1:]
            return dict(row.split(b'\t', 1) for row in rows)
        before = setting_values(legacy)
        defaults = setting_values(name)
        for migration in ('remember-login.sql','page-comments.sql','passwords.sql','transactional-tables.sql',
                          'settings-defaults.sql'):
            sql((source/'docs/migrations'/migration).read_text(), legacy)
        assert original == sql('SELECT uid, usr, pwdhash FROM userinfo ORDER BY uid', legacy)
        assert b'InnoDB' in sql("SHOW TABLE STATUS LIKE 'submit'", legacy)
        assert b'varchar(255)' in sql("SHOW COLUMNS FROM userinfo LIKE 'pwdhash'", legacy)
        expected = defaults | before
        expected[b'reg_readforce'] = before.get(b'reg_readforce', before[b'reg_readfroce'])
        assert setting_values(legacy) == expected, '升级后的设置缺失或自定义值被覆盖'

        # Re-running after a settings edit must preserve both the correct spelling and its row ID.
        sql("UPDATE settings SET value='0' WHERE name IN ('reg_readforce', 'index_problem_size')", legacy)
        snapshot = 'SELECT ssid, name, HEX(value) FROM settings ORDER BY ssid'
        after_edit = sql(snapshot, legacy)
        sql((source/'docs/migrations/settings-defaults.sql').read_text(), legacy)
        assert sql(snapshot, legacy) == after_edit, '重跑迁移改动了已有设置'
        print('旧库迁移通过，配置补齐且重复执行保留自定义值、账号和密码哈希', flush=True)
    # Fixtures include legacy hashes to exercise the real upgrade-on-login path.
    fixture_password = 'local-test-password'
    old_hash = hashlib.md5((hashlib.sha1(fixture_password.encode()).hexdigest() + 'hasyzxcmykrgb123').encode()).hexdigest()
    env['COGS_TEST_LOGIN_PASSWORD'] = fixture_password
    sql(f"""UPDATE userinfo SET pwdhash='{old_hash}' WHERE uid=1;
        INSERT INTO userinfo(usr,nickname,realname,pwdhash,regtime,gbelong,user_style)
        VALUES('student','student','student','{old_hash}',1,1,'');""", name)
    def run(script):
        executable = sys.executable if script.endswith('.py') else php
        subprocess.run([executable, str(site/'tests'/script)], env=env, cwd=site, check=True)
    logs = []
    for port_key in ('COGS_TEST_WEB_URL','COGS_TEST_GRADER_URL'):
        log = stack.enter_context((root/(port_key+'.log')).open('w+'))
        logs.append(log)
        env[port_key] = stack.enter_context(contextlib.contextmanager(serve)(site, env, log, root/'sessions'))
    env['COGS_TEST_GRADER_URL'] += '/grader/'
    for key,directory in [('dir_source','source'),('dir_competition','comp'),('dir_databackup','backup')]:
        path = str(root / directory).replace("'", "''")
        sql(f"UPDATE settings SET value='{path}/' WHERE name='{key}'", name)
    sql("UPDATE grader SET address='%s' WHERE grid=1" % env['COGS_TEST_GRADER_URL'], name)
    try:
        for script in ('architecture.php','sql-binding.php','grader-protocol.php','grader-toolchain.php','sources.php','web.py','judging.py'):
            run(script)
        if os.environ.get('COGS_TEST_BROWSER'):
            run('browser.py')
    finally:
        for log in logs:
            log.seek(0)
            errors = [line.strip() for line in log if 'Fatal error' in line or 'Uncaught' in line or 'Parse error' in line]
            if errors: raise RuntimeError('\n'.join(errors[:8]))
