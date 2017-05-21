/*
Navicat MySQL Data Transfer

Source Server         : 本地3308
Source Server Version : 50617
Source Host           : localhost:3308
Source Database       : topnt2016

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2017-03-15 11:04:51
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `oa_psychological_test`
-- ----------------------------
DROP TABLE IF EXISTS `oa_psychological_test`;
CREATE TABLE `oa_psychological_test` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` varchar(18) DEFAULT NULL,
  `type_name` varchar(32) DEFAULT NULL,
  `feature` text,
  `file` varchar(128) DEFAULT NULL,
  `path_name` varchar(128) DEFAULT NULL,
  `grade_id` int(11) DEFAULT NULL,
  `timestamp` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of oa_psychological_test
-- ----------------------------
INSERT INTO `oa_psychological_test` VALUES ('3', 'a1476345655', '测试12', '测试1测试1测试1测试1测试1测试1测试1测试1测试1测试1测试1测试1测试1测试1测试1测试1测试1测试1测试1测试1测试1测试1测试1测试1测试1测试1测试1测试1测试12', '/oa/uploadfiles/attachment/a1476345655/201703151044523289a0258.pdf', '201703071826_22605_28.pdf', '13', '1489546529');
INSERT INTO `oa_psychological_test` VALUES ('4', 'a1476345655', '测试22', '测试2测试2测试2测试2测试2测试2测试2测试2测试2测试2测试2测试2测试2测试2测试2测试2测试2测试2测试2测试2测试2测试2测试2测试2测试2测试2测试2测试2测试2测试2测试2测试2测试2测试2测试2测试2测试22', '/oa/uploadfiles/attachment/a1476345655/201703151044523289a0258.pdf', '201703071826_22605_28.pdf', '13', '1489546529');
INSERT INTO `oa_psychological_test` VALUES ('5', 'a1476345655', '此处', 'ccc', '', '', '10', '1489546613');
