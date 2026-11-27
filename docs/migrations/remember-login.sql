CREATE TABLE IF NOT EXISTS login_token (
    digest CHAR(64) CHARACTER SET ascii PRIMARY KEY,
    uid INT NOT NULL,
    expires_at BIGINT NOT NULL,
    password_version CHAR(64) CHARACTER SET ascii NOT NULL,
    INDEX (uid), INDEX (expires_at)
) ENGINE=InnoDB;
