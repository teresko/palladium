

CREATE TABLE `identities` (
    `identity_id`           SERIAL PRIMARY KEY,
    `parent_id`             BIGINT UNSIGNED NULL DEFAULT NULL COMMENT '',
    `account_id`            BIGINT UNSIGNED NULL DEFAULT NULL,

    `type`                  INT(1) UNSIGNED NOT NULL,
    `identifier`            TEXT NOT NULL COMMENT 'this is where email or access token goes',
    `fingerprint`           CHAR(96) NOT NULL COMMENT 'stores SHA-384 of identifier',
    `status`                INT(1) UNSIGNED NOT NULL DEFAULT 0,

    `hash`                  TEXT NULL DEFAULT NULL COMMENT 'stores password hash or access token hash',

    `created_on`            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `used_on`               TIMESTAMP NULL DEFAULT NULL,
    `expires_on`            TIMESTAMP NULL DEFAULT NULL,

    `token`                 CHAR(32) NULL DEFAULT NULL COMMENT 'stores hex of 16 random bytes',
    `token_expires_on`      TIMESTAMP NULL DEFAULT NULL,
    `token_action`          VARCHAR(15) NULL DEFAULT NULL,

    KEY (`parent_id`),
    KEY (`account_id`),
    KEY (`type`),
    KEY (`status`),
    KEY (`fingerprint`),
    KEY (`expires_on`),
    UNIQUE KEY (`token`),
    KEY (`token_expires_on`),
    KEY (`token_action`),

    FOREIGN KEY `parentIdentity` (`parent_id`) REFERENCES `identities`(`identity_id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE

    FOREIGN KEY `associatedAccount` (`account_id`) REFERENCES `users`(`account_id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
