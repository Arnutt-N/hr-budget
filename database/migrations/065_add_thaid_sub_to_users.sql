-- 065_add_thaid_sub_to_users.sql
-- Link column for ThaID (DOPA) OAuth2 accounts.
-- thaid_sub = the stable ThaID subject (PID / OIDC `sub`) and is the canonical
-- identity key for ThaID-provisioned users. Nullable so existing email/password
-- accounts are unaffected; UNIQUE so one ThaID identity maps to one user.
-- (A UNIQUE index permits many NULLs in MySQL/MariaDB.)

ALTER TABLE `users`
    ADD COLUMN `thaid_sub` VARCHAR(64) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL AFTER `department`,
    ADD UNIQUE KEY `uniq_thaid_sub` (`thaid_sub`);
