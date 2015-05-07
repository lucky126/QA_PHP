# -----------------------------------------------------------
# PHP-Amateur database backup files
# Type: 系统自动备份
# Description:当前SQL文件包含了表：bbs_board、bbs_topic、bbs_topicinfo、bbs_user的数据
# Time: 2015-04-21 15:58:48
# -----------------------------------------------------------
# 当前SQL卷标：#1
# -----------------------------------------------------------


# 数据库表：bbs_board 结构信息
DROP TABLE IF EXISTS `bbs_board`;
CREATE TABLE `bbs_board` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `BoardName` varchar(256) CHARACTER SET utf8 NOT NULL,
  `Depth` int(4) NOT NULL,
  `ParentID` int(8) NOT NULL,
  `RootID` int(8) NOT NULL,
  `BoardOrder` int(4) NOT NULL,
  `IsLeaf` int(4) NOT NULL,
  `IsPublic` int(4) NOT NULL,
  `BoardType` int(4) NOT NULL,
  `BoardMaster` varchar(1000) CHARACTER SET utf8 NOT NULL,
  `AddTime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdateTime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `TopicNum` int(8) NOT NULL,
  `PostNum` int(11) NOT NULL,
  `ParentStr` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `BoardId_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;


# 数据库表：bbs_topic 结构信息
DROP TABLE IF EXISTS `bbs_topic`;
CREATE TABLE `bbs_topic` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `BoardId` int(8) NOT NULL,
  `TopicType` int(4) NOT NULL,
  `Title` varchar(256) CHARACTER SET utf8 NOT NULL,
  `Keywords` varchar(256) CHARACTER SET utf8 NOT NULL,
  `PostUserId` int(8) NOT NULL,
  `PostUserName` varchar(50) CHARACTER SET utf8 NOT NULL,
  `PostTime` datetime NOT NULL,
  `UpdateTime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `PostIp` varchar(256) CHARACTER SET utf8 NOT NULL,
  `LastPostUserId` int(8) DEFAULT NULL,
  `LastPostUserName` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `LastPostTime` datetime DEFAULT NULL,
  `TopicStatus` int(4) NOT NULL,
  `IsFinish` int(4) NOT NULL,
  `IsLock` int(4) NOT NULL,
  `IsDigest` int(4) NOT NULL,
  `TopLevel` int(4) NOT NULL,
  `Child` int(8) NOT NULL,
  `Hits` int(8) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TopicId_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=latin1;


# 数据库表：bbs_topicinfo 结构信息
DROP TABLE IF EXISTS `bbs_topicinfo`;
CREATE TABLE `bbs_topicinfo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `TopicId` int(11) NOT NULL,
  `BoardId` int(11) NOT NULL,
  `Content` varchar(5000) CHARACTER SET utf8 NOT NULL,
  `PostUserId` int(11) NOT NULL,
  `PostUserName` varchar(50) CHARACTER SET utf8 NOT NULL,
  `PostTime` datetime NOT NULL,
  `PostIp` varchar(256) CHARACTER SET utf8 NOT NULL,
  `IsTopic` int(11) NOT NULL,
  `IsAnswer` int(11) NOT NULL,
  `DisplayMode` int(11) NOT NULL,
  `AnswerMode` int(11) NOT NULL,
  `BBSStatus` int(11) NOT NULL,
  `AddTime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdateTime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `BBSId_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=latin1;


# 数据库表：bbs_user 结构信息
DROP TABLE IF EXISTS `bbs_user`;
CREATE TABLE `bbs_user` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `UserLevel` int(4) NOT NULL,
  `LoginName` varchar(50) CHARACTER SET utf8 NOT NULL,
  `Password` varchar(50) CHARACTER SET utf8 NOT NULL,
  `NickName` varchar(50) CHARACTER SET utf8 NOT NULL,
  `RealName` varchar(50) CHARACTER SET utf8 NOT NULL,
  `Gender` varchar(10) CHARACTER SET utf8 NOT NULL,
  `UserEmail` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `UserSign` varchar(1000) CHARACTER SET utf8 NOT NULL,
  `UserQuesion` varchar(50) CHARACTER SET utf8 NOT NULL,
  `UserAnswer` varchar(50) CHARACTER SET utf8 NOT NULL,
  `AddTime` datetime NOT NULL,
  `updateTime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `AddIp` varchar(255) CHARACTER SET utf8 NOT NULL,
  `TopicCnt` int(8) NOT NULL,
  `PostCnt` int(8) NOT NULL,
  `DelCnt` int(8) NOT NULL,
  `LoginCnt` int(8) NOT NULL,
  `Grade` int(4) NOT NULL,
  `LastLoginTime` datetime NOT NULL,
  `UserStatus` int(4) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UserId_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
