ALTER TABLE `user` ADD COLUMN `last_login_ip` VARCHAR(30) COMMENT '最后一次登录ip' AFTER `last_mod_time`;
ALTER TABLE `area` ADD COLUMN `sort` INT(11) NOT NULL DEFAULT 0 COMMENT '排序' AFTER `path`;
ALTER TABLE `target` ADD COLUMN `sort` INT(11) NOT NULL DEFAULT 0 COMMENT '排序' AFTER `path`;