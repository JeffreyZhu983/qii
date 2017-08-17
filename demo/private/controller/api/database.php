<?php
/**
 * 提供data_controller类使用的接口
 */
namespace controller\api;

use \controller\base;

class database extends base
{
	public function __construct()
	{
		parent::__construct();
		$this->_load->model('table')->checkRuleTable();
	}

	/**
	 * 保存creator
	 */
	public function creatorAction()
	{
		$data = array();
		$database = $this->_request->post('database');
		$tableName = $this->_request->post('tableName');
		$rules = $this->_request->post($tableName);
		if (!$rules) {
			$data['code'] = 1;
			echo $this->Json($data);
			return;
		}
		$result = $this->_load->model('table')->saveRules($database, $tableName, $rules);
		if ($result) {
			$data['code'] = 0;
		} else {
			$data['code'] = 1;
			$data['error'] = $this->_load->model('table')->tablesError;
		}
		$this->echoJson($data);
	}

	/**
	 * 编辑详细的规则
	 */
	public function rulesAction()
	{
		$data = array();
		$database = $this->_request->post('database');
		$tableName = $this->_request->post('tableName');
		$rules = $this->_request->post('rules');
		if (!$rules) {
			$data['code'] = 1;
			echo $this->Json($data);
			return;
		}
		$result = $this->_load->model('table')->updateRules($database, $tableName, $rules);
		if ($result) {
			$data['code'] = 0;
		} else {
			$data['code'] = 1;
			$data['error'] = $this->_load->model('table')->tablesError;
		}
		$this->echoJson($data);
	}

	/**
	 * 获取制定数据库的数据包
	 */
	public function tableListsAction()
	{
		$tableName = $this->_request->get('tableName', '');
		$this->echoJson($this->_load->model('table')->getTableLists($this->_load->model('table')->getUseDB(), $tableName));
	}

	/**
	 * 获取自定表的字段
	 */
	public function fieldsListsAction()
	{
		$tableName = $this->_request->get('tableName');
		$this->echoJson($this->_load->model('table')->getFieldsLists($this->_load->model('table')->getUseDB(), $tableName));
	}

	/**
	 * 删除数据表中的数据
	 */
	public function tableAction()
	{
		$data = array();
		$database = $this->_request->get('__database', $this->_load->model('table')->getUseDB());
		$tableName = $this->_request->get('__tableName', '');
		if (!$database || !$tableName) {
			$data['code'] = 1;
			$data['msg'] = '参数不全';
			echo $this->Json($data);
			return;
		}

		$pri = explode(',', $this->_request->get('__pri'));

		$priVal = array();
		foreach ($pri as $val) {
			if (!$this->_request->keyExists($_GET, $val)) {
				$data['code'] = 1;
				break;
			}
			$priVal[$val] = $this->_request->get($val);
		}
		$result = $this->_load->model('table')->removeTableData($database, $tableName, $priVal);
		if ($result) {
			$data['code'] = 0;
		} else {
			$data['code'] = 1;
			$data['error'] = $this->_load->model('table')->getError() === false ? '删除数据失败' : $this->_load->model('table')->getError();
		}
		$this->echoJson($data);
	}

	/**
	 * 更新数据表中的数据
	 */
	public function updateAction()
	{
		try {

			$data = array();
			$data['code'] = 0;
			$database = $this->_request->post('__database');
			$tableName = $this->_request->post('__tableName');
			$pri = explode(',', $this->_request->post('__pri'));


			$fields = $this->_request->post('fields');
			$priVal = array();
			foreach ($pri as $val) {
				if (!$this->_request->keyExists($_POST, $val)) {
					$data['code'] = 1;
					break;
				}
				$priVal[$val] = $this->_request->post($val);
			}
			if ($data['code'] != 1) {
				$result = $this->_load->model('table')->updateTableData($database, $tableName, $priVal, $fields);
				if (!$result->isError()) {
					$data['code'] = 0;
				} else {
					$data['code'] = 1;
					$data['error'] = $result->getResult();
				}
			}
		} catch (Exception $e) {
			$data['code'] = 1;
			$data['msg'] = $e->getMessage();
		}

		$this->echoJson($data);
	}


	/**
	 * 添加数据表中的数据
	 */
	public function addAction()
	{
		try {

			$data = array();
			$data['code'] = 0;
			$database = $this->_request->post('database');
			$tableName = $this->_request->post('tableName');


			$fields = $this->_request->post('fields');
			if ($data['code'] != 1) {
				$result = $this->_load->model('table')->addTableData($database, $tableName, $fields);
				if ($result->getCode() == 0) {
					$data['code'] = 0;
				} else {
					$data['code'] = 1;
					$data['error'] = $result->getMessage();
				}
			}
		} catch (Exception $e) {
			$data['code'] = 1;
			$data['msg'] = $e->getMessage();
		}

		$this->echoJson($data);
	}

	/**
	 * 下载数据表的配置文件
	 * @author Jinhui Zhu<jinhui.zhu@live.cn> 2015-09-28 11:16
	 */
	public function downloadConfigAction()
	{
		$database = $this->_request->get('database');
		$tableName = $this->_request->get('tableName');
		try {
			if (!$database || !$tableName) {
				$data['code'] = 1;
				$data['msg'] = '参数不全';
				throw new Exception($data['msg'], $data['code']);
			}
			$result = $this->_load->model('table')->autoRules($database, $tableName);
			if ($result) {
				$tableStruct = $this->_load->model('table')->getTableSQL($database, $tableName);
				$result['sql'] = '';
				if ($tableStruct['sql']) $result['sql'] = str_replace("'", "\'", $tableStruct['sql']);
				$downloadStr = "<?php\n\t return " . var_export($result, true) . ';';
				$this->_load->library('download')->downloadByString($database . '.' . $tableName . '.config.php', $downloadStr);
			}
		} catch (Exception $e) {
			$this->showErrorPage($e->getMessage());
		}
	}

	/**
	 * 获取数据表建表URL
	 * @author Jinhui Zhu <jinhui.zhu@live.cn> 2015-09-28 12:03
	 *
	 */
	public function tableSQLAction()
	{
		$database = $this->_request->get('database');
		$tableName = $this->_request->get('tableName');
		$data = array();
		try {
			$data = $this->_load->model('table')->getTableSQL($database, $tableName);
			$data['code'] = 0;
		} catch (Exception $e) {
			$data['code'] = 1;
			$data['msg'] = $e->getMessage();
		}
		$this->echoJson($data);
	}

	/**
	 * 备份数据表及数据
	 */
	public function backupAction()
	{
		$database = $this->_request->get('database');
		$tableName = $this->_request->get('tableName');
		try {
			$downloadStr = $this->_load->model('table')->backupTable($database, $tableName);
			$this->_load->library('download')->downloadByString($database . '.' . $tableName . '.sql', $downloadStr);
		} catch (Exception $e) {
			$data['code'] = 1;
			$data['msg'] = $e->getMessage();
			$this->echoJson($data);
		}
	}

	public function restoreAction()
	{
		$database = $this->_request->post('database');
		$tableName = $this->_request->post('tableName');
		if (!$database || !$tableName || !isset($_FILES['restoreSQL']) ||
			!isset($_FILES['restoreSQL']['tmp_name']) ||
			$_FILES['restoreSQL']['tmp_name'] == '' ||
			$_FILES['restoreSQL']['error'] != UPLOAD_ERR_OK
		) {
			$data['code'] = 1;
			$data['msg'] = '参数或文件错误';
			$this->echoJson($data);
			return;
		}
		$fileName = $_FILES['restoreSQL']['tmp_name'];
		$data = $this->_load->model('table')->restore($database, $tableName, $fileName);
		echo $this->Json($data);
	}

	public function creatBasicCodeAction()
	{
		$database = $this->_request->get('database');
		$tableName = $this->_request->get('tableName');
		try {
			if (!$database || !$tableName) {
				$data['code'] = 1;
				$data['msg'] = '参数或文件错误';
				throw new \Exception($data['msg'], $data['code']);
			}
			$rules = $this->_load->model('table')->getRules($database, $tableName);
			if(!isset($rules['rules']))
			{
				$data['code'] = 1;
				$data['msg'] = '请先设置规则';
				throw new \Exception($data['msg'], $data['code']);
			}
			$privateKeys = 'array(\'' . join('\', \'', array_keys($rules['rules']['pri'])) . '\')';
			$this->_view->assign('privateKeys', $privateKeys);
			$code = $this->_load->model('code');
			$code->setDatabase($database);
			$code->setClass($tableName);
			$this->_view->assign('code', $code->output());
			$sampleCode = $this->_view->fetch('manage/data/code.html');
			$this->_load->library('download')->downloadByString($tableName . '.php', $sampleCode);
		} catch (\Exception $e) {
			$this->showErrorPage($e->getMessage());
		}
	}
}
