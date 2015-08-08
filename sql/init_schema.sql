/*
SQLyog Ultimate v8.32 
MySQL - 5.5.34 : Database - cmdb
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Table structure for table `area` */

DROP TABLE IF EXISTS `area`;

CREATE TABLE `area` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL COMMENT '名称',
  `path` varchar(45) NOT NULL COMMENT '父节点字符串',
  `last_mod_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `city_id` varchar(6) NOT NULL COMMENT '所属城市id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='区域表';

/*Data for the table `area` */

LOCK TABLES `area` WRITE;

UNLOCK TABLES;

/*Table structure for table `issue` */

DROP TABLE IF EXISTS `issue`;

CREATE TABLE `issue` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增编号',
  `city_id` varchar(6) NOT NULL COMMENT '所属城市id',
  `area1` varchar(45) NOT NULL COMMENT '区域',
  `area2` varchar(45) NOT NULL COMMENT '类别',
  `area3` varchar(45) NOT NULL COMMENT '路/街',
  `area4` varchar(45) DEFAULT NULL COMMENT '路',
  `area_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '区域id(最末级)',
  `target1` varchar(45) NOT NULL COMMENT '一级指标',
  `target2` varchar(45) NOT NULL COMMENT '二级指标',
  `target3` varchar(45) NOT NULL COMMENT '三级指标',
  `target_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '指标id',
  `target_code` varchar(45) NOT NULL COMMENT '指标代码',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `examine_time` datetime NOT NULL COMMENT '检查日期时间',
  `weight` tinyint(3) unsigned NOT NULL COMMENT '计数',
  `des` text COMMENT '详细描述',
  `img` varchar(100) NOT NULL COMMENT '图片路径',
  `user_id` int(10) unsigned NOT NULL COMMENT '录入用户id',
  `last_mod_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑用户id',
  `status_id` tinyint(3) unsigned NOT NULL COMMENT '问题状态id',
  `last_mod_user2_id` int(10) unsigned NOT NULL COMMENT '最后操作用户id(城管部门人员)',
  `is_vp` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '重要问题标识',
  `db_chk_rs` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '复查结果(0未解决，1已解决)',
  `lat` varchar(45) NOT NULL COMMENT '纬度',
  `lng` varchar(45) NOT NULL COMMENT '经度',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `last_mod_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `tags` text COMMENT '标签(用于搜索)',
  `start_date` date NOT NULL COMMENT '开始计时日期',
  `end_date` date NOT NULL COMMENT '到期日期',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='问题数据表';

/*Data for the table `issue` */

LOCK TABLES `issue` WRITE;

UNLOCK TABLES;

/*Table structure for table `issue_reply` */

DROP TABLE IF EXISTS `issue_reply`;

CREATE TABLE `issue_reply` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `issue_id` int(10) unsigned NOT NULL COMMENT '问题id',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '类型(1未指定,2图片,3视频)',
  `path` varchar(100) DEFAULT NULL COMMENT '附件路径',
  `text` text COMMENT '问题回复',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `user_id` varchar(45) NOT NULL COMMENT '操作用户id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='问题附件及回复';

/*Data for the table `issue_reply` */

LOCK TABLES `issue_reply` WRITE;

UNLOCK TABLES;

/*Table structure for table `issue_status` */

DROP TABLE IF EXISTS `issue_status`;

CREATE TABLE `issue_status` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `status_id` tinyint(3) unsigned NOT NULL COMMENT '状态id',
  `name` varchar(45) NOT NULL COMMENT '状态名称',
  `last_mod_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COMMENT='状态表';

/*Data for the table `issue_status` */

LOCK TABLES `issue_status` WRITE;

insert  into `issue_status`(`id`,`status_id`,`name`,`last_mod_time`) values (1,1,'待审核','2015-08-02 11:27:42'),(2,2,'未通过','2015-08-02 11:27:50'),(3,3,'已通过','2015-08-02 11:31:08'),(4,4,'已处理','2015-08-02 11:31:18'),(5,5,'无法处理','2015-08-02 11:31:32'),(6,6,'非职责范围','2015-08-02 11:31:47'),(7,7,'非区属范围','2015-08-02 11:32:21'),(8,8,'同意已处理','2015-08-02 11:33:54'),(9,9,'同意无法处理','2015-08-02 11:35:06'),(10,10,'同意非职责范围','2015-08-02 11:35:23'),(11,11,'同意非区属范围','2015-08-02 11:36:41'),(12,12,'申请延期','2015-08-02 11:40:39');

UNLOCK TABLES;

/*Table structure for table `setting` */

DROP TABLE IF EXISTS `setting`;

CREATE TABLE `setting` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(45) NOT NULL COMMENT '配置项key',
  `value` text NOT NULL COMMENT '配置项值',
  `serialized` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='配置项表';

/*Data for the table `setting` */

LOCK TABLES `setting` WRITE;

insert  into `setting`(`id`,`key`,`value`,`serialized`) values (1,'config_exp_days','5',0);

UNLOCK TABLES;

/*Table structure for table `target` */

DROP TABLE IF EXISTS `target`;

CREATE TABLE `target` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(45) DEFAULT NULL COMMENT '指标代码',
  `name` varchar(45) NOT NULL COMMENT '指标名称',
  `path` varchar(45) NOT NULL COMMENT '父节点字符串',
  `city_id` varchar(6) NOT NULL COMMENT '所属城市id',
  `last_mod_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='指标代码表';

/*Data for the table `target` */

LOCK TABLES `target` WRITE;

UNLOCK TABLES;

/*Table structure for table `user` */

DROP TABLE IF EXISTS `user`;

CREATE TABLE `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(45) NOT NULL COMMENT '用户名',
  `smartphone` varchar(15) NOT NULL COMMENT '手机号',
  `password` varchar(45) NOT NULL COMMENT '密文密码',
  `city_id` varchar(6) NOT NULL COMMENT '所属城市id',
  `user_type_id` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '用户类型',
  `area` text COMMENT '区域权限',
  `target` text COMMENT '指标权限',
  `last_login_time` datetime DEFAULT NULL COMMENT '最后一次登录的时间',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `change_pwd_time` datetime DEFAULT NULL COMMENT '最后一次修改密码的时间',
  `pwd_changed_times` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '密码修改次数',
  `last_mod_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户表';

/*Data for the table `user` */

LOCK TABLES `user` WRITE;

UNLOCK TABLES;

/*Table structure for table `user_gps` */

DROP TABLE IF EXISTS `user_gps`;

CREATE TABLE `user_gps` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL COMMENT '用户id',
  `lat` varchar(45) NOT NULL COMMENT '纬度',
  `lng` varchar(45) NOT NULL COMMENT '经度',
  `create_time` datetime NOT NULL COMMENT '上传时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户位置表';

/*Data for the table `user_gps` */

LOCK TABLES `user_gps` WRITE;

UNLOCK TABLES;

/*Table structure for table `user_type` */

DROP TABLE IF EXISTS `user_type`;

CREATE TABLE `user_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_type_id` tinyint(3) unsigned NOT NULL COMMENT '用户类型',
  `type_name` varchar(45) NOT NULL COMMENT '类型名称',
  `gtype` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1后台操作账户，2浏览账户',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='用户类型表';

/*Data for the table `user_type` */

LOCK TABLES `user_type` WRITE;

insert  into `user_type`(`id`,`user_type_id`,`type_name`,`gtype`) values (1,11,'一般管理员',1),(2,12,'普通管理员',1),(3,10,'超级管理员',1),(4,21,'一般账户',2),(5,22,'管理员账户',2),(6,20,'高级用户',2);

UNLOCK TABLES;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
