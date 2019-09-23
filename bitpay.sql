/*
SQLyog Ultimate v13.1.1 (64 bit)
MySQL - 5.5.62-log : Database - pay
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Table structure for table `think_ad_buy` */

DROP TABLE IF EXISTS `think_ad_buy`;

CREATE TABLE `think_ad_buy` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ad_no` varchar(15) NOT NULL COMMENT '挂单唯一编号',
  `userid` int(11) NOT NULL COMMENT '发布用户id',
  `location` int(10) NOT NULL COMMENT '地区',
  `currency` int(10) NOT NULL COMMENT '货币',
  `margin` decimal(4,2) NOT NULL DEFAULT '0.00' COMMENT '溢价',
  `min_limit` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '限额（最小）',
  `max_limit` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '限额（最大）',
  `pay_method` tinyint(4) NOT NULL COMMENT '支付方式',
  `message` varchar(500) NOT NULL COMMENT '留言',
  `due_time` int(10) NOT NULL COMMENT '购买付款期限(分钟)',
  `safe_option` tinyint(1) NOT NULL DEFAULT '0' COMMENT '安全选项,0不开启,1开启',
  `trust_only` tinyint(1) NOT NULL DEFAULT '0' COMMENT '仅限受信任的交易者(0关闭,1开启)',
  `open_time` varchar(100) NOT NULL DEFAULT '1,1,1,1,1,1,1' COMMENT '开启时间(单个1开启,单个0关闭,0-1表示0点到1点开启)',
  `add_time` int(10) NOT NULL COMMENT '添加时间',
  `state` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态(1进行中,2下架,3已完成)',
  `finished_time` int(10) DEFAULT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '默认值，不用赋值',
  `coin` int(10) NOT NULL DEFAULT '0',
  `fee` decimal(5,2) NOT NULL DEFAULT '0.00',
  `amount` int(11) DEFAULT '0' COMMENT '购买数量',
  `price` decimal(10,2) DEFAULT '0.00',
  `pay_method2` tinyint(4) DEFAULT NULL COMMENT '支付宝',
  `pay_method3` tinyint(4) DEFAULT NULL COMMENT '微信',
  `pay_method4` tinyint(4) DEFAULT NULL COMMENT '云闪付',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ad_no` (`ad_no`) USING BTREE,
  KEY `userid` (`userid`) USING BTREE,
  KEY `currency` (`currency`) USING BTREE,
  KEY `trust_only` (`trust_only`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE,
  KEY `state` (`state`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `think_ad_buy` */

/*Table structure for table `think_ad_sell` */

DROP TABLE IF EXISTS `think_ad_sell`;

CREATE TABLE `think_ad_sell` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ad_no` varchar(15) NOT NULL COMMENT '挂单唯一编号',
  `userid` int(11) NOT NULL COMMENT '发布用户id',
  `location` int(10) NOT NULL COMMENT '地区',
  `currency` int(10) NOT NULL COMMENT '货币',
  `margin` decimal(4,2) NOT NULL DEFAULT '0.00' COMMENT '溢价',
  `min_price` decimal(12,2) DEFAULT '0.00',
  `min_limit` decimal(12,2) NOT NULL DEFAULT '0.00',
  `max_limit` decimal(12,2) NOT NULL DEFAULT '0.00',
  `pay_method` varchar(20) NOT NULL COMMENT '支付方式',
  `message` varchar(500) NOT NULL COMMENT '留言',
  `safe_option` tinyint(1) NOT NULL DEFAULT '0' COMMENT '安全选项,0不开启,1开启',
  `trust_only` tinyint(1) NOT NULL DEFAULT '0' COMMENT '仅限受信任的交易者(0关闭,1开启)',
  `open_time` varchar(100) NOT NULL DEFAULT '1,1,1,1,1,1,1' COMMENT '开启时间(单个1开启,单个0关闭,0-1表示0点到1点开启)',
  `add_time` int(10) NOT NULL COMMENT '添加时间',
  `state` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态(1进行中,2下架,3完成)',
  `finished_time` int(10) DEFAULT NULL COMMENT '完成时间',
  `type` tinyint(1) NOT NULL DEFAULT '1',
  `coin` int(10) NOT NULL DEFAULT '0',
  `fee` decimal(4,2) DEFAULT '0.00',
  `price` decimal(12,2) DEFAULT '0.00',
  `is_check` tinyint(4) DEFAULT '0' COMMENT '0:待审核，1：通过，2：拒绝',
  `amount` int(11) DEFAULT '0' COMMENT '出售数量',
  `pay_method2` tinyint(4) DEFAULT NULL COMMENT '支付宝',
  `pay_method3` tinyint(4) DEFAULT NULL COMMENT '微信',
  `pay_method4` tinyint(4) DEFAULT NULL COMMENT '云闪付',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ad_no` (`ad_no`) USING BTREE,
  KEY `userid` (`userid`) USING BTREE,
  KEY `currency` (`currency`) USING BTREE,
  KEY `trust_only` (`trust_only`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE,
  KEY `state` (`state`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `think_ad_sell` */

/*Table structure for table `think_address` */

DROP TABLE IF EXISTS `think_address`;

CREATE TABLE `think_address` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) DEFAULT NULL COMMENT '用户ID',
  `address` varchar(255) DEFAULT NULL COMMENT '地址',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态:0未分配1已分配',
  `addtime` varchar(60) DEFAULT NULL COMMENT '添加时间',
  `note` varchar(255) DEFAULT NULL COMMENT '备注',
  `type` varchar(10) DEFAULT NULL COMMENT '钱包类型,btc和eth',
  `password` varchar(255) DEFAULT NULL COMMENT 'ETH钱包地址密码',
  `privatekey` varchar(255) DEFAULT NULL COMMENT '私钥',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `think_address` */

/*Table structure for table `think_admin` */

DROP TABLE IF EXISTS `think_admin`;

CREATE TABLE `think_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) COLLATE utf8_bin DEFAULT '' COMMENT '用户名',
  `password` varchar(32) COLLATE utf8_bin DEFAULT '' COMMENT '密码',
  `portrait` varchar(100) COLLATE utf8_bin DEFAULT NULL COMMENT '头像',
  `loginnum` int(11) DEFAULT '0' COMMENT '登陆次数',
  `last_login_ip` varchar(255) COLLATE utf8_bin DEFAULT '' COMMENT '最后登录IP',
  `last_login_time` int(11) DEFAULT '0' COMMENT '最后登录时间',
  `real_name` varchar(20) COLLATE utf8_bin DEFAULT '' COMMENT '真实姓名',
  `status` int(1) DEFAULT '0' COMMENT '状态',
  `groupid` int(11) DEFAULT '1' COMMENT '用户角色id',
  `token` varchar(32) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

/*Data for the table `think_admin` */

insert  into `think_admin`(`id`,`username`,`password`,`portrait`,`loginnum`,`last_login_ip`,`last_login_time`,`real_name`,`status`,`groupid`,`token`) values 
(1,'admin','bcdc0b231056d9acf93c00c58ddab31b','20161122\\admin.jpg',548,'183.31.245.163',1569201228,'admin',1,1,'41a8f96765efb4db193e5932bf353acc'),
(29,'cus1','f3c44112258ecd8847c8378855a5dde7','',2,'127.0.0.1',1567234646,'客服一',1,23,'6fd73eb569c520f42b3478fe5cfbc7c0');

/*Table structure for table `think_agent_reward` */

DROP TABLE IF EXISTS `think_agent_reward`;

CREATE TABLE `think_agent_reward` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `duid` int(11) DEFAULT NULL,
  `amount` decimal(20,8) DEFAULT '0.00000000',
  `type` tinyint(4) DEFAULT '0' COMMENT '0:商户提币，1：用户提币，2：用户充值',
  `create_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `think_agent_reward` */

/*Table structure for table `think_article` */

DROP TABLE IF EXISTS `think_article`;

CREATE TABLE `think_article` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '文章逻辑ID',
  `title` varchar(128) NOT NULL COMMENT '文章标题',
  `cate_id` int(11) NOT NULL DEFAULT '1' COMMENT '文章类别',
  `photo` varchar(64) DEFAULT '' COMMENT '文章图片',
  `remark` varchar(256) DEFAULT '' COMMENT '文章描述',
  `keyword` varchar(32) DEFAULT '' COMMENT '文章关键字',
  `content` text NOT NULL COMMENT '文章内容',
  `views` int(11) NOT NULL DEFAULT '1' COMMENT '浏览量',
  `status` tinyint(1) DEFAULT NULL,
  `type` int(1) NOT NULL DEFAULT '1' COMMENT '文章类型',
  `is_tui` int(1) DEFAULT '0' COMMENT '是否推荐',
  `from` varchar(16) NOT NULL DEFAULT '' COMMENT '来源',
  `writer` varchar(64) NOT NULL COMMENT '作者',
  `ip` varchar(16) NOT NULL,
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `a_title` (`title`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='文章表';

/*Data for the table `think_article` */

insert  into `think_article`(`id`,`title`,`cate_id`,`photo`,`remark`,`keyword`,`content`,`views`,`status`,`type`,`is_tui`,`from`,`writer`,`ip`,`create_time`,`update_time`) values 
(1,'测试公告',23,'','123','123','<p>测试公告</p>',1,1,1,1,'','','',1566197983,1566197983);

/*Table structure for table `think_article_cate` */

DROP TABLE IF EXISTS `think_article_cate`;

CREATE TABLE `think_article_cate` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) DEFAULT NULL COMMENT '分类名称',
  `orderby` varchar(10) DEFAULT '100' COMMENT '排序',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `status` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4;

/*Data for the table `think_article_cate` */

insert  into `think_article_cate`(`id`,`name`,`orderby`,`create_time`,`update_time`,`status`) values 
(24,'使用教程','2',1556202561,1556202561,1),
(23,'最新公告','1',1556202546,1556202546,0);

/*Table structure for table `think_auth_group` */

DROP TABLE IF EXISTS `think_auth_group`;

CREATE TABLE `think_auth_group` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `title` char(100) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `rules` text NOT NULL,
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4;

/*Data for the table `think_auth_group` */

insert  into `think_auth_group`(`id`,`title`,`status`,`rules`,`create_time`,`update_time`) values 
(1,'超级管理员',1,'',1446535750,1446535750),
(4,'系统测试员',1,'1,2,9,10,11,12,3,30,31,32,33,34,4,35,36,37,38,39,61,62,63,92,93,115,5,6,7,8,27,28,29,13,14,22,24,25,40,41,42,43,26,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,70,71,72,73,74,80,75,76,77,78,79,84,85,88,89,90,107,86,87,101,95,96,97,98,99,100,103,102,94,104,108,105,106,109,110,111,112,113,114',1446535750,1566183317),
(23,'客服',1,'84,91,119,120,121,122,123,124',1567178730,1567234600);

/*Table structure for table `think_auth_group_access` */

DROP TABLE IF EXISTS `think_auth_group_access`;

CREATE TABLE `think_auth_group_access` (
  `uid` mediumint(8) unsigned NOT NULL,
  `group_id` mediumint(8) unsigned NOT NULL,
  UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
  KEY `uid` (`uid`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

/*Data for the table `think_auth_group_access` */

insert  into `think_auth_group_access`(`uid`,`group_id`) values 
(1,4),
(21,4),
(22,4),
(23,4),
(24,4),
(25,4),
(26,4),
(28,4),
(29,23);

/*Table structure for table `think_auth_rule` */

DROP TABLE IF EXISTS `think_auth_rule`;

CREATE TABLE `think_auth_rule` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(80) NOT NULL DEFAULT '',
  `title` char(20) NOT NULL DEFAULT '',
  `type` tinyint(1) NOT NULL DEFAULT '1',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `css` varchar(20) NOT NULL COMMENT '样式',
  `condition` char(100) NOT NULL DEFAULT '',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '父栏目ID',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=127 DEFAULT CHARSET=utf8mb4;

/*Data for the table `think_auth_rule` */

insert  into `think_auth_rule`(`id`,`name`,`title`,`type`,`status`,`css`,`condition`,`pid`,`sort`,`create_time`,`update_time`) values 
(1,'#','系统管理',1,1,'fa fa-gear','',0,1,1446535750,1477312169),
(2,'admin/user/index','用户管理',1,1,'','',1,10,1446535750,1477312169),
(3,'admin/role/index','角色管理',1,1,'','',1,20,1446535750,1477312169),
(4,'admin/menu/index','菜单管理',1,1,'','',1,30,1446535750,1477312169),
(5,'#','数据库管理',1,1,'fa fa-database','',0,7,1446535750,1477312169),
(6,'admin/data/index','数据库备份',1,1,'','',5,50,1446535750,1477312169),
(7,'admin/data/optimize','优化表',1,1,'','',6,50,1477312169,1477312169),
(8,'admin/data/repair','修复表',1,1,'','',6,50,1477312169,1477312169),
(9,'admin/user/add','添加用户',1,1,'','',2,50,1477312169,1477312169),
(10,'admin/user/edit','编辑用户',1,1,'','',2,50,1477312169,1477312169),
(11,'admin/user/del','删除用户',1,1,'','',2,50,1477312169,1477312169),
(12,'admin/user/state','用户状态',1,1,'','',2,20,1477312169,1477312169),
(13,'#','日志管理',1,1,'fa fa-tasks','',0,6,1477312169,1477312169),
(14,'admin/log/operate_log','行为日志',1,1,'','',13,50,1477312169,1477312169),
(22,'admin/log/del_log','删除日志',1,1,'','',14,50,1477312169,1477316778),
(24,'#','文章管理',1,1,'fa fa-paste','',0,4,1477312169,1477312169),
(25,'admin/article/index_cate','文章分类',1,1,'','',24,10,1477312260,1477312260),
(26,'admin/article/index','文章列表',1,1,'','',24,20,1477312333,1477312333),
(27,'admin/data/import','数据库还原',1,1,'','',5,50,1477639870,1477639870),
(28,'admin/data/revert','还原',1,1,'','',27,50,1477639972,1477639972),
(29,'admin/data/del','删除',1,1,'','',27,50,1477640011,1477640011),
(30,'admin/role/add','添加角色',1,1,'','',3,50,1477640011,1477640011),
(31,'admin/role/edit','编辑角色',1,1,'','',3,50,1477640011,1477640011),
(32,'admin/role/del','删除角色',1,1,'','',3,50,1477640011,1477640011),
(33,'admin/role/state','角色状态',1,1,'','',3,50,1477640011,1477640011),
(34,'admin/role/giveAccess','权限分配',1,1,'','',3,50,1477640011,1477640011),
(35,'admin/menu/add','添加菜单',1,1,'','',4,50,1477640011,147740011),
(36,'admin/menu/edit','编辑菜单',1,1,'','',4,50,1477640011,1477640011),
(37,'admin/menu/del','删除菜单',1,1,'','',4,50,1477640011,1477640011),
(38,'admin/menu/state','菜单状态',1,1,'','',4,50,1477640011,1477640011),
(39,'admin/menu/ruleOrderBy','菜单排序',1,1,'','',4,50,1477640011,1477640011),
(40,'admin/article/add_cate','添加分类',1,1,'','',25,50,1477640011,1477640011),
(41,'admin/article/edit_cate','编辑分类',1,1,'','',25,50,1477640011,1477640011),
(42,'admin/article/del_cate','删除分类',1,1,'','',25,50,1477640011,1477640011),
(43,'admin/article/cate_state','分类状态',1,1,'','',25,50,1477640011,1477640011),
(44,'admin/article/add_article','添加文章',1,1,'','',26,50,1477640011,1477640011),
(45,'admin/article/edit_article','编辑文章',1,1,'','',26,50,1477640011,1477640011),
(46,'admin/article/del_article','删除文章',1,1,'','',26,50,1477640011,1477640011),
(47,'admin/article/article_state','文章状态',1,1,'','',26,50,1477640011,1477640011),
(48,'#','挂单管理',1,0,'fa fa-image','',0,5,1477640011,1477640011),
(49,'admin/banner/index_position','挂单位',1,1,'','',48,10,1477640011,1477640011),
(50,'admin/banner/add_position','添加挂单位',1,1,'','',49,50,1477640011,1477640011),
(51,'admin/banneredit_position','编辑挂单位',1,1,'','',49,50,1477640011,1477640011),
(52,'admin/banner/del_position','删除挂单位',1,1,'','',49,50,1477640011,1477640011),
(53,'admin/banner/position_state','挂单位状态',1,1,'','',49,50,1477640011,1477640011),
(54,'admin/banner/index','挂单列表',1,1,'','',48,20,1477640011,1477640011),
(55,'admin/banner/add','添加挂单',1,1,'','',54,50,1477640011,1477640011),
(56,'admin/banner/edit','编辑挂单',1,1,'','',54,50,1477640011,1477640011),
(57,'admin/banner/del','删除挂单',1,1,'','',54,50,1477640011,1477640011),
(58,'admin/banner/state','挂单状态',1,1,'','',54,50,1477640011,1477640011),
(61,'admin/config/index','配置管理',1,1,'','',1,50,1479908607,1479908607),
(62,'admin/config/index','配置列表',1,1,'','',61,50,1479908607,1487943813),
(63,'admin/config/save','保存配置',1,1,'','',61,50,1479908607,1487943831),
(70,'#','会员管理',1,0,'fa fa-users','',0,3,1484103066,1484103066),
(72,'admin/member/add_group','添加会员组',1,1,'','',71,50,1484103304,1484103304),
(71,'admin/member/group','会员组',1,1,'','',70,10,1484103304,1484103304),
(73,'admin/member/edit_group','编辑会员组',1,1,'','',71,50,1484103304,1484103304),
(74,'admin/member/del_group','删除会员组',1,1,'','',71,50,1484103304,1484103304),
(75,'admin/member/index','会员列表',1,1,'','',70,20,1484103304,1484103304),
(76,'admin/member/add_member','添加会员',1,1,'','',75,50,1484103304,1484103304),
(77,'admin/member/edit_member','编辑会员',1,1,'','',75,50,1484103304,1484103304),
(78,'admin/member/del_member','删除会员',1,1,'','',75,50,1484103304,1484103304),
(79,'admin/member/member_status','会员状态',1,1,'','',75,50,1484103304,1487937671),
(80,'admin/member/group_status','会员组状态',1,1,'','',71,50,1484103304,1484103304),
(84,'#','商户管理',1,1,'fa fa-user','',0,2,1542891863,1542892118),
(85,'admin/merchant/tibi','商户提币列表',1,0,'','',84,50,1542891863,1542892118),
(86,'#','问题管理',1,1,'fa fa-comment','',0,5,1543030476,1543030476),
(87,'admin/message/index','问题列表',1,1,'','',86,50,1543030515,1543030515),
(88,'admin/merchant/address','USDT地址列表',1,1,'','',84,49,1543063416,1543063416),
(89,'admin/merchant/withdrawlist','用户提币列表',1,1,'','',84,50,1543063942,1543063942),
(90,'admin/merchant/rechargelist','用户充值列表',1,0,'','',84,50,1543066579,1543066579),
(91,'admin/merchant/index?reg_type=1','商户列表',1,1,'','',84,48,1543068278,1543068278),
(92,'admin/merchant/usdtlog','USDT更新记录',1,1,'','',1,51,1446535750,1566021721),
(93,'admin/merchant/btclog','BTC更新记录',1,1,'','',1,51,1446535750,1446535750),
(94,'admin/merchant/agentreward','代理奖励',1,1,'','',102,49,1543066579,1543066579),
(95,'admin/merchant/adlist','挂卖列表',1,1,'','',101,52,1446535750,1446535750),
(96,'admin/merchant/orderlist','匹配订单列表',1,1,'','',101,53,1446535750,1446535750),
(97,'admin/merchant/traderrecharge','承兑商充币列表',1,1,'','',101,51,1446535750,1446535750),
(98,'admin/merchant/buyadlist','承兑商挂买单',1,1,'','',101,54,1446535750,1446535750),
(99,'admin/merchant/orderlistbuy','承兑商求购订单',1,1,'','',101,54,1446535750,1446535750),
(100,'admin/merchant/traderreward','承兑商奖励',1,1,'','',101,49,1446535750,1446535750),
(101,'#','承兑商管理',1,1,'fa fa-user','',0,3,1446535750,1446535750),
(102,'#','代理商管理',1,1,'fa fa-user','',0,4,1446535750,1446535750),
(103,'admin/merchant/index?reg_type=2','承兑商列表',1,1,'','',101,0,1543066579,1543066579),
(104,'admin/merchant/index?reg_type=3','代理商列表',1,1,'','',102,0,1543066579,1543066579),
(105,'#','交易管理',1,0,'fa fa-image','',0,3,1555559364,1555561185),
(106,'admin/merchant/orderlist','盘口订单',1,0,' Example of exchange','',105,50,1555559706,1555559853),
(107,'admin/merchant/orderlistbuy?reg_type=1','商户出售订单列表',1,1,'','',84,52,1446535750,1446535750),
(108,'admin/merchant/orderlistbuy?reg_type=3','代理商出售订单列表',1,1,'','',102,2,1446535750,1446535750),
(109,'#','统计管理',1,1,'fa fa-cube','',0,7,1446535750,1446535750),
(110,'admin/merchant/statistics','平台统计',1,1,'','',109,0,1446535750,1446535750),
(111,'admin/merchant/merchantstatistics','商户统计',1,1,'','',109,0,1446535750,1446535750),
(112,'#','提币管理',1,1,'fa fa-user','',0,5,1556181096,1556181096),
(113,'admin/merchant/tibi','管理提币',1,1,'','',112,50,1556181210,1556181210),
(114,'admin/merchant/rechargelist','盘口提币',1,1,'','',112,50,1556181295,1556181295),
(115,'admin/merchant/log','登录日志',1,1,'','',13,70,1556181210,1556181210),
(116,'admin/log/financelog','资金日志',1,1,'fa fa-usd','',13,50,1566958834,1566958834),
(118,'admin/merchant/addresslist','钱包地址列表',1,1,'','',1,50,1566994828,1566994828),
(119,'admin/merchant/merchant_check','商户注册审核',1,1,'','',91,50,1567009697,1567009697),
(120,'admin/merchant/merchant_agent_check','代理审核',1,1,'','',91,50,1567009697,1567009697),
(121,'admin/merchant/merchant_trader_check','承兑商审核',1,1,'','',91,50,1567009697,1567009697),
(122,'admin/merchant/merchant_status','更改会员状态',1,1,'','',91,50,1567009697,1567009697),
(123,'admin/merchant/del_merchant','删除会员',1,1,'','',91,50,1567009697,1567009697),
(124,'admin/merchant/edit_merchant','编辑会员',1,1,'','',91,50,1567009697,1567009697),
(125,'admin/merchant/merchant_check','承兑商注册审核',1,1,'','',103,50,1567009697,1567009697),
(126,'admin/merchant/merchant_check','代理注册审核',1,1,'','',104,50,1567009697,1567009697);

/*Table structure for table `think_banner` */

DROP TABLE IF EXISTS `think_banner`;

CREATE TABLE `think_banner` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(64) DEFAULT NULL,
  `ad_position_id` varchar(10) DEFAULT NULL COMMENT '挂单位',
  `link_url` varchar(128) DEFAULT NULL,
  `images` varchar(128) DEFAULT NULL,
  `start_date` date DEFAULT NULL COMMENT '开始时间',
  `end_date` date DEFAULT NULL COMMENT '结束时间',
  `status` tinyint(1) DEFAULT NULL COMMENT '状态',
  `closed` tinyint(1) DEFAULT '0',
  `orderby` tinyint(3) DEFAULT '100',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4;

/*Data for the table `think_banner` */

insert  into `think_banner`(`id`,`title`,`ad_position_id`,`link_url`,`images`,`start_date`,`end_date`,`status`,`closed`,`orderby`) values 
(52,'1','25','','20190819/7fde9b834d7f727fd43caf06e6fc5432.png','2019-08-19','2019-08-20',1,0,100);

/*Table structure for table `think_banner_position` */

DROP TABLE IF EXISTS `think_banner_position`;

CREATE TABLE `think_banner_position` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) DEFAULT NULL COMMENT '分类名称',
  `orderby` varchar(10) DEFAULT '100' COMMENT '排序',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `status` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4;

/*Data for the table `think_banner_position` */

insert  into `think_banner_position`(`id`,`name`,`orderby`,`create_time`,`update_time`,`status`) values 
(25,'首页banner','10',1502181832,1502434196,1),
(26,'6168','11',1502182772,1502182772,1);

/*Table structure for table `think_coin_log` */

DROP TABLE IF EXISTS `think_coin_log`;

CREATE TABLE `think_coin_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) DEFAULT NULL,
  `balance` decimal(20,8) DEFAULT '0.00000000',
  `coin_type` tinyint(4) DEFAULT '0' COMMENT '0:usdt，1:btc',
  `create_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `think_coin_log` */

/*Table structure for table `think_config` */

DROP TABLE IF EXISTS `think_config`;

CREATE TABLE `think_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '配置ID',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '配置名称',
  `value` text COMMENT '配置值',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4;

/*Data for the table `think_config` */

insert  into `think_config`(`id`,`name`,`value`) values 
(1,'web_site_title','BETER'),
(2,'web_site_description',''),
(3,'web_site_keyword',''),
(4,'web_site_icp',''),
(5,'web_site_cnzz',''),
(6,'web_site_copy','Copyright  2019  All rights reserved.'),
(7,'web_site_close','1'),
(8,'list_rows','20'),
(9,'admin_allow_ip',''),
(12,'alisms_appkey',''),
(13,'alisms_appsecret',''),
(14,'alisms_signname',''),
(15,'logo','20190901/e51cc260c53245891bfa550d58e78b90.png'),
(16,'merchant_tibi_fee','0.28'),
(17,'user_tibi_fee',''),
(18,'user_recharge_fee',''),
(19,'merchant_tibi_max','10000'),
(20,'merchant_tibi_min','100'),
(21,'usdt_confirms','6'),
(22,'agent_tibi_fee','0'),
(23,'agent_withdraw_fee','0'),
(24,'agent_recharge_fee','1'),
(25,'usdt_price_way','2'),
(26,'usdt_price_min','6'),
(27,'usdt_price_max','8'),
(28,'mobile_url','https://api.mysubmail.com/message/send.json'),
(29,'mobile_user','25328'),
(30,'mobile_pwd','0ba24b9330a813dff4197dbcbc79bff7'),
(65,'mobile_method','post'),
(31,'send_message_content','您的挂单号:{tx_id}买家已付款，数量：{usdt}，请及时处理。'),
(32,'trader_merchant_fee','3'),
(33,'usdt_price_way_buy','2'),
(34,'usdt_price_min_buy','6'),
(35,'usdt_buy_trader_fee','0.1'),
(36,'usdt_buy_merchant_fee','0'),
(37,'usdt_price_max_buy','8'),
(38,'trader_platform_get','1'),
(39,'reg_invite_on','0'),
(40,'usdt_pwd','zrCzPx8ozSfWjJqN4iQ81'),
(41,'usdt_fee','0'),
(43,'ad_down_remain_amount','15'),
(44,'trader_parent_get','5'),
(45,'trader_merchant_parent_get','3'),
(46,'trader_pp_max_unfinished_order','100'),
(47,'rpc_user','usdtuser'),
(48,'rpc_pwd','123123123'),
(49,'rpc_url','127.0.0.1'),
(50,'rpc_port','60001'),
(51,'base_address','3Ee5NW584q1wWKCWaxaJzZHdyqiUL46dmP'),
(52,'pk_waiting_finished_num','20'),
(53,'ethip','http://127.0.0.1:40010'),
(54,'usdtaddr','0xdac17f958d2ee523a2206206994597c13d831ec7'),
(55,'feepass',''),
(56,'feeaddr',''),
(57,'hzaddr','0x4D0567E832BA305e08489deCfbCd49041A861B4b'),
(58,'mincover','10'),
(59,'wallettype','erc'),
(60,'usdtabi','[{\"constant\":true,\"inputs\":[],\"name\":\"name\",\"outputs\":[{\"name\":\"\",\"type\":\"string\"}],\"payable\":false,\"stateMutability\":\"view\",\"type\":\"function\"},{\"constant\":false,\"inputs\":[{\"name\":\"_upgradedAddress\",\"type\":\"address\"}],\"name\":\"deprecate\",\"outputs\":[],\"payable\":false,\"stateMutability\":\"nonpayable\",\"type\":\"function\"},{\"constant\":false,\"inputs\":[{\"name\":\"_spender\",\"type\":\"address\"},{\"name\":\"_value\",\"type\":\"uint256\"}],\"name\":\"approve\",\"outputs\":[],\"payable\":false,\"stateMutability\":\"nonpayable\",\"type\":\"function\"},{\"constant\":true,\"inputs\":[],\"name\":\"deprecated\",\"outputs\":[{\"name\":\"\",\"type\":\"bool\"}],\"payable\":false,\"stateMutability\":\"view\",\"type\":\"function\"},{\"constant\":false,\"inputs\":[{\"name\":\"_evilUser\",\"type\":\"address\"}],\"name\":\"addBlackList\",\"outputs\":[],\"payable\":false,\"stateMutability\":\"nonpayable\",\"type\":\"function\"},{\"constant\":true,\"inputs\":[],\"name\":\"totalSupply\",\"outputs\":[{\"name\":\"\",\"type\":\"uint256\"}],\"payable\":false,\"stateMutability\":\"view\",\"type\":\"function\"},{\"constant\":false,\"inputs\":[{\"name\":\"_from\",\"type\":\"address\"},{\"name\":\"_to\",\"type\":\"address\"},{\"name\":\"_value\",\"type\":\"uint256\"}],\"name\":\"transferFrom\",\"outputs\":[],\"payable\":false,\"stateMutability\":\"nonpayable\",\"type\":\"function\"},{\"constant\":true,\"inputs\":[],\"name\":\"upgradedAddress\",\"outputs\":[{\"name\":\"\",\"type\":\"address\"}],\"payable\":false,\"stateMutability\":\"view\",\"type\":\"function\"},{\"constant\":true,\"inputs\":[{\"name\":\"\",\"type\":\"address\"}],\"name\":\"balances\",\"outputs\":[{\"name\":\"\",\"type\":\"uint256\"}],\"payable\":false,\"stateMutability\":\"view\",\"type\":\"function\"},{\"constant\":true,\"inputs\":[],\"name\":\"decimals\",\"outputs\":[{\"name\":\"\",\"type\":\"uint256\"}],\"payable\":false,\"stateMutability\":\"view\",\"type\":\"function\"},{\"constant\":true,\"inputs\":[],\"name\":\"maximumFee\",\"outputs\":[{\"name\":\"\",\"type\":\"uint256\"}],\"payable\":false,\"stateMutability\":\"view\",\"type\":\"function\"},{\"constant\":true,\"inputs\":[],\"name\":\"_totalSupply\",\"outputs\":[{\"name\":\"\",\"type\":\"uint256\"}],\"payable\":false,\"stateMutability\":\"view\",\"type\":\"function\"},{\"constant\":false,\"inputs\":[],\"name\":\"unpause\",\"outputs\":[],\"payable\":false,\"stateMutability\":\"nonpayable\",\"type\":\"function\"},{\"constant\":true,\"inputs\":[{\"name\":\"_maker\",\"type\":\"address\"}],\"name\":\"getBlackListStatus\",\"outputs\":[{\"name\":\"\",\"type\":\"bool\"}],\"payable\":false,\"stateMutability\":\"view\",\"type\":\"function\"},{\"constant\":true,\"inputs\":[{\"name\":\"\",\"type\":\"address\"},{\"name\":\"\",\"type\":\"address\"}],\"name\":\"allowed\",\"outputs\":[{\"name\":\"\",\"type\":\"uint256\"}],\"payable\":false,\"stateMutability\":\"view\",\"type\":\"function\"},{\"constant\":true,\"inputs\":[],\"name\":\"paused\",\"outputs\":[{\"name\":\"\",\"type\":\"bool\"}],\"payable\":false,\"stateMutability\":\"view\",\"type\":\"function\"},{\"constant\":true,\"inputs\":[{\"name\":\"who\",\"type\":\"address\"}],\"name\":\"balanceOf\",\"outputs\":[{\"name\":\"\",\"type\":\"uint256\"}],\"payable\":false,\"stateMutability\":\"view\",\"type\":\"function\"},{\"constant\":false,\"inputs\":[],\"name\":\"pause\",\"outputs\":[],\"payable\":false,\"stateMutability\":\"nonpayable\",\"type\":\"function\"},{\"constant\":true,\"inputs\":[],\"name\":\"getOwner\",\"outputs\":[{\"name\":\"\",\"type\":\"address\"}],\"payable\":false,\"stateMutability\":\"view\",\"type\":\"function\"},{\"constant\":true,\"inputs\":[],\"name\":\"owner\",\"outputs\":[{\"name\":\"\",\"type\":\"address\"}],\"payable\":false,\"stateMutability\":\"view\",\"type\":\"function\"},{\"constant\":true,\"inputs\":[],\"name\":\"symbol\",\"outputs\":[{\"name\":\"\",\"type\":\"string\"}],\"payable\":false,\"stateMutability\":\"view\",\"type\":\"function\"},{\"constant\":false,\"inputs\":[{\"name\":\"_to\",\"type\":\"address\"},{\"name\":\"_value\",\"type\":\"uint256\"}],\"name\":\"transfer\",\"outputs\":[],\"payable\":false,\"stateMutability\":\"nonpayable\",\"type\":\"function\"},{\"constant\":false,\"inputs\":[{\"name\":\"newBasisPoints\",\"type\":\"uint256\"},{\"name\":\"newMaxFee\",\"type\":\"uint256\"}],\"name\":\"setParams\",\"outputs\":[],\"payable\":false,\"stateMutability\":\"nonpayable\",\"type\":\"function\"},{\"constant\":false,\"inputs\":[{\"name\":\"amount\",\"type\":\"uint256\"}],\"name\":\"issue\",\"outputs\":[],\"payable\":false,\"stateMutability\":\"nonpayable\",\"type\":\"function\"},{\"constant\":false,\"inputs\":[{\"name\":\"amount\",\"type\":\"uint256\"}],\"name\":\"redeem\",\"outputs\":[],\"payable\":false,\"stateMutability\":\"nonpayable\",\"type\":\"function\"},{\"constant\":true,\"inputs\":[{\"name\":\"_owner\",\"type\":\"address\"},{\"name\":\"_spender\",\"type\":\"address\"}],\"name\":\"allowance\",\"outputs\":[{\"name\":\"remaining\",\"type\":\"uint256\"}],\"payable\":false,\"stateMutability\":\"view\",\"type\":\"function\"},{\"constant\":true,\"inputs\":[],\"name\":\"basisPointsRate\",\"outputs\":[{\"name\":\"\",\"type\":\"uint256\"}],\"payable\":false,\"stateMutability\":\"view\",\"type\":\"function\"},{\"constant\":true,\"inputs\":[{\"name\":\"\",\"type\":\"address\"}],\"name\":\"isBlackListed\",\"outputs\":[{\"name\":\"\",\"type\":\"bool\"}],\"payable\":false,\"stateMutability\":\"view\",\"type\":\"function\"},{\"constant\":false,\"inputs\":[{\"name\":\"_clearedUser\",\"type\":\"address\"}],\"name\":\"removeBlackList\",\"outputs\":[],\"payable\":false,\"stateMutability\":\"nonpayable\",\"type\":\"function\"},{\"constant\":true,\"inputs\":[],\"name\":\"MAX_UINT\",\"outputs\":[{\"name\":\"\",\"type\":\"uint256\"}],\"payable\":false,\"stateMutability\":\"view\",\"type\":\"function\"},{\"constant\":false,\"inputs\":[{\"name\":\"newOwner\",\"type\":\"address\"}],\"name\":\"transferOwnership\",\"outputs\":[],\"payable\":false,\"stateMutability\":\"nonpayable\",\"type\":\"function\"},{\"constant\":false,\"inputs\":[{\"name\":\"_blackListedUser\",\"type\":\"address\"}],\"name\":\"destroyBlackFunds\",\"outputs\":[],\"payable\":false,\"stateMutability\":\"nonpayable\",\"type\":\"function\"},{\"inputs\":[{\"name\":\"_initialSupply\",\"type\":\"uint256\"},{\"name\":\"_name\",\"type\":\"string\"},{\"name\":\"_symbol\",\"type\":\"string\"},{\"name\":\"_decimals\",\"type\":\"uint256\"}],\"payable\":false,\"stateMutability\":\"nonpayable\",\"type\":\"constructor\"},{\"anonymous\":false,\"inputs\":[{\"indexed\":false,\"name\":\"amount\",\"type\":\"uint256\"}],\"name\":\"Issue\",\"type\":\"event\"},{\"anonymous\":false,\"inputs\":[{\"indexed\":false,\"name\":\"amount\",\"type\":\"uint256\"}],\"name\":\"Redeem\",\"type\":\"event\"},{\"anonymous\":false,\"inputs\":[{\"indexed\":false,\"name\":\"newAddress\",\"type\":\"address\"}],\"name\":\"Deprecate\",\"type\":\"event\"},{\"anonymous\":false,\"inputs\":[{\"indexed\":false,\"name\":\"feeBasisPoints\",\"type\":\"uint256\"},{\"indexed\":false,\"name\":\"maxFee\",\"type\":\"uint256\"}],\"name\":\"Params\",\"type\":\"event\"},{\"anonymous\":false,\"inputs\":[{\"indexed\":false,\"name\":\"_blackListedUser\",\"type\":\"address\"},{\"indexed\":false,\"name\":\"_balance\",\"type\":\"uint256\"}],\"name\":\"DestroyedBlackFunds\",\"type\":\"event\"},{\"anonymous\":false,\"inputs\":[{\"indexed\":false,\"name\":\"_user\",\"type\":\"address\"}],\"name\":\"AddedBlackList\",\"type\":\"event\"},{\"anonymous\":false,\"inputs\":[{\"indexed\":false,\"name\":\"_user\",\"type\":\"address\"}],\"name\":\"RemovedBlackList\",\"type\":\"event\"},{\"anonymous\":false,\"inputs\":[{\"indexed\":true,\"name\":\"owner\",\"type\":\"address\"},{\"indexed\":true,\"name\":\"spender\",\"type\":\"address\"},{\"indexed\":false,\"name\":\"value\",\"type\":\"uint256\"}],\"name\":\"Approval\",\"type\":\"event\"},{\"anonymous\":false,\"inputs\":[{\"indexed\":true,\"name\":\"from\",\"type\":\"address\"},{\"indexed\":true,\"name\":\"to\",\"type\":\"address\"},{\"indexed\":false,\"name\":\"value\",\"type\":\"uint256\"}],\"name\":\"Transfer\",\"type\":\"event\"},{\"anonymous\":false,\"inputs\":[],\"name\":\"Pause\",\"type\":\"event\"},{\"anonymous\":false,\"inputs\":[],\"name\":\"Unpause\",\"type\":\"event\"}]'),
(61,'feeaddrprive','0x05F72bE0CB7Cf86f41a49788C7CCaCC3DC80034C'),
(63,'usdt_price_add','0.07'),
(62,'usdtabi_bak','[{\"constant\":true,\"inputs\":[],\"name\":\"name\",\"outputs\":[{\"name\":\"\",\"type\":\"string\"}],\"payable\":false,\"stateMutability\":\"view\",\"type\":\"function\"},{\"constant\":false,\"inputs\":[{\"name\":\"_upgradedAddress\",\"type\":\"address\"}],\"name\":\"deprecate\",\"outputs\":[],\"payable\":false,\"stateMutability\":\"nonpayable\",\"type\":\"function\"},{\"constant\":false,\"inputs\":[{\"name\":\"_spender\",\"type\":\"address\"},{\"name\":\"_value\",\"type\":\"uint256\"}],\"name\":\"approve\",\"outputs\":[],\"payable\":false,\"stateMutability\":\"nonpayable\",\"type\":\"function\"},{\"constant\":true,\"inputs\":[],\"name\":\"deprecated\",\"outputs\":[{\"name\":\"\",\"type\":\"bool\"}],\"payable\":false,\"stateMutability\":\"view\",\"type\":\"function\"},{\"constant\":false,\"inputs\":[{\"name\":\"_evilUser\",\"type\":\"address\"}],\"name\":\"addBlackList\",\"outputs\":[],\"payable\":false,\"stateMutability\":\"nonpayable\",\"type\":\"function\"},{\"constant\":true,\"inputs\":[],\"name\":\"totalSupply\",\"outputs\":[{\"name\":\"\",\"type\":\"uint256\"}],\"payable\":false,\"stateMutability\":\"view\",\"type\":\"function\"},{\"constant\":false,\"inputs\":[{\"name\":\"_from\",\"type\":\"address\"},{\"name\":\"_to\",\"type\":\"address\"},{\"name\":\"_value\",\"type\":\"uint256\"}],\"name\":\"transferFrom\",\"outputs\":[],\"payable\":false,\"stateMutability\":\"nonpayable\",\"type\":\"function\"},{\"constant\":true,\"inputs\":[],\"name\":\"upgradedAddress\",\"outputs\":[{\"name\":\"\",\"type\":\"address\"}],\"payable\":false,\"stateMutability\":\"view\",\"type\":\"function\"},{\"constant\":true,\"inputs\":[{\"name\":\"\",\"type\":\"address\"}],\"name\":\"balances\",\"outputs\":[{\"name\":\"\",\"type\":\"uint256\"}],\"payable\":false,\"stateMutability\":\"view\",\"type\":\"function\"},{\"constant\":true,\"inputs\":[],\"name\":\"decimals\",\"outputs\":[{\"name\":\"\",\"type\":\"uint256\"}],\"payable\":false,\"stateMutability\":\"view\",\"type\":\"function\"},{\"constant\":true,\"inputs\":[],\"name\":\"maximumFee\",\"outputs\":[{\"name\":\"\",\"type\":\"uint256\"}],\"payable\":false,\"stateMutability\":\"view\",\"type\":\"function\"},{\"constant\":true,\"inputs\":[],\"name\":\"_totalSupply\",\"outputs\":[{\"name\":\"\",\"type\":\"uint256\"}],\"payable\":false,\"stateMutability\":\"view\",\"type\":\"function\"},{\"constant\":false,\"inputs\":[],\"name\":\"unpause\",\"outputs\":[],\"payable\":false,\"stateMutability\":\"nonpayable\",\"type\":\"function\"},{\"constant\":true,\"inputs\":[{\"name\":\"_maker\",\"type\":\"address\"}],\"name\":\"getBlackListStatus\",\"outputs\":[{\"name\":\"\",\"type\":\"bool\"}],\"payable\":false,\"stateMutability\":\"view\",\"type\":\"function\"},{\"constant\":true,\"inputs\":[{\"name\":\"\",\"type\":\"address\"},{\"name\":\"\",\"type\":\"address\"}],\"name\":\"allowed\",\"outputs\":[{\"name\":\"\",\"type\":\"uint256\"}],\"payable\":false,\"stateMutability\":\"view\",\"type\":\"function\"},{\"constant\":true,\"inputs\":[],\"name\":\"paused\",\"outputs\":[{\"name\":\"\",\"type\":\"bool\"}],\"payable\":false,\"stateMutability\":\"view\",\"type\":\"function\"},{\"constant\":true,\"inputs\":[{\"name\":\"who\",\"type\":\"address\"}],\"name\":\"balanceOf\",\"outputs\":[{\"name\":\"\",\"type\":\"uint256\"}],\"payable\":false,\"stateMutability\":\"view\",\"type\":\"function\"},{\"constant\":false,\"inputs\":[],\"name\":\"pause\",\"outputs\":[],\"payable\":false,\"stateMutability\":\"nonpayable\",\"type\":\"function\"},{\"constant\":true,\"inputs\":[],\"name\":\"getOwner\",\"outputs\":[{\"name\":\"\",\"type\":\"address\"}],\"payable\":false,\"stateMutability\":\"view\",\"type\":\"function\"},{\"constant\":true,\"inputs\":[],\"name\":\"owner\",\"outputs\":[{\"name\":\"\",\"type\":\"address\"}],\"payable\":false,\"stateMutability\":\"view\",\"type\":\"function\"},{\"constant\":true,\"inputs\":[],\"name\":\"symbol\",\"outputs\":[{\"name\":\"\",\"type\":\"string\"}],\"payable\":false,\"stateMutability\":\"view\",\"type\":\"function\"},{\"constant\":false,\"inputs\":[{\"name\":\"_to\",\"type\":\"address\"},{\"name\":\"_value\",\"type\":\"uint256\"}],\"name\":\"transfer\",\"outputs\":[],\"payable\":false,\"stateMutability\":\"nonpayable\",\"type\":\"function\"},{\"constant\":false,\"inputs\":[{\"name\":\"newBasisPoints\",\"type\":\"uint256\"},{\"name\":\"newMaxFee\",\"type\":\"uint256\"}],\"name\":\"setParams\",\"outputs\":[],\"payable\":false,\"stateMutability\":\"nonpayable\",\"type\":\"function\"},{\"constant\":false,\"inputs\":[{\"name\":\"amount\",\"type\":\"uint256\"}],\"name\":\"issue\",\"outputs\":[],\"payable\":false,\"stateMutability\":\"nonpayable\",\"type\":\"function\"},{\"constant\":false,\"inputs\":[{\"name\":\"amount\",\"type\":\"uint256\"}],\"name\":\"redeem\",\"outputs\":[],\"payable\":false,\"stateMutability\":\"nonpayable\",\"type\":\"function\"},{\"constant\":true,\"inputs\":[{\"name\":\"_owner\",\"type\":\"address\"},{\"name\":\"_spender\",\"type\":\"address\"}],\"name\":\"allowance\",\"outputs\":[{\"name\":\"remaining\",\"type\":\"uint256\"}],\"payable\":false,\"stateMutability\":\"view\",\"type\":\"function\"},{\"constant\":true,\"inputs\":[],\"name\":\"basisPointsRate\",\"outputs\":[{\"name\":\"\",\"type\":\"uint256\"}],\"payable\":false,\"stateMutability\":\"view\",\"type\":\"function\"},{\"constant\":true,\"inputs\":[{\"name\":\"\",\"type\":\"address\"}],\"name\":\"isBlackListed\",\"outputs\":[{\"name\":\"\",\"type\":\"bool\"}],\"payable\":false,\"stateMutability\":\"view\",\"type\":\"function\"},{\"constant\":false,\"inputs\":[{\"name\":\"_clearedUser\",\"type\":\"address\"}],\"name\":\"removeBlackList\",\"outputs\":[],\"payable\":false,\"stateMutability\":\"nonpayable\",\"type\":\"function\"},{\"constant\":true,\"inputs\":[],\"name\":\"MAX_UINT\",\"outputs\":[{\"name\":\"\",\"type\":\"uint256\"}],\"payable\":false,\"stateMutability\":\"view\",\"type\":\"function\"},{\"constant\":false,\"inputs\":[{\"name\":\"newOwner\",\"type\":\"address\"}],\"name\":\"transferOwnership\",\"outputs\":[],\"payable\":false,\"stateMutability\":\"nonpayable\",\"type\":\"function\"},{\"constant\":false,\"inputs\":[{\"name\":\"_blackListedUser\",\"type\":\"address\"}],\"name\":\"destroyBlackFunds\",\"outputs\":[],\"payable\":false,\"stateMutability\":\"nonpayable\",\"type\":\"function\"},{\"inputs\":[{\"name\":\"_initialSupply\",\"type\":\"uint256\"},{\"name\":\"_name\",\"type\":\"string\"},{\"name\":\"_symbol\",\"type\":\"string\"},{\"name\":\"_decimals\",\"type\":\"uint256\"}],\"payable\":false,\"stateMutability\":\"nonpayable\",\"type\":\"constructor\"},{\"anonymous\":false,\"inputs\":[{\"indexed\":false,\"name\":\"amount\",\"type\":\"uint256\"}],\"name\":\"Issue\",\"type\":\"event\"},{\"anonymous\":false,\"inputs\":[{\"indexed\":false,\"name\":\"amount\",\"type\":\"uint256\"}],\"name\":\"Redeem\",\"type\":\"event\"},{\"anonymous\":false,\"inputs\":[{\"indexed\":false,\"name\":\"newAddress\",\"type\":\"address\"}],\"name\":\"Deprecate\",\"type\":\"event\"},{\"anonymous\":false,\"inputs\":[{\"indexed\":false,\"name\":\"feeBasisPoints\",\"type\":\"uint256\"},{\"indexed\":false,\"name\":\"maxFee\",\"type\":\"uint256\"}],\"name\":\"Params\",\"type\":\"event\"},{\"anonymous\":false,\"inputs\":[{\"indexed\":false,\"name\":\"_blackListedUser\",\"type\":\"address\"},{\"indexed\":false,\"name\":\"_balance\",\"type\":\"uint256\"}],\"name\":\"DestroyedBlackFunds\",\"type\":\"event\"},{\"anonymous\":false,\"inputs\":[{\"indexed\":false,\"name\":\"_user\",\"type\":\"address\"}],\"name\":\"AddedBlackList\",\"type\":\"event\"},{\"anonymous\":false,\"inputs\":[{\"indexed\":false,\"name\":\"_user\",\"type\":\"address\"}],\"name\":\"RemovedBlackList\",\"type\":\"event\"},{\"anonymous\":false,\"inputs\":[{\"indexed\":true,\"name\":\"owner\",\"type\":\"address\"},{\"indexed\":true,\"name\":\"spender\",\"type\":\"address\"},{\"indexed\":false,\"name\":\"value\",\"type\":\"uint256\"}],\"name\":\"Approval\",\"type\":\"event\"},{\"anonymous\":false,\"inputs\":[{\"indexed\":true,\"name\":\"from\",\"type\":\"address\"},{\"indexed\":true,\"name\":\"to\",\"type\":\"address\"},{\"indexed\":false,\"name\":\"value\",\"type\":\"uint256\"}],\"name\":\"Transfer\",\"type\":\"event\"},{\"anonymous\":false,\"inputs\":[],\"name\":\"Pause\",\"type\":\"event\"},{\"anonymous\":false,\"inputs\":[],\"name\":\"Unpause\",\"type\":\"event\"}]'),
(64,'usdt_price_add_buy','0');

/*Table structure for table `think_financelog` */

DROP TABLE IF EXISTS `think_financelog`;

CREATE TABLE `think_financelog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL COMMENT '用户ID',
  `user` varchar(50) DEFAULT NULL COMMENT '用户姓名',
  `note` varchar(300) DEFAULT NULL COMMENT '描述',
  `amount` varchar(200) DEFAULT NULL COMMENT '数量',
  `status` tinyint(1) DEFAULT NULL COMMENT '0增加1减少',
  `add_time` int(11) DEFAULT NULL COMMENT '添加时间',
  `op` varchar(255) DEFAULT NULL COMMENT '操作员',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

/*Data for the table `think_financelog` */

/*Table structure for table `think_log` */

DROP TABLE IF EXISTS `think_log`;

CREATE TABLE `think_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) DEFAULT NULL COMMENT '用户ID',
  `admin_name` varchar(50) DEFAULT NULL COMMENT '用户姓名',
  `description` varchar(300) DEFAULT NULL COMMENT '描述',
  `ip` char(60) DEFAULT NULL COMMENT 'IP地址',
  `status` tinyint(1) DEFAULT NULL COMMENT '1 成功 2 失败',
  `add_time` int(11) DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

/*Data for the table `think_log` */

/*Table structure for table `think_login_log` */

DROP TABLE IF EXISTS `think_login_log`;

CREATE TABLE `think_login_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` int(11) DEFAULT NULL,
  `login_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  `online` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `think_login_log` */

/*Table structure for table `think_member` */

DROP TABLE IF EXISTS `think_member`;

CREATE TABLE `think_member` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `account` varchar(64) DEFAULT NULL COMMENT '邮件或者手机',
  `nickname` varchar(32) DEFAULT NULL COMMENT '昵称',
  `sex` int(10) DEFAULT NULL COMMENT '1男2女',
  `password` char(32) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `head_img` varchar(128) DEFAULT NULL COMMENT '头像',
  `integral` int(11) DEFAULT '0' COMMENT '积分',
  `money` int(11) DEFAULT '0' COMMENT '账户余额',
  `mobile` varchar(11) DEFAULT NULL COMMENT '认证的手机号码',
  `create_time` int(11) DEFAULT '0' COMMENT '注册时间',
  `update_time` int(11) DEFAULT NULL COMMENT '最后一次登录',
  `login_num` varchar(15) DEFAULT NULL COMMENT '登录次数',
  `status` tinyint(1) DEFAULT NULL COMMENT '1正常  0 禁用',
  `closed` tinyint(1) DEFAULT '0' COMMENT '0正常，1删除',
  `token` char(32) DEFAULT '0' COMMENT '令牌',
  `session_id` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=212066 DEFAULT CHARSET=utf8mb4;

/*Data for the table `think_member` */

/*Table structure for table `think_member_group` */

DROP TABLE IF EXISTS `think_member_group`;

CREATE TABLE `think_member_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '留言Id',
  `group_name` varchar(32) NOT NULL COMMENT '留言评论作者',
  `status` tinyint(1) DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL COMMENT '留言回复时间',
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COMMENT='文章评论表';

/*Data for the table `think_member_group` */

insert  into `think_member_group`(`id`,`group_name`,`status`,`create_time`,`update_time`) values 
(1,'系统组',1,1441616559,1525405964),
(2,'游客组',1,1441617195,1502940499),
(3,'VIP1',1,1441769224,1502940506);

/*Table structure for table `think_merchant` */

DROP TABLE IF EXISTS `think_merchant`;

CREATE TABLE `think_merchant` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(10) NOT NULL COMMENT '姓名',
  `mobile` char(11) NOT NULL COMMENT '手机号',
  `password` char(32) NOT NULL COMMENT '密码',
  `paypassword` char(32) DEFAULT NULL COMMENT '交易密码',
  `invite` varchar(15) DEFAULT NULL COMMENT '邀请码',
  `idcard` varchar(50) DEFAULT NULL COMMENT '身份证号',
  `idcard_zheng` varchar(255) DEFAULT NULL COMMENT '正面照',
  `idcard_fan` varchar(255) DEFAULT NULL COMMENT '反面照',
  `appid` char(16) NOT NULL COMMENT '商户id',
  `key` char(32) NOT NULL COMMENT 'MD5Key',
  `addtime` int(11) NOT NULL COMMENT '注册时间',
  `status` tinyint(2) NOT NULL DEFAULT '1',
  `usdt` decimal(20,8) DEFAULT '0.00000000' COMMENT '活动的usdt',
  `usdtd` decimal(20,8) DEFAULT '0.00000000' COMMENT '冻结usdt',
  `headpic` varchar(255) DEFAULT NULL COMMENT '头像',
  `ga` varchar(255) DEFAULT NULL,
  `merchant_tibi_fee` decimal(10,4) DEFAULT '0.0000',
  `user_withdraw_fee` decimal(10,4) DEFAULT '0.0000',
  `user_recharge_fee` decimal(10,4) DEFAULT '0.0000',
  `pid` int(11) DEFAULT '0',
  `reg_check` tinyint(4) DEFAULT '0' COMMENT '0:待审核，1:通过，2：拒绝',
  `agent_check` tinyint(4) DEFAULT '0' COMMENT '0:未提交，1：通过，2：拒绝，3：已提交申请',
  `trader_check` tinyint(4) DEFAULT '0' COMMENT '承兑商审核，0:未提交，1：通过，2：拒绝，3：已提交申请',
  `usdtb` varchar(200) DEFAULT NULL COMMENT 'omni地址',
  `usdte` varchar(255) DEFAULT NULL COMMENT 'erc地址',
  `trader_recharge_fee` decimal(10,4) DEFAULT '0.0000',
  `c_bank` varchar(20) DEFAULT NULL,
  `c_bank_detail` varchar(50) DEFAULT NULL,
  `c_bank_card` varchar(50) DEFAULT NULL,
  `c_wechat_account` varchar(50) DEFAULT NULL,
  `c_wechat_img` varchar(255) DEFAULT NULL,
  `c_alipay_account` varchar(50) DEFAULT NULL,
  `c_alipay_img` varchar(255) DEFAULT NULL,
  `transact` int(11) DEFAULT '0',
  `averge` int(11) DEFAULT '0',
  `online` tinyint(4) DEFAULT '0',
  `pp_amount` int(11) DEFAULT '0',
  `transact_buy` int(11) DEFAULT '0',
  `averge_buy` int(11) DEFAULT '0',
  `trader_trader_get` decimal(10,2) DEFAULT '0.00',
  `trader_parent_get` decimal(10,2) DEFAULT '0.00',
  `trader_merchant_parent_get` decimal(10,2) DEFAULT '0.00',
  `pptrader` varchar(255) DEFAULT NULL,
  `merchant_pk_fee` decimal(10,2) DEFAULT '0.00' COMMENT '商户盘口费率',
  `reg_type` tinyint(4) DEFAULT NULL COMMENT '1,商户,2,承兑商,3,代理商',
  `recharge_amount` decimal(20,8) DEFAULT '0.00000000' COMMENT '充值数量汇总',
  `withdraw_amount` decimal(20,8) DEFAULT '0.00000000' COMMENT '提币数量汇总',
  `ad_on_sell` int(11) DEFAULT '0' COMMENT '挂卖单数',
  `ad_on_buy` int(11) DEFAULT '0' COMMENT '挂买单数',
  `order_sell_success_num` int(11) DEFAULT '0' COMMENT '出售成功次数',
  `order_buy_success_num` int(11) DEFAULT '0' COMMENT '求购成功次数',
  `order_sell_usdt_amount` decimal(20,8) DEFAULT '0.00000000' COMMENT '总出售usdt数量',
  `order_buy_usdt_amount` decimal(20,8) DEFAULT '0.00000000' COMMENT '总求购usdt数量',
  `nickname` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `think_merchant` */

insert  into `think_merchant`(`id`,`name`,`mobile`,`password`,`paypassword`,`invite`,`idcard`,`idcard_zheng`,`idcard_fan`,`appid`,`key`,`addtime`,`status`,`usdt`,`usdtd`,`headpic`,`ga`,`merchant_tibi_fee`,`user_withdraw_fee`,`user_recharge_fee`,`pid`,`reg_check`,`agent_check`,`trader_check`,`usdtb`,`usdte`,`trader_recharge_fee`,`c_bank`,`c_bank_detail`,`c_bank_card`,`c_wechat_account`,`c_wechat_img`,`c_alipay_account`,`c_alipay_img`,`transact`,`averge`,`online`,`pp_amount`,`transact_buy`,`averge_buy`,`trader_trader_get`,`trader_parent_get`,`trader_merchant_parent_get`,`pptrader`,`merchant_pk_fee`,`reg_type`,`recharge_amount`,`withdraw_amount`,`ad_on_sell`,`ad_on_buy`,`order_sell_success_num`,`order_buy_success_num`,`order_sell_usdt_amount`,`order_buy_usdt_amount`,`nickname`) values 
(1,'阿生','13113427817','e10adc3949ba59abbe56e057f20f883e','4297f44b13955235245b2497399d7a93','UTIAVM','123456789123456789','','','SwDcPBwtIvxXM5C1','89fc5d2225fde98a05fc47799cd658cb',1569050766,1,1000.00000000,0.00000000,NULL,'Y6Z5VZE3QP2KI7HU|0|1',0.0000,0.0000,3.0000,0,1,1,0,NULL,NULL,0.0000,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,0,0,0.00,0.10,1.00,'3',0.00,3,0.00000000,0.00000000,0,0,0,0,0.00000000,0.00000000,'阿生'),
(2,'杨志明','17360179685','4297f44b13955235245b2497399d7a93','81dc9bdb52d04dc20036dbd8313ed055',NULL,'513722199608055115','','','kuUn1fhQTasoAeYX','a86223ca9349c17848409cf3b57843df',1569050953,1,1000.00000000,0.00000000,NULL,'LIIQRC7JXYQWFAS5|1|1',0.0000,0.0000,0.0000,1,1,0,0,NULL,NULL,0.0000,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,0,0,0.00,0.00,0.00,'3',3.00,1,0.00000000,0.00000000,0,0,0,0,0.00000000,1109.56801626,'yzm'),
(3,'王艳','13373156547','830bd55ee7d5ce1b2c4eab5240bcd06b','78707fab6fc79c5ae989fc0bbf64984d',NULL,'220303197608312220','','','YAAf7j9iu1HBGSLj','ace46e9e83db53c2c397d6fc543135e9',1569050979,1,1000.00000000,0.00000000,NULL,'KDOTUCPRFGTQJSB4|0|0',0.0000,0.1000,0.0000,1,1,0,1,NULL,'0x032280dd29514a79fe9144220d1330e414a2f8ab',0.0000,NULL,NULL,NULL,NULL,NULL,NULL,NULL,13,3,0,15,0,0,0.00,0.00,0.00,'',0.10,2,0.00000000,0.00000000,0,0,0,0,1140.78742554,0.00000000,'sun');

/*Table structure for table `think_merchant_apilog` */

DROP TABLE IF EXISTS `think_merchant_apilog`;

CREATE TABLE `think_merchant_apilog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `duid` int(11) DEFAULT NULL,
  `api_name` varchar(50) DEFAULT NULL,
  `request_param` varchar(255) DEFAULT NULL,
  `return_param` text,
  `create_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

/*Data for the table `think_merchant_apilog` */

/*Table structure for table `think_merchant_bankcard` */

DROP TABLE IF EXISTS `think_merchant_bankcard`;

CREATE TABLE `think_merchant_bankcard` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(50) DEFAULT NULL,
  `c_bank` varchar(20) DEFAULT NULL,
  `c_bank_detail` varchar(100) DEFAULT NULL,
  `c_bank_card` varchar(100) DEFAULT NULL,
  `create_time` int(11) DEFAULT '0',
  `update_time` int(11) DEFAULT '0',
  `truename` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `think_merchant_bankcard` */

/*Table structure for table `think_merchant_log` */

DROP TABLE IF EXISTS `think_merchant_log`;

CREATE TABLE `think_merchant_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) DEFAULT NULL COMMENT '用户ID',
  `admin_name` varchar(50) DEFAULT NULL COMMENT '用户姓名',
  `description` varchar(300) DEFAULT NULL COMMENT '描述',
  `ip` char(60) DEFAULT NULL COMMENT 'IP地址',
  `status` tinyint(1) DEFAULT NULL COMMENT '1 成功 2 失败',
  `add_time` int(11) DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

/*Data for the table `think_merchant_log` */

/*Table structure for table `think_merchant_recharge` */

DROP TABLE IF EXISTS `think_merchant_recharge`;

CREATE TABLE `think_merchant_recharge` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `from_address` varchar(255) NOT NULL COMMENT '转出地址',
  `to_address` varchar(255) DEFAULT NULL COMMENT '转入地址',
  `coinname` varchar(20) DEFAULT NULL COMMENT '币种',
  `txid` varchar(255) DEFAULT NULL COMMENT 'hash值',
  `num` decimal(20,8) DEFAULT '0.00000000' COMMENT '数量',
  `fee` decimal(20,8) DEFAULT '0.00000000' COMMENT '手续费',
  `mum` decimal(20,8) DEFAULT '0.00000000' COMMENT '实到',
  `addtime` int(11) DEFAULT NULL COMMENT '时间',
  `status` tinyint(2) DEFAULT '0' COMMENT '状态',
  `confirmations` tinyint(4) DEFAULT '0' COMMENT '确认数',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

/*Data for the table `think_merchant_recharge` */

/*Table structure for table `think_merchant_user_address` */

DROP TABLE IF EXISTS `think_merchant_user_address`;

CREATE TABLE `think_merchant_user_address` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `username` varchar(50) DEFAULT NULL COMMENT '用户名',
  `address` varchar(255) DEFAULT NULL COMMENT '钱包地址',
  `addtime` int(11) DEFAULT NULL COMMENT '申请时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4;

/*Data for the table `think_merchant_user_address` */

insert  into `think_merchant_user_address`(`id`,`merchant_id`,`username`,`address`,`addtime`) values 
(12,2,'admin','0x11a7500e3e6bde9d94a585161dffe05a93012860',1567239426),
(13,2,'admin','0x11a7500e3e6bde9d94a585161dffe05a93012860',1567239429),
(14,2,'admin','0x11a7500e3e6bde9d94a585161dffe05a93012860',1567239430),
(15,4,'admin','0x6d8ffc3c999de02900a7e13a58ee5472b0029a59',1567311675),
(16,5,'admin','0x0f491c4675a84d7c40e538b1737568ae48d40f21',1567475387),
(17,6,NULL,'0xd8865a9f321e8241629afca7e447d89b1937f11b',1567502485),
(18,10,'admin','0x315d9ad61efb1a438ecfa06247f710fcb980567b',1568948840),
(19,9,'admin','0xe996036921340f77df97e15f0a4b628e6139362d',1568962442);

/*Table structure for table `think_merchant_user_recharge` */

DROP TABLE IF EXISTS `think_merchant_user_recharge`;

CREATE TABLE `think_merchant_user_recharge` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `from_address` varchar(255) NOT NULL COMMENT '转出地址',
  `to_address` varchar(255) DEFAULT NULL COMMENT '转入地址',
  `coinname` varchar(20) DEFAULT NULL COMMENT '币种',
  `txid` varchar(255) DEFAULT NULL COMMENT 'hash值',
  `num` decimal(20,8) DEFAULT '0.00000000' COMMENT '数量',
  `fee` decimal(20,8) DEFAULT '0.00000000' COMMENT '手续费',
  `mum` decimal(20,8) DEFAULT '0.00000000' COMMENT '实到',
  `addtime` int(11) DEFAULT NULL COMMENT '时间',
  `status` tinyint(2) DEFAULT '0' COMMENT '状态',
  `confirmations` tinyint(4) DEFAULT '0' COMMENT '确认数',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

/*Data for the table `think_merchant_user_recharge` */

/*Table structure for table `think_merchant_user_withdraw` */

DROP TABLE IF EXISTS `think_merchant_user_withdraw`;

CREATE TABLE `think_merchant_user_withdraw` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `address` varchar(255) DEFAULT NULL,
  `username` varchar(20) DEFAULT NULL COMMENT '用户名',
  `num` decimal(20,8) DEFAULT '0.00000000' COMMENT '数量',
  `fee` decimal(20,8) DEFAULT '0.00000000' COMMENT '手续费',
  `mum` decimal(20,8) DEFAULT '0.00000000' COMMENT '实到',
  `txid` varchar(255) DEFAULT NULL COMMENT 'hash值',
  `addtime` int(11) NOT NULL,
  `status` tinyint(2) DEFAULT '0' COMMENT '状态，0:待审核，1:通过，2:拒绝，3:撤销',
  `endtime` int(11) DEFAULT NULL COMMENT '完成实际',
  `ordersn` varchar(255) DEFAULT NULL COMMENT '订单号唯一标识',
  `type` smallint(6) DEFAULT NULL COMMENT '1:走钱包,2:不走钱包',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

/*Data for the table `think_merchant_user_withdraw` */

/*Table structure for table `think_merchant_withdraw` */

DROP TABLE IF EXISTS `think_merchant_withdraw`;

CREATE TABLE `think_merchant_withdraw` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `username` varchar(255) DEFAULT NULL COMMENT '用户名',
  `address` varchar(255) DEFAULT NULL COMMENT '转入地址',
  `num` decimal(20,8) DEFAULT '0.00000000' COMMENT '数量',
  `fee` decimal(20,8) DEFAULT '0.00000000' COMMENT '手续费',
  `mum` decimal(20,8) DEFAULT '0.00000000' COMMENT '实到',
  `note` varchar(255) DEFAULT NULL COMMENT '备注',
  `txid` varchar(255) DEFAULT NULL COMMENT 'hash值',
  `addtime` int(11) DEFAULT NULL,
  `status` tinyint(2) DEFAULT '0' COMMENT '状态，0:待审核，1:通过，2:拒绝，3:撤销',
  `endtime` int(11) DEFAULT NULL COMMENT '完成实际',
  `ordersn` varchar(255) DEFAULT NULL COMMENT '订单号，唯一标识',
  `type` smallint(6) DEFAULT NULL COMMENT '1:走钱包,2:不走钱包',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `think_merchant_withdraw` */

/*Table structure for table `think_merchant_wx` */

DROP TABLE IF EXISTS `think_merchant_wx`;

CREATE TABLE `think_merchant_wx` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(50) DEFAULT NULL,
  `c_bank` varchar(20) DEFAULT NULL,
  `c_bank_detail` varchar(100) DEFAULT NULL,
  `c_bank_card` varchar(100) DEFAULT NULL,
  `create_time` int(11) DEFAULT '0',
  `update_time` int(11) DEFAULT '0',
  `truename` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `think_merchant_wx` */

insert  into `think_merchant_wx`(`id`,`merchant_id`,`name`,`c_bank`,`c_bank_detail`,`c_bank_card`,`create_time`,`update_time`,`truename`) values 
(1,2,'微信1','123456','20190921/1331adef8f527f62e2efc80cca7d5ba2.jpg',NULL,1569051456,1569051456,'杨志明'),
(2,3,'账户2','15833523795','20190921/b267d0219b96a5a4c52630f4f4ed1f09.jpg',NULL,1569051622,1569051622,'王艳');

/*Table structure for table `think_merchant_ysf` */

DROP TABLE IF EXISTS `think_merchant_ysf`;

CREATE TABLE `think_merchant_ysf` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(50) DEFAULT NULL,
  `c_bank` varchar(20) DEFAULT NULL,
  `c_bank_detail` varchar(100) DEFAULT NULL,
  `c_bank_card` varchar(100) DEFAULT NULL,
  `create_time` int(11) DEFAULT '0',
  `update_time` int(11) DEFAULT '0',
  `truename` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `think_merchant_ysf` */

/*Table structure for table `think_merchant_zfb` */

DROP TABLE IF EXISTS `think_merchant_zfb`;

CREATE TABLE `think_merchant_zfb` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(50) DEFAULT NULL,
  `c_bank` varchar(20) DEFAULT NULL,
  `c_bank_detail` varchar(100) DEFAULT NULL,
  `c_bank_card` varchar(100) DEFAULT NULL,
  `create_time` int(11) DEFAULT '0',
  `update_time` int(11) DEFAULT '0',
  `truename` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `think_merchant_zfb` */

insert  into `think_merchant_zfb`(`id`,`merchant_id`,`name`,`c_bank`,`c_bank_detail`,`c_bank_card`,`create_time`,`update_time`,`truename`) values 
(1,2,'支付宝1','1324035984@qq.com','20190921/98a8bb3bfa6b9ae5d99a6e914cdb4c37.jpg',NULL,1569051268,1569051268,'杨志明'),
(2,3,'账户1','15833523795','20190921/eb867d9ebb767b2f2427946ca75e04fa.jpg',NULL,1569051585,1569051585,'王艳');

/*Table structure for table `think_order_buy` */

DROP TABLE IF EXISTS `think_order_buy`;

CREATE TABLE `think_order_buy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `buy_id` int(10) NOT NULL COMMENT '买家的id',
  `buy_bid` int(11) NOT NULL DEFAULT '0' COMMENT '发布购买单子的id',
  `sell_id` int(10) NOT NULL COMMENT '卖家id',
  `sell_sid` int(10) NOT NULL DEFAULT '0' COMMENT '卖家发布买的id',
  `deal_amount` decimal(13,2) NOT NULL COMMENT '交易金额',
  `deal_price` decimal(13,2) NOT NULL DEFAULT '0.00' COMMENT '交易价格',
  `deal_ctype` int(2) NOT NULL DEFAULT '0' COMMENT '交易货币的种类',
  `deal_num` decimal(20,8) NOT NULL COMMENT '交易数量',
  `ctime` int(10) NOT NULL COMMENT '创建时间',
  `dktime` int(11) NOT NULL DEFAULT '0' COMMENT '打款时间',
  `ltime` int(10) NOT NULL DEFAULT '30' COMMENT '限时多长时间付款',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '0代表已经拍下1代付款2待收货3代评价4已经完成5取消交易6处于申诉状态 需要管理员处理',
  `desc` varchar(100) DEFAULT NULL COMMENT '交易操作描述',
  `finished_time` int(10) DEFAULT NULL,
  `order_no` varchar(55) NOT NULL COMMENT '订单编号',
  `cancle_op` int(1) NOT NULL DEFAULT '0' COMMENT '0默手动取消交易1为此交易已关闭，因为未及时将付款标记为完成。如果您已付款，请要求卖家重新打开交易。',
  `buy_pj` int(1) DEFAULT '0' COMMENT '来自买家的评价',
  `sell_pj` int(1) DEFAULT '0' COMMENT '来自卖家的评价',
  `su_type` int(1) NOT NULL DEFAULT '0' COMMENT '1我已付款，但卖家没有放行我比特币2卖家未遵守交易挂单条款',
  `su_reason` text,
  `sutp` varchar(255) DEFAULT NULL COMMENT '上传路径',
  `type` tinyint(1) NOT NULL DEFAULT '1',
  `deal_coin` int(10) NOT NULL DEFAULT '0',
  `fee` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `buy_username` varchar(255) DEFAULT NULL,
  `buy_address` varchar(255) DEFAULT NULL,
  `return_url` varchar(255) DEFAULT NULL,
  `notify_url` varchar(255) DEFAULT NULL,
  `orderid` varchar(255) DEFAULT NULL,
  `platform_fee` decimal(20,8) DEFAULT '0.00000000' COMMENT '平台利润，承兑商释放订单时更新',
  `check_code` varchar(20) NOT NULL COMMENT '校验码',
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_no` (`order_no`) USING BTREE,
  KEY `buy_id` (`buy_id`) USING BTREE,
  KEY `sell_id` (`sell_id`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `think_order_buy` */

/*Table structure for table `think_order_sell` */

DROP TABLE IF EXISTS `think_order_sell`;

CREATE TABLE `think_order_sell` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `buy_id` int(10) NOT NULL COMMENT '买家的id',
  `buy_bid` int(11) NOT NULL DEFAULT '0' COMMENT '发布购买单子的id',
  `sell_id` int(10) NOT NULL COMMENT '卖家id',
  `sell_sid` int(10) NOT NULL DEFAULT '0' COMMENT '买家发布买的id',
  `deal_amount` decimal(20,2) NOT NULL COMMENT '交易金额',
  `deal_price` decimal(13,2) NOT NULL DEFAULT '0.00' COMMENT '交易价格',
  `deal_ctype` int(2) NOT NULL DEFAULT '0' COMMENT '交易货币种类',
  `deal_num` decimal(20,8) NOT NULL COMMENT '交易数量',
  `ctime` int(10) NOT NULL COMMENT '创建时间',
  `dktime` int(11) NOT NULL DEFAULT '0' COMMENT '打款时间',
  `ltime` int(10) NOT NULL DEFAULT '60' COMMENT '限时多长时间付款',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '0代表已经拍下1代付款2待发货3代评价4已经完成5取消交易6处于申诉的状态管理员需要审核',
  `desc` varchar(100) DEFAULT NULL COMMENT '交易操作描述',
  `finished_time` int(10) DEFAULT NULL,
  `order_no` varchar(55) NOT NULL COMMENT '订单编号',
  `buy_pj` int(1) DEFAULT '0' COMMENT '买家给出的评价1好评2中评3差评',
  `sell_pj` int(1) DEFAULT '0' COMMENT '卖家给买家的评价',
  `su_type` int(1) NOT NULL DEFAULT '0' COMMENT '1 2',
  `su_reason` text,
  `cancle_op` int(2) NOT NULL DEFAULT '0' COMMENT '取消的原因',
  `sutp` varchar(155) DEFAULT NULL COMMENT '上传路径',
  `type` tinyint(1) NOT NULL DEFAULT '2',
  `deal_coin` int(10) NOT NULL DEFAULT '0',
  `fee` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `getpaymethod` varchar(255) DEFAULT NULL,
  `buyer_fee` decimal(20,8) DEFAULT '0.00000000' COMMENT '买家手续费，即承兑商',
  `pay` varchar(255) DEFAULT NULL COMMENT '银行卡',
  `pay2` varchar(255) DEFAULT NULL COMMENT '支付宝',
  `pay3` varchar(255) DEFAULT NULL COMMENT '微信',
  `pay4` varchar(255) DEFAULT NULL COMMENT '云闪付',
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_no` (`order_no`) USING BTREE,
  KEY `buy_id` (`buy_id`) USING BTREE,
  KEY `sell_id` (`sell_id`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `think_order_sell` */

/*Table structure for table `think_question` */

DROP TABLE IF EXISTS `think_question`;

CREATE TABLE `think_question` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `type` tinyint(2) DEFAULT NULL COMMENT '问题类型，1：充值，2：提币，3：其他问题',
  `content` varchar(255) DEFAULT NULL COMMENT '内容',
  `reply` varchar(255) DEFAULT NULL COMMENT '回复',
  `addtime` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

/*Data for the table `think_question` */

insert  into `think_question`(`id`,`merchant_id`,`type`,`content`,`reply`,`addtime`) values 
(1,5,2,'到账时间慢',' 请提供账号',1567562344);

/*Table structure for table `think_statistics` */

DROP TABLE IF EXISTS `think_statistics`;

CREATE TABLE `think_statistics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `platform_profit` decimal(20,8) DEFAULT '0.00000000' COMMENT '平台利润，所有平台的手续费',
  `agent_reward` decimal(20,8) DEFAULT '0.00000000' COMMENT '代理商奖励总和',
  `trader_reward` decimal(20,8) DEFAULT '0.00000000' COMMENT '承兑商奖励总和',
  `platform_usdt_amount` decimal(20,8) DEFAULT '0.00000000' COMMENT '平台现存usdt总数量，所有会员类型账户的冻结加活动',
  `recharge_total` decimal(20,8) DEFAULT '0.00000000' COMMENT '总充值数量',
  `withdraw_total` decimal(20,8) DEFAULT '0.00000000' COMMENT '总提币数量',
  `ad_sell_on_total` int(11) DEFAULT '0' COMMENT '现存挂单出售笔数，承兑商发布出售的单子，不含下架的，不含数量低于0的',
  `order_sell_amount` decimal(20,8) DEFAULT '0.00000000' COMMENT '现存挂单出售总USDT，所有承兑商挂单出售的usdt数量',
  `ad_buy_on_total` int(11) DEFAULT '0' COMMENT '求购笔数，承兑商挂买单数量',
  `order_buy_amount` decimal(20,8) DEFAULT '0.00000000' COMMENT '求购总数量，挂单购买的总usdt数量',
  `create_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

/*Data for the table `think_statistics` */

/*Table structure for table `think_trader_reward` */

DROP TABLE IF EXISTS `think_trader_reward`;

CREATE TABLE `think_trader_reward` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `duid` int(11) DEFAULT NULL,
  `amount` decimal(20,8) DEFAULT '0.00000000',
  `type` tinyint(4) DEFAULT '0' COMMENT '0:商户提币，1：用户提币，2：用户充值',
  `create_time` int(11) DEFAULT NULL,
  `orderid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `think_trader_reward` */

/*Table structure for table `think_user` */

DROP TABLE IF EXISTS `think_user`;

CREATE TABLE `think_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `account` varchar(20) DEFAULT NULL COMMENT '认证的手机号码',
  `nickname` varchar(32) DEFAULT NULL COMMENT '昵称',
  `password` char(32) DEFAULT NULL,
  `head_img` varchar(255) DEFAULT NULL COMMENT '头像',
  `status` tinyint(1) DEFAULT NULL COMMENT '1激活  0 未激活',
  `token` varchar(255) DEFAULT '0' COMMENT '令牌',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

/*Data for the table `think_user` */

insert  into `think_user`(`id`,`account`,`nickname`,`password`,`head_img`,`status`,`token`) values 
(1,'13800138000','13800138000','e10adc3949ba59abbe56e057f20f883e','',1,'LWBYIiLWinNiulNXYD1UzGgfynNx+gy/zmq5Ega0E0we4a0WyB8UaG4x+VKRoc9CG4e1BXrqZww='),
(2,'18993075721','7245','e10adc3949ba59abbe56e057f20f883e','',1,'VslU7gKYuddZFPq4ssWLZCNYBsi3YQIicyG1jm5pUfvZHI4qw03b3A2sygA4efLyWHRkYBQX8LAscwsA7sLzhg=='),
(3,'15095340657','45245','e10adc3949ba59abbe56e057f20f883e','',1,'2d8471d156a9e6db155145571cedea5a');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
