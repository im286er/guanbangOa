/*
Navicat MySQL Data Transfer

Source Server         : 本地3308
Source Server Version : 50617
Source Host           : localhost:3308
Source Database       : topnt2016

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2017-03-15 11:05:36
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `oa_interest_type`
-- ----------------------------
DROP TABLE IF EXISTS `oa_interest_type`;
CREATE TABLE `oa_interest_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` varchar(18) DEFAULT NULL,
  `type_name` varchar(32) DEFAULT NULL,
  `feature` text,
  `preference` text,
  `file` varchar(128) DEFAULT NULL,
  `path_name` varchar(128) DEFAULT NULL,
  `grade_id` int(11) DEFAULT NULL,
  `timestamp` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of oa_interest_type
-- ----------------------------
INSERT INTO `oa_interest_type` VALUES ('26', 'a1476345655', '自然23', '是2343', '刚23', null, null, '11', '1489506456');
INSERT INTO `oa_interest_type` VALUES ('36', 'a1476345655', '试试', '收到', '收到', null, null, '12', '1489506552');
INSERT INTO `oa_interest_type` VALUES ('40', 'a1476345655', '天真', '三', '原', null, null, '10', '1489506587');
INSERT INTO `oa_interest_type` VALUES ('52', 'a1476345655', '222', 'www ', 'wew', null, null, '10', '1489508440');
INSERT INTO `oa_interest_type` VALUES ('53', 'a1476345655', '55', '55', '66', null, null, '10', '1489510543');
INSERT INTO `oa_interest_type` VALUES ('54', 'a1476345655', '66', '66', '66', '/oa/uploadfiles/attachment/a1476345655/2017031501011597204fbb3.pdf', null, '10', '1489510875');
INSERT INTO `oa_interest_type` VALUES ('55', 'a1476345655', '66', '66', '66', '/oa/uploadfiles/attachment/a1476345655/20170315010115276584a47.pdf', null, '10', '1489510875');
INSERT INTO `oa_interest_type` VALUES ('59', 'a1476345655', '实用型2', '特征', '篮球2', '/oa/uploadfiles/attachment/a1476345655/2017031501182969202830b.pdf', '下载奖金计划PDF文件(英文).pdf', '13', '1489512085');
INSERT INTO `oa_interest_type` VALUES ('60', 'a1476345655', '通知2', '问问，山东省‘’是', '收到', '/oa/uploadfiles/attachment/a1476345655/2017031501182969202830b.pdf', '下载奖金计划PDF文件(英文).pdf', '13', '1489512085');
INSERT INTO `oa_interest_type` VALUES ('61', 'a1476345655', '12121', '山东省，，你', '适当123', '/oa/uploadfiles/attachment/a1476345655/2017031501182969202830b.pdf', '下载奖金计划PDF文件(英文).pdf', '13', '1489512085');
