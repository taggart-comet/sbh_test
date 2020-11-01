
CREATE DATABASE `sbh_test_blog` DEFAULT CHARACTER SET = `utf8`;

CREATE TABLE IF NOT EXISTS `sbh_test_blog`.`article_main` (
				    `article_id` varchar(300) NOT NULL DEFAULT '',
				    `author_id` int(11) unsigned NOT NULL,
				    `created_at` int(11) unsigned NOT NULL,
				    `article_content` json DEFAULT NULL COMMENT 'Should contain title and text of the article',
				    PRIMARY KEY (`article_id`),
				    KEY `author_id` (`author_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `sbh_test_blog`.`author_main` (
				   `author_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				   `author_name` varchar(200) DEFAULT '',
				   `created_at` int(11) unsigned NOT NULL,
				   `article_count` int(11) unsigned NOT NULL,
				   PRIMARY KEY (`author_id`),
				   KEY `author_name` (`author_name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;