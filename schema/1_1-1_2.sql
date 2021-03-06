/* 
 * ******************************************************************************
 * *  Copyright 2011-12 DANTE Ltd. on behalf of the GN3 consortium
 * ******************************************************************************
 * *  License: see the LICENSE file in the root directory of this release
 * ******************************************************************************
 */
/**
 * Author:  swinter
 * Created: 17.12.2015
 */
INSERT INTO `profile_option_dict` VALUES 
('fed:realname','a friendly display name of the NRO/federation','string', 'ML'),
('fed:logo_file','logo of the NRO/federation','file', NULL),
('fed:css_file','custom CSS to be applied on any skin','file',NULL),
('fed:custominvite','custom text to send with new IdP invitations','text', NULL),
('fed:desired_skin','UI skin to use - if not exist, fall back to default','string',NULL),
('fed:silverbullet','enable Silver Bullet in this federation','boolean',NULL),
('fed:silverbullet-noterm','to tell us we should not terminate EAP for this federation silverbullet','boolean',NULL),
('fed:silverbullet-maxusers','maximum number of users per silverbullet profile','integer',NULL);

CREATE TABLE `federation` (
  `federation_id` varchar(16) NOT NULL,
  `last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`federation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `federation_option` (
  `federation_id` varchar(16) NOT NULL DEFAULT 'DEFAULT',
  `option_name` varchar(32) DEFAULT NULL,
  `option_value` longblob,
  `row` int(11) NOT NULL AUTO_INCREMENT,
  KEY `option_name` (`option_name`),
  KEY `rowindex` (`row`),
  CONSTRAINT `federation_option_ibfk_1` FOREIGN KEY (`option_name`) REFERENCES `profile_option_dict` (`name`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `profile` ADD COLUMN `status_dns` int(11) DEFAULT NULL;
ALTER TABLE `profile` ADD COLUMN `status_cert` int(11) DEFAULT NULL;
ALTER TABLE `profile` ADD COLUMN `status_reachability` int(11) DEFAULT NULL;
ALTER TABLE `profile` ADD COLUMN `status_TLS` int(11) DEFAULT NULL;
ALTER TABLE `profile` ADD COLUMN `last_status_check` timestamp NULL DEFAULT NULL;
ALTER TABLE `profile` ADD COLUMN `checkuser_outer` int(1) NOT NULL DEFAULT '0';
ALTER TABLE `profile` ADD COLUMN `checkuser_value` varchar(128) DEFAULT NULL;
ALTER TABLE `profile` ADD COLUMN `verify_userinput_suffix` int(1) NOT NULL DEFAULT '0';
ALTER TABLE `profile` ADD COLUMN `hint_userinput_suffix` int(1) NOT NULL DEFAULT '0';
