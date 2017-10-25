/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50554
Source Host           : localhost:3306
Source Database       : fab

Target Server Type    : MYSQL
Target Server Version : 50554
File Encoding         : 65001

Date: 2017-08-21 17:42:55
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for `h_role`
-- ----------------------------
DROP TABLE IF EXISTS `h_role`;
CREATE TABLE `h_role` (
  `roleid` int(11) NOT NULL AUTO_INCREMENT,
  `rolename` varchar(50) NOT NULL,
  `remark` varchar(500) NOT NULL,
  `su` int(2) NOT NULL DEFAULT '0',
  `upid` varchar(100) NOT NULL DEFAULT '0' COMMENT '角色上级ID',
  PRIMARY KEY (`roleid`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of h_role
-- ----------------------------
INSERT INTO `h_role` VALUES ('1', '超级管理员', '超级管理员', '1', '0');

-- ----------------------------
-- Table structure for `h_role_auth`
-- ----------------------------
DROP TABLE IF EXISTS `h_role_auth`;
CREATE TABLE `h_role_auth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roleid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '角色表ID',
  `menu` varchar(100) NOT NULL DEFAULT '' COMMENT '菜单表ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=318 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of h_role_auth
-- ----------------------------

-- ----------------------------
-- Table structure for `h_user`
-- ----------------------------
DROP TABLE IF EXISTS `h_user`;
CREATE TABLE `h_user` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `path` varchar(200) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `point` int(11) NOT NULL DEFAULT '0',
  `money` int(11) NOT NULL DEFAULT '0',
  `inventory` int(11) NOT NULL DEFAULT '0',
  `salt` varchar(30) NOT NULL,
  `roleid` int(11) NOT NULL DEFAULT '3' COMMENT '角色ID',
  `name` varchar(200) NOT NULL,
  `address` varchar(300) NOT NULL,
  `contacts` varchar(30) NOT NULL,
  `mobile` varchar(30) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of h_user
-- ----------------------------
INSERT INTO `h_user` VALUES ('110', '', 'jackie', '5aa40568967019c4e8dfebdc7f3a7d53', '0', '0', '0', '', '1', '', '', '', '', '1');
