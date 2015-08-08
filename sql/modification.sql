ALTER TABLE `user` ADD COLUMN `last_login_ip` VARCHAR(30) COMMENT '最后一次登录ip' AFTER `last_mod_time`;
