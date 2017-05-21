/*
Navicat MySQL Data Transfer

Source Server         : 本地3308
Source Server Version : 50617
Source Host           : localhost:3308
Source Database       : topnt2016

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2017-03-15 11:06:13
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `oa_career_story`
-- ----------------------------
DROP TABLE IF EXISTS `oa_career_story`;
CREATE TABLE `oa_career_story` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` varchar(18) DEFAULT NULL,
  `title` varchar(32) DEFAULT NULL,
  `content` text,
  `grade_id` int(11) DEFAULT NULL,
  `timestamp` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of oa_career_story
-- ----------------------------
INSERT INTO `oa_career_story` VALUES ('1', 'a1476345655', '标题1333', '标题1标题1标题1标题1标题1标题1标题1标题1标题1标题1标题1标题1标题1标题1标题1标题1标题1标题1标题1标题1标题1标题1', '12', '1489504360');
INSERT INTO `oa_career_story` VALUES ('2', 'a1476345655', '问问', '问问我', '12', '1489488388');
INSERT INTO `oa_career_story` VALUES ('3', 'a1476345655', '问问下方', '问问我v出错vg55', '12', '1489504384');
INSERT INTO `oa_career_story` VALUES ('4', 'a1476345655', '问呜呜呜呜', 'swww', '11', '1489506860');
