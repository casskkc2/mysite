ALTER TABLE `user` ADD COLUMN `last_login_ip` VARCHAR(30) COMMENT '最后一次登录ip' AFTER `last_mod_time`;
ALTER TABLE `area` ADD COLUMN `sort` INT(11) NOT NULL DEFAULT 0 COMMENT '排序' AFTER `path`;
ALTER TABLE `target` ADD COLUMN `sort` INT(11) NOT NULL DEFAULT 0 COMMENT '排序' AFTER `path`;

ALTER TABLE `user` ADD COLUMN `status` INT(11) NOT NULL DEFAULT 1 COMMENT '状态' AFTER `target`;

CREATE TABLE `login_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `create_time` datetime NOT NULL,
  `ip` varchar(20) DEFAULT NULL,
  `city_id` varchar(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;