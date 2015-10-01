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

CREATE TABLE `bulletin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增编号',
  `content` text COMMENT '公告内容',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `last_mod_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='网站公告表';

ALTER TABLE `issue` ADD COLUMN `come_from` VARCHAR(30) DEFAULT '网站上传' COMMENT '来源';
ALTER TABLE `issue` ADD COLUMN `checker` VARCHAR(30) COMMENT '发现人';

INSERT INTO `user_type`(user_type_id, type_name, gtype) VALUES(19, '特权管理员', 1);

ALTER TABLE `target` ADD COLUMN `status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '状态 0 未删除, 1 已删除';
ALTER TABLE `area` ADD COLUMN `status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '状态 0 未删除, 1 已删除';

ALTER TABLE `user` MODIFY COLUMN `smartphone` VARCHAR(15) COMMENT '手机号';
ALTER TABLE `user` ADD COLUMN `department` VARCHAR(30) COMMENT '部门';