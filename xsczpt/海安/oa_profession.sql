/*
Navicat MySQL Data Transfer

Source Server         : 本地3308
Source Server Version : 50617
Source Host           : localhost:3308
Source Database       : topnt2016

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2017-03-15 11:04:24
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `oa_profession`
-- ----------------------------
DROP TABLE IF EXISTS `oa_profession`;
CREATE TABLE `oa_profession` (
  `id` varchar(11) NOT NULL,
  `uid` varchar(18) DEFAULT NULL,
  `name` varchar(32) DEFAULT NULL,
  `pid` varchar(11) DEFAULT NULL,
  `pid_name` varchar(32) DEFAULT NULL,
  `grade_id` int(11) DEFAULT NULL,
  `intro` text,
  `timestamp` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of oa_profession
-- ----------------------------
INSERT INTO `oa_profession` VALUES ('010101', 'a1476345655', '基础数学', '0101', '数学', '12', null, '1489423328');
INSERT INTO `oa_profession` VALUES ('010102', 'a1476345655', '数论', '0101', '数学', '12', null, '1489474369');
INSERT INTO `oa_profession` VALUES ('020101', 'a1476345655', '基础金融', '0201', '金融', '13', null, '1489474439');
INSERT INTO `oa_profession` VALUES ('020102', 'a1476345655', '会计', '0201', '金融学', '13', '山东省', '1489488305');
INSERT INTO `oa_profession` VALUES ('020103', 'a1476345655', '会计22', '0201', '金融学2', '13', '位2', '1489504941');
