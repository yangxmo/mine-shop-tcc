CREATE TABLE `tcc_coupon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT '',
  `fee` decimal(11,2) DEFAULT '0.00',
  `status` tinyint(1) DEFAULT '0',
  `lock` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='优惠券';

CREATE TABLE `tcc_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `price` decimal(11,2) DEFAULT '0.00',
  `name` varchar(255) DEFAULT '',
  `num` int(11) unsigned DEFAULT '0',
  `lock` int(11) unsigned DEFAULT '0',
  `sale` int(11) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COMMENT='商品表';

CREATE TABLE `tcc_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_sn` varchar(80) DEFAULT '',
  `body` varchar(255) DEFAULT '',
  `total_fee` decimal(11,2) DEFAULT '0.00',
  `pay_fee` decimal(11,2) DEFAULT '0.00',
  `goods_id` int(11) DEFAULT '0',
  `coupon_id` int(11) DEFAULT '0',
  `sub_fee` decimal(11,2) DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=715 DEFAULT CHARSET=utf8mb4 COMMENT='订单';

CREATE TABLE `tcc_order_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT '0',
  `message` varchar(255) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=625 DEFAULT CHARSET=utf8mb4 COMMENT='订单消息';

CREATE TABLE `tcc_order_statistics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_num` int(11) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='订单统计表';

INSERT INTO `tcc_order_statistics` (`id`, `order_num`) VALUES ('1', '0');
INSERT INTO `tcc_goods` (`id`, `price`, `name`, `num`, `lock`, `sale`) VALUES ('1', '100.00', '桃子', '100', '0', '0');
INSERT INTO `tcc_goods` (`id`, `price`, `name`, `num`, `lock`, `sale`) VALUES ('2', '100.00', '苹果', '100', '0', '0');
INSERT INTO `tcc_coupon` (`id`, `name`, `fee`, `status`, `lock`) VALUES ('1', '满100减50', '50.00', '0', '0');
INSERT INTO `tcc_coupon` (`id`, `name`, `fee`, `status`, `lock`) VALUES ('2', '满100减50', '50.00', '0', '0');
INSERT INTO `tcc_coupon` (`id`, `name`, `fee`, `status`, `lock`) VALUES ('3', '满100减50', '50.00', '0', '0');
INSERT INTO `tcc_coupon` (`id`, `name`, `fee`, `status`, `lock`) VALUES ('4', '满100减50', '50.00', '0', '0');
INSERT INTO `tcc_coupon` (`id`, `name`, `fee`, `status`, `lock`) VALUES ('5', '满100减50', '50.00', '0', '0');
INSERT INTO `tcc_coupon` (`id`, `name`, `fee`, `status`, `lock`) VALUES ('6', '满100减50', '50.00', '0', '0');
INSERT INTO `tcc_coupon` (`id`, `name`, `fee`, `status`, `lock`) VALUES ('7', '满100减50', '50.00', '0', '0');
INSERT INTO `tcc_coupon` (`id`, `name`, `fee`, `status`, `lock`) VALUES ('8', '满100减50', '50.00', '0', '0');

