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
		$this->load->model('table')->checkRuleTable();
	}

	/**
	 * 保存creator
	 */
	public function creatorAction()
	{
		$data = array();
		$database = $this->request->post('database');
		$tableName = $this->request->post('tableName');
		$rules = $this->request->post($tableName);
		if (!$rules) {
			$data['code'] = 1;
			echo $this->jsonEncode($data);
			return;
		}
		$result = $this->load->model('table')->saveRules($database, $tableName, $rules);
		if ($result) {
			$data['code'] = 0;
		} else {
			$data['code'] = 1;
			$data['error'] = $this->load->model('table')->tablesError;
		}
		$this->echoJson($data);
	}

	/**
	 * 编辑详细的规则
	 */
	public function rulesAction()
	{
		$data = array();
		$database = $this->request->post('database');
		$tableName = $this->request->post('tableName');
		$rules = $this->request->post('rules');
		if (!$rules) {
			$data['code'] = 1;
			echo $this->jsonEncode($data);
			return;
		}
		$result = $this->load->model('table')->updateRules($database, $tableName, $rules);
		if ($result) {
			$data['code'] = 0;
		} else {
			$data['code'] = 1;
			$data['error'] = $this->load->model('table')->tablesError;
		}
		$this->echoJson($data);
	}

	/**
	 * 获取制定数据库的数据包
	 */
	public function tableListsAction()
	{
		$tableName = $this->request->get('tableName', '');
		$this->echoJson($this->load->model('table')->getTableLists($this->load->model('table')->getUseDB(), $tableName));
	}

	/**
	 * 获取自定表的字段
	 */
	public function fieldsListsAction()
	{
		$tableName = $this->request->get('tableName');
		$this->echoJson($this->load->model('table')->getFieldsLists($this->load->model('table')->getUseDB(), $tableName));
	}

	/**
	 * 删除数据表中的数据
	 */
	public function tableAction()
	{
		$data = array();
		$database = $this->request->get('__database', $this->load->model('table')->getUseDB());
		$tableName = $this->request->get('__tableName', '');
		if (!$database || !$tableName) {
			$data['code'] = 1;
			$data['msg'] = '参数不全';
			echo $this->jsonEncode($data);
			return;
		}

		$pri = explode(',', $this->request->get('__pri'));

		$priVal = array();
		foreach ($pri as $val) {
			if (!$this->request->keyExists($_GET, $val)) {
				$data['code'] = 1;
				break;
			}
			$priVal[$val] = $this->request->get($val);
		}
		$result = $this->load->model('table')->removeTableData($database, $tableName, $priVal);
		if ($result) {
			$data['code'] = 0;
		} else {
			$data['code'] = 1;
			$data['error'] = $this->load->model('table')->getError() === false ? '删除数据失败' : $this->load->model('table')->getError();
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
			$database = $this->request->post('__database');
			$tableName = $this->request->post('__tableName');
			$pri = explode(',', $this->request->post('__pri'));


			$fields = $this->request->post('fields');
			$priVal = array();
			foreach ($pri as $val) {
				if (!$this->request->keyExists($_POST, $val)) {
					$data['code'] = 1;
					break;
				}
				$priVal[$val] = $this->request->post($val);
			}
			if ($data['code'] != 1) {
				$result = $this->load->model('table')->updateTableData($database, $tableName, $priVal, $fields);
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
			$database = $this->request->post('database');
			$tableName = $this->request->post('tableName');


			$fields = $this->request->post('fields');
			if ($data['code'] != 1) {
				$result = $this->load->model('table')->addTableData($database, $tableName, $fields);
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
		$database = $this->request->get('database');
		$tableName = $this->request->get('tableName');
		try {
			if (!$database || !$tableName) {
				$data['code'] = 1;
				$data['msg'] = '参数不全';
				throw new Exception($data['msg'], $data['code']);
			}
			$result = $this->load->model('table')->autoRules($database, $tableName);
			if ($result) {
				$tableStruct = $this->load->model('table')->getTableSQL($database, $tableName);
				$result['sql'] = '';
				if ($tableStruct['sql']) $result['sql'] = str_replace("'", "\'", $tableStruct['sql']);
				$downloadStr = "<?php\n\t return " . var_export($result, true) . ';';
				_loadClass('\Qii\Library\Download')->downloadByString($database . '.' . $tableName . '.config.php', $downloadStr);
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
		$database = $this->request->get('database');
		$tableName = $this->request->get('tableName');
		$data = array();
		try {
			$data = $this->load->model('table')->getTableSQL($database, $tableName);
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
		$database = $this->request->get('database');
		$tableName = $this->request->get('tableName');
		try {
			$downloadStr = $this->load->model('table')->backupTable($database, $tableName);
			_loadClass('\Qii\Library\Download')->downloadByString($database . '.' . $tableName . '.sql', $downloadStr);
		} catch (Exception $e) {
			$data['code'] = 1;
			$data['msg'] = $e->getMessage();
			$this->echoJson($data);
		}
	}

	public function restoreAction()
	{
		$database = $this->request->post('database');
		$tableName = $this->request->post('tableName');
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
		$data = $this->load->model('table')->restore($database, $tableName, $fileName);
		echo $this->jsonEncode($data);
	}

	public function creatBasicCodeAction()
	{
		$database = $this->request->get('database');
		$tableName = $this->request->get('tableName');
		try {
			if (!$database || !$tableName) {
				$data['code'] = 1;
				$data['msg'] = '参数或文件错误';
				throw new \Exception($data['msg'], $data['code']);
			}
			$rules = $this->load->model('table')->getRules($database, $tableName);
			if(!isset($rules['rules']))
			{
				$data['code'] = 1;
				$data['msg'] = '请先设置规则';
				throw new \Exception($data['msg'], $data['code']);
			}
            $privateKeys = '';
			if(isset($rules['rules']['pri']) && $rules['rules']['pri']) {
                $privateKeys = 'array(\'' . join('\', \'', array_keys($rules['rules']['pri'])) . '\')';
            }
			$this->view->assign('privateKeys', $privateKeys);
			$code = $this->load->model('code');
			$code->setDatabase($database);
			$code->setClass($tableName);
			$this->view->assign('code', $code->output());
			$sampleCode = $this->view->fetch('manage/data/code.html');
			_loadClass('\Qii\Library\Download')->downloadByString($tableName . '.php', $sampleCode);
		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}
}
