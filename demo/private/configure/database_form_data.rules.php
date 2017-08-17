<?php
return array(
	'database' => 'test',
	'tableName' => 'database_form_data',
	'rules' =>
		array(
			'end' =>
				array(
					'id' => '1',
					'form_id' => '1',
					'form_uniqid' => '1',
					'form_value' => '1',
					'status' => '1',
					'add_time' => '1',
					'update_time' => '1',
				),
			'front' =>
				array(
					'id' => '1',
					'form_id' => '1',
					'form_uniqid' => '1',
					'form_value' => '1',
					'status' => '1',
					'add_time' => '1',
					'update_time' => '1',
				),
			'alias' =>
				array(
					'id' => 'id',
					'form_id' => 'form_id',
					'form_uniqid' => 'form_uniqid',
					'form_value' => 'form_value',
					'status' => 'status',
					'add_time' => 'add_time',
					'update_time' => 'update_time',
				),
			'fields' =>
				array(
					0 => 'id',
					1 => 'form_id',
					2 => 'form_uniqid',
					3 => 'form_value',
					4 => 'status',
					5 => 'add_time',
					6 => 'update_time',
				),
			'length' =>
				array(
					'id' => '11',
					'form_id' => '11',
					'form_uniqid' => '41',
					'form_value' => '0',
					'status' => '1',
					'add_time' => '11',
					'update_time' => '11',
				),
			'type' =>
				array(
					'id' => 'int',
					'form_id' => 'int',
					'form_uniqid' => 'char',
					'form_value' => 'text',
					'status' => 'tinyint',
					'add_time' => 'int',
					'update_time' => 'int',
				),
			'null' =>
				array(
					'id' => 'no',
					'form_id' => 'no',
					'form_uniqid' => 'no',
					'form_value' => 'no',
					'status' => 'no',
					'add_time' => 'yes',
					'update_time' => 'yes',
				),
			'pri' =>
				array(
					'id' => '1',
				),
			'validate' =>
				array(
					'form_id' =>
						array(
							0 => 'required',
						),
					'form_uniqid' =>
						array(
							0 => 'required',
						),
					'form_value' =>
						array(
							0 => 'required',
						),
				),
			'default' =>
				array(
					'id' => '',
					'form_id' => '',
					'form_uniqid' => '',
					'form_value' => '',
					'status' => '1',
					'add_time' => '',
					'update_time' => '',
				),
			'save' =>
				array(
					'form_id' => '1',
					'form_uniqid' => '1',
					'form_value' => '1',
				),
			'update' =>
				array(
					'form_id' => '1',
					'form_uniqid' => '1',
				),
			'remove' =>
				array(
					'form_id' => '1',
					'form_uniqid' => '1',
				),
		),
	'table' => 'database_form_data',
	'add_time' => '1454482716',
	'update_time' => '1454482721',
	'sql' => 'CREATE TABLE IF NOT EXISTS `database_form_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `form_id` int(11) NOT NULL,
  `form_uniqid` char(41) NOT NULL,
  `form_value` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT \\\'1\\\',
  `add_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;',
);