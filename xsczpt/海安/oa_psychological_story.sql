/*
Navicat MySQL Data Transfer

Source Server         : 本地3308
Source Server Version : 50617
Source Host           : localhost:3308
Source Database       : topnt2016

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2017-03-15 11:04:38
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `oa_psychological_story`
-- ----------------------------
DROP TABLE IF EXISTS `oa_psychological_story`;
CREATE TABLE `oa_psychological_story` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` varchar(18) DEFAULT NULL,
  `title` varchar(32) DEFAULT NULL,
  `content` text,
  `grade_id` int(11) DEFAULT NULL,
  `timestamp` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of oa_psychological_story
-- ----------------------------
INSERT INTO `oa_psychological_story` VALUES ('1', 'a1476345655', '测试1', '测试1测试1测试1，，‘’&#039;ssss&#039;', '12', '1489545798');
INSERT INTO `oa_psychological_story` VALUES ('2', 'a1476345655', 'ceshi333', 'wewewewew333', '12', '1489546749');
