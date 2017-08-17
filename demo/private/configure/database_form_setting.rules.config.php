<?php
return array(
	'database' => 'test',
	'tableName' => 'database_form_setting',
	'rules' =>
		array(
			'end' =>
				array(
					'id' => '1',
					'uid' => '1',
					'uniqid' => '1',
					'mask' => '1',
					'form' => '1',
					'form_serialize' => '1',
					'status' => '1',
					'add_time' => '1',
					'update_time' => '1',
				),
			'front' =>
				array(
					'id' => '1',
					'uid' => '1',
					'uniqid' => '1',
					'mask' => '1',
					'form' => '1',
					'form_serialize' => '1',
					'status' => '1',
					'add_time' => '1',
					'update_time' => '1',
				),
			'alias' =>
				array(
					'id' => 'id',
					'uid' => 'uid',
					'uniqid' => 'uniqid',
					'mask' => '',
					'form' => 'form',
					'form_serialize' => 'form_serialize',
					'status' => 'status',
					'add_time' => 'add_time',
					'update_time' => 'update_time',
				),
			'fields' =>
				array(
					0 => 'id',
					1 => 'uid',
					2 => 'uniqid',
					3 => 'mask',
					4 => 'form',
					5 => 'form_serialize',
					6 => 'status',
					7 => 'add_time',
					8 => 'update_time',
				),
			'length' =>
				array(
					'id' => '11',
					'uid' => '11',
					'uniqid' => '41',
					'mask' => '32',
					'form' => '0',
					'form_serialize' => '0',
					'status' => '1',
					'add_time' => '11',
					'update_time' => '11',
				),
			'type' =>
				array(
					'id' => 'int',
					'uid' => 'int',
					'uniqid' => 'char',
					'mask' => 'char',
					'form' => 'text',
					'form_serialize' => 'text',
					'status' => 'tinyint',
					'add_time' => 'int',
					'update_time' => 'int',
				),
			'null' =>
				array(
					'id' => 'no',
					'uid' => 'yes',
					'uniqid' => 'no',
					'mask' => 'no',
					'form' => 'yes',
					'form_serialize' => 'yes',
					'status' => 'no',
					'add_time' => 'yes',
					'update_time' => 'yes',
				),
			'pri' =>
				array(
					'id' => '1',
				),
			'uni' =>
				array(
					'uniqid' => '1',
					'mask' => '1',
				),
			'validate' =>
				array(
					'uniqid' =>
						array(
							0 => 'required',
						),
					'form' =>
						array(
							0 => 'required',
						),
					'form_serialize' =>
						array(
							0 => 'required',
						),
				),
			'default' =>
				array(
					'id' => '',
					'uid' => '',
					'uniqid' => '',
					'mask' => '',
					'form' => '',
					'form_serialize' => '',
					'status' => '1',
					'add_time' => '',
					'update_time' => '',
				),
			'update' =>
				array(
					'id' => '1',
					'uniqid' => '1',
					'mask' => '1',
					'form' => '1',
					'form_serialize' => '1',
				),
			'remove' =>
				array(
					'id' => '1',
					'uniqid' => '1',
				),
			'save' =>
				array(
					'uniqid' => '1',
					'mask' => '1',
					'form' => '1',
					'form_serialize' => '1',
				),
		),
	'table' => 'database_form_setting',
	'add_time' => '1454470879',
	'update_time' => '1454471891',
	'sql' => 'CREATE TABLE IF NOT EXISTS `database_form_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `uniqid` char(41) NOT NULL,
  `mask` char(32) NOT NULL,
  `form` text,
  `form_serialize` text,
  `status` tinyint(1) NOT NULL DEFAULT \\\'1\\\',
  `add_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniqid_UNIQUE` (`uniqid`),
  UNIQUE KEY `mask_UNIQUE` (`mask`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;',
);