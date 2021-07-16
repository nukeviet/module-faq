<?php

/**
 * NukeViet Content Management System
 * @version 4.x
 * @author VINADES.,JSC <contact@vinades.vn>
 * @copyright (C) 2009-2021 VINADES.,JSC. All rights reserved
 * @license GNU/GPL version 2 or any later version
 * @see https://github.com/nukeviet The NukeViet CMS GitHub project
 */

if (!defined('NV_IS_FILE_MODULES')) {
    exit('Stop!!!');
}

//ALTER TABLE`nv4_vi_faq_categories DROP who_view;

$sql_drop_module = [];

$sql_drop_module[] = 'DROP TABLE IF EXISTS ' . $db_config['prefix'] . '_' . $lang . '_' . $module_data . '';
$sql_drop_module[] = 'DROP TABLE IF EXISTS ' . $db_config['prefix'] . '_' . $lang . '_' . $module_data . '_categories';
$sql_drop_module[] = 'DROP TABLE IF EXISTS ' . $db_config['prefix'] . '_' . $lang . '_' . $module_data . '_config';

$sql_create_module = $sql_drop_module;

$sql_create_module[] = 'CREATE TABLE IF NOT EXISTS ' . $db_config['prefix'] . '_' . $lang . '_' . $module_data . " (
	`id` MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	`catid` MEDIUMINT(8) UNSIGNED NOT NULL,
	`title` VARCHAR(250) NOT NULL,
	`alias` VARCHAR(250) NOT NULL,
	`question` MEDIUMTEXT NOT NULL,
	`answer` MEDIUMTEXT NOT NULL,
	`weight` SMALLINT(4) UNSIGNED NOT NULL DEFAULT '0',
	`status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`addtime` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	UNIQUE INDEX `alias` (`alias`(191)),
	INDEX `catid` (`catid`)
)";

$sql_create_module[] = 'CREATE TABLE IF NOT EXISTS ' . $db_config['prefix'] . '_' . $lang . '_' . $module_data . "_categories (
	`id` MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	`parentid` MEDIUMINT(8) UNSIGNED NOT NULL,
	`title` VARCHAR(250) NOT NULL,
	`alias` VARCHAR(250) NOT NULL,
	`description` MEDIUMTEXT NOT NULL,
	`groups_view` VARCHAR(255) NOT NULL,
	`weight` SMALLINT(4) UNSIGNED NOT NULL DEFAULT '0',
	`status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`keywords` MEDIUMTEXT NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `alias` (`alias`(191))
)";

// Config
$sql_create_module[] = 'CREATE TABLE IF NOT EXISTS ' . $db_config['prefix'] . '_' . $lang . '_' . $module_data . "_config (
	`config_name` VARCHAR(50) NOT NULL,
	`config_value` VARCHAR(255) NOT NULL,
	UNIQUE INDEX `config_name` (`config_name`)
)";

$sql_create_module[] = 'INSERT INTO ' . $db_config['prefix'] . '_' . $lang . '_' . $module_data . "_config VALUES
('per_page', '30'),
('per_cat', '5')";
