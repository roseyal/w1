# Host: localhost  (Version: 5.5.53)
# Date: 2018-01-18 20:36:57
# Generator: MySQL-Front 5.3  (Build 4.234)

/*!40101 SET NAMES utf8 */;

#
# Structure for table "ih_goods"
#

DROP TABLE IF EXISTS `ih_goods`;
CREATE TABLE `ih_goods` (
  `goods_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cat_id` int(11) NOT NULL,
  `goods_name` varchar(255) NOT NULL,
  PRIMARY KEY (`goods_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

#
# Structure for table "ih_log"
#

DROP TABLE IF EXISTS `ih_log`;
CREATE TABLE `ih_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event` varchar(255) NOT NULL DEFAULT '' COMMENT '返回事件',
  `type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '状态码',
  `addtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=463 DEFAULT CHARSET=utf8 COMMENT='记录日志';

#
# Structure for table "ih_order"
#

DROP TABLE IF EXISTS `ih_order`;
CREATE TABLE `ih_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_sn` char(32) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '状态码',
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '购买数量',
  `sku_id` int(11) NOT NULL DEFAULT '0' COMMENT '产品id',
  `price` float DEFAULT NULL COMMENT '产品价格',
  `addtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单表';

#
# Structure for table "ih_store"
#

DROP TABLE IF EXISTS `ih_store`;
CREATE TABLE `ih_store` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) NOT NULL,
  `sku_id` int(10) unsigned NOT NULL DEFAULT '0',
  `number` int(10) NOT NULL DEFAULT '0',
  `freez` int(11) NOT NULL DEFAULT '0' COMMENT '虚拟库存',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='库存';
