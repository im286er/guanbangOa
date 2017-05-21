/*
Navicat MySQL Data Transfer

Source Server         : 本地3308
Source Server Version : 50617
Source Host           : localhost:3308
Source Database       : topnt2016

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2017-03-15 11:06:04
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `oa_career_plan`
-- ----------------------------
DROP TABLE IF EXISTS `oa_career_plan`;
CREATE TABLE `oa_career_plan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `term_id` int(11) DEFAULT NULL,
  `uid` varchar(18) DEFAULT NULL,
  `type_id` varchar(255) DEFAULT NULL,
  `career_id` varchar(255) DEFAULT NULL,
  `timestamp` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of oa_career_plan
-- ----------------------------
