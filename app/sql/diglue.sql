-- diglue database sb0
-- ver 0.0.7
-- Since:       2016/10/18
--
-- Last UPDATE: 2017/01/16
-- cntent_data
--      add `yomigana` varchar(256) default NULL
-- UPDATE:      2017/01/12
-- user_checklist
--      add `origin` boolean NOT NULL DEFAULT FALSE
--      add `hidden` boolean NOT NULL DEFAULT FALSE
-- UPDATE:      2017/01/10
-- content_data
--      add `spell` varchar(256) DEFAULT NULL
--      modify content_name varchar(255) NOT NULL
--      add unique(content_name)
--      modify disamb boolean NOT NULL DEFAULT FALSE
--      modify rlsdate datetime DEFAULT NULL
--      alter usercount set DEFAULT 0
--
-- UPDATE:      2016/12/01
-- user_checklist
--      add `user_comment` varchar(256) DEFAULT NULL,
--      add `user_image` varchar(64) NOT NULL DEFAULT 'noimage.png',
--
-- UPDATE:      2016/11/29
--  user_checklist
--      add KEY `content_num` (`content_num`,`user_num`)
--      alter genre set default 'default'
--  post_data
--      alter genre set default 'default']
--
-- UPDATE:      2016/11/28
--  content_data
--      add UNIQUE KEY `content_hash`(`content_hash`,`genre`,`category`)
--      modify content_comment null
--  user_checklist
--      add `content_name` varchar(256)
--      add `genre` varchar(32) not null default '不明'
--
-- UPDATE:      2016/11/25
--  follow_list
--      add `blocked` boolean not null default false
--  content_data
--      add `category` varchar(32) not null default '不明'

CREATE TABLE IF NOT EXISTS `user_data`(
	`user_num` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`user_hash` varchar(32) NOT NULL,
	`user_id` varchar(32) NOT NULL,
	`user_pass` varchar(256) NOT NULL,
	`user_name` varchar(64) NOT NULL,
	`user_icon` varchar(64) NOT NULL DEFAULT 'noicon.png',
	`user_comment` varchar(2048),
	`mail_address` varchar(255) DEFAULT '_@hogehoge.com',
	`favgenres` varchar(2048) NOT NULL DEFAULT '#_STREAM',
	`hidden` boolean NOT NULL DEFAULT FALSE,
	`regdate` datetime NOT NULL,
	`moddate` datetime NOT NULL,
    `enable` boolean NOT NULL DEFAULT TRUE,
	PRIMARY KEY (`user_num`),
	UNIQUE KEY `user_id` (`user_id`),
	UNIQUE KEY `user_hash` (`user_hash`),
	UNIQUE KEY `mail_address` (`mail_address`)
) ENGINE=InnoDB DEFAULT CHARSET=UTF8;


CREATE TABLE IF NOT EXISTS `user_checklist`(
	`check_num` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`content_num` int(11) unsigned NOT NULL,
    `content_name` varchar(256) NOT NULL,
	`user_num` int(11) unsigned NOT NULL,
    `user_comment` varchar(256) DEFAULT NULL,
    `user_ref` varchar(2083) DEFAULT NULL,
    `user_image` varchar(64) NOT NULL DEFAULT 'noimage.png',
    `genre` varchar(32) NOT NULL DEFAULT 'default',
	`favorite` boolean NOT NULL DEFAULT FALSE,
    `origin` boolean NOT NULL DEFAULT FALSE,
	`active` boolean NOT NULL DEFAULT TRUE,
    `hidden` boolean NOT NULL DEFAULT FALSE,
	`regdate` datetime NOT NULL,
	PRIMARY KEY `check_num` (`check_num`),
    UNIQUE KEY `content_num` (`content_num`,`user_num`)
) ENGINE=InnoDB DEFAULT CHARSET=UTF8;

CREATE TABLE IF NOT EXISTS `user_login_log`(
    `log_num` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `user_num` int(11) unsigned NOT NULL,
    `user_id` varchar(32) NOT NULL,
    `remote_addr` varchar(40) NOT NULL,
    `logindate` datetime NOT NULL,
    PRIMARY KEY `log_num` (`log_num`)

)ENGINE=InnoDB DEFAULT CHARSET=UTF8;

CREATE TABLE IF NOT EXISTS `post_data`(
	`post_num` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`user_num` int(11) unsigned NOT NULL,
	`user_id` varchar(32) NOT NULL,
	-- `user_name`varchar(64) NOT NULL DEFAULT 'nanashi',
	`content_name` varchar(256) NOT NULL,
	`reference_url` varchar(2083),
	`post_comment` varchar(20),
	`mygenre` varchar(32),
	-- `tags` varchar(2048),
	`post_image_name` varchar(64),
	`scope` boolean NOT NULL DEFAULT FALSE, -- フォロワーのみに公開のオンオフ
	`hidden_user` boolean NOT NULL DEFAULT FALSE, -- 鍵アカのオンオフ
	`regdate` datetime NOT NULL,
	`moddate` datetime NOT NULL,
	PRIMARY KEY `post_num` (`post_num`)
) ENGINE=InnoDB DEFAULT CHARSET=UTF8;


CREATE TABLE IF NOT EXISTS `content_data`(
	`content_num` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`content_name` varchar(255) NOT NULL,  --全て小文字,スペースあり
    `spell` varchar(256) DEFAULT NULL, --表記
    `yomigana` varchar(256) DEFAULT NULL,
	`content_comment` varchar(256) NOT NULL, -- htmlへのリンク
	`official_ref` varchar(2083), -- 公式サイト以外はcommentの中に埋め込む
	`content_image` varchar(640) NOT NULL DEFAULT '#noimage.png',
	`usercount` int(11) unsigned NOT NULL DEFAULT 0,
	`genre` varchar(32) NOT NULL DEFAULT '不明',
    `category` varchar(32) NOT NULL DEFAULT '不明',
	`rlsdate` datetime DEFAULT NULL,
	`regdate` datetime NOT NULL,
	`moddate` datetime NOT NULL,
	`mod_comment` varchar(2083),
	`exist_same_name` boolean NOT NULL DEFAULT FALSE,
	`disamb` boolean NOT NULL DEFAULT FALSE, -- 曖昧さ回避ページか否か
	PRIMARY KEY `content_num` (`content_num`),
    UNIQUE KEY `content_name` (`content_name`),
    UNIQUE KEY `content_hash` (`content_hash`,`genre`,`category`)
) ENGINE=InnoDB DEFAULT CHARSET=UTF8;


CREATE TABLE IF NOT EXISTS `content_image_data`(
	`content_image_num` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`content_num` int(11) unsigned NOT NULL,
	`content_name` varchar(256) NOT NULL,
	`content_image_name` varchar(64) NOT NULL,
	`placement` int NOT NULL, -- wikiページにおける配置順`
	PRIMARY KEY `content_image_num` (`content_image_num`)
)ENGINE=InnoDB DEFAULT CHARSET=UTF8;

CREATE TABLE IF NOT EXISTS `follow_list`(
	`list_num` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`user_followed_by` int(11) unsigned NOT NULL, -- フォローされたユーザ
	`user_follow_to` int(11) unsigned NOT NULL,	-- フォローしたユーザ
	`active` boolean NOT NULL DEFAULT TRUE,	-- フォロー解除・再フォロー用
	`block` boolean NOT NULL DEFAULT FALSE, -- ブロック
    `blocked` boolean NOT NULL DEFAULT FALSE, --被ブロック
	`regdate` datetime NOT NULL,
	`moddate` datetime NOT NULL,
	PRIMARY KEY `list_num` (`list_num`),
	UNIQUE KEY (`user_followed_by`,`user_follow_to`)
)ENGINE=InnoDB DEFAULT CHARSET=UTF8;


CREATE TABLE IF NOT EXISTS `auto_login`(
    `log_num` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `login_key` varchar(255),
    `user_num` int(11),
    `limitdate` datetime NOT NULL,
    PRIMARY KEY (`log_num`)
 )ENGINE=InnoDB DEFAULT CHARSET=UTF8;
 
