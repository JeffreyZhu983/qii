<?php
return array(
	'database' => 'test',
	'tableName' => 'database_table_rules',
	'rules' => array(
		'length' => array(
			'database' => '20',
			'table' => '20',
			'rules' => '0',
			'add_time' => '10',
			'update_time' => '10'
		),
		'type' => array(
			'database' => 'varchar',
			'table' => 'varchar',
			'rules' => 'text',
			'add_time' => 'int',
			'update_time' => 'int'
		),
		'fields' => array(
			0 => 'database',
			1 => 'table',
			2 => 'rules',
			3 => 'add_time',
			4 => 'update_time'
		),
		'pri' => array(
			'database' => '1',
			'table' => '1'
		),
		'alias' => array(
			'database' => '数据库名',
			'table' => '数据表',
			'rules' => '规则',
			'add_time' => '添加时间',
			'update_time' => '更新时间'
		),
		'front' => array(
			'database' => '1',
			'table' => '1',
			'rules' => '1',
			'add_time' => '1',
			'update_time' => '1'
		),
		'end' => array(
			'database' => '1',
			'table' => '1',
			'rules' => '1',
			'add_time' => '1',
			'update_time' => '1'
		),
		'default' => array(
			'database' => '',
			'table' => '',
			'rules' => '',
			'add_time' => '',
			'update_time' => ''
		),
		'validate' => array(
			'database' => array(
				0 => 'required',
				1 => 'string',
			),
			'table' => array(
				0 => 'required',
				1 => 'string'
			),
			'rules' => array(
				0 => 'required'
			)
		),
		'update' => array(
			'database' => '1',
			'table' => '1',
			'rules' => '1'
		),
		'save' => array(
			'database' => '1',
			'table' => '1',
			'rules' => '1'
		),
		'remove' => array(
			'database' => '1',
			'table' => '1'
		),
		'null' => array(
			'database' => 'no',
			'table' => 'no',
			'rules' => 'no',
			'add_time' => 'yes',
			'update_time' => 'yes'
		)
	),
	'add_time' => '1442888805',
	'update_time' => '1451032338',
	'sql' => 'CREATE TABLE IF NOT EXISTS `database_table_rules` (
              `database` varchar(50) NOT NULL,
              `table` varchar(50) NOT NULL,
              `rules` text NOT NULL,
              `add_time` int(10) DEFAULT NULL,
              `update_time` int(10) DEFAULT NULL,
              PRIMARY KEY (`database`,`table`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;'
);