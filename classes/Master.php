<?php
require_once('../config.php');
class Master extends DBConnection
{
	private $settings;
	public function __construct()
	{
		global $_settings;
		$this->settings = $_settings;
		parent::__construct();
	}
	public function __destruct()
	{
		parent::__destruct();
	}
	function capture_err()
	{
		if (!$this->conn->error)
			return false;
		else {
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
			return json_encode($resp);
			exit;
		}
	}
	function delete_img()
	{
		extract($_POST);
		if (is_file($path)) {
			if (unlink($path)) {
				$resp['status'] = 'success';
			} else {
				$resp['status'] = 'failed';
				$resp['error'] = 'failed to delete ' . $path;
			}
		} else {
			$resp['status'] = 'failed';
			$resp['error'] = 'Unkown ' . $path . ' path';
		}
		return json_encode($resp);
	}
	function save_response()
	{
		extract($_POST);
		$data = "";
		foreach ($_POST as $k => $v) {
			if (!in_array($k, ['id', 'keyword', 'suggestion'])) {
				if (!empty($data)) $data .= ",";
				$v = $this->conn->real_escape_string($v);
				$data .= " `{$k}`='{$v}' ";
			}
		}
		$kw_arr = [];
		foreach ($keyword as $k => $v) {
			$v  = trim($this->conn->real_escape_string($v));
			$check = $this->conn->query("SELECT keyword FROM `keyword_list` where keyword = '{$v}'" . (!empty($id) ? " and response_id != '{$id}' " : ""))->num_rows;
			if ($check > 0) {
				$resp['status'] = 'failed';
				$resp['msg'] = 'Keyword already taken. This might complicate for fetching a response.';
				$resp['kw_index'] = $k;
				return json_encode($resp);
			}
			$kw_arr[] = $v;
		}
		if (empty($id)) {
			$sql = "INSERT INTO `response_list` set {$data} ";
		} else {
			$sql = "UPDATE `response_list` set {$data} where id = '{$id}' ";
		}
		$save = $this->conn->query($sql);
		if ($save) {
			$rid = !empty($id) ? $id : $this->conn->insert_id;
			$resp['rid'] = $rid;
			$resp['status'] = 'success';
			if (empty($id))
				$resp['msg'] = "New Response successfully saved.";
			else
				$resp['msg'] = " Response successfully updated.";
			$data2 = "";
			foreach ($kw_arr as $kw) {
				if (!empty($data2)) $data2 .= ", ";
				$data2 .= "('{$rid}', '{$kw}')";
			}
			$sql2 = "INSERT INTO `keyword_list` (`response_id`, `keyword`) VALUES {$data2}";
			$this->conn->query("DELETE FROM `keyword_list` where response_id = '{$rid}'");
			$save2 = $this->conn->query($sql2);
			if (!$save2) {
				if (empty($id))
					$this->conn->query("DELETE FROM `keyword_list` where response_id = '{$rid}'");
				$resp['status'] = 'failed';
				$resp['msg'] = $this->conn->error;
				$resp['sql'] = $sql2;
			}
			$data3 = "";
			$this->conn->query("DELETE FROM `suggestion_list` where response_id = '{$rid}'");
			foreach ($suggestion as $sg) {
				if (empty($sg))
					continue;
				$sg = $this->conn->real_escape_string($sg);
				if (!empty($data3)) $data3 .= ", ";
				$data3 .= "('{$rid}', '{$sg}')";
			}
			if (!empty($data3)) {
				$sql3 = "INSERT INTO `suggestion_list` (`response_id`, `suggestion`) VALUES {$data3}";
				$save3 = $this->conn->query($sql3);
				if (!$save3) {
					if (empty($id))
						$this->conn->query("DELETE FROM `keyword_list` where response_id = '{$rid}'");
					$resp['status'] = 'failed';
					$resp['msg'] = $this->conn->error;
					$resp['sql'] = $sql3;
				}
			}
		} else {
			$resp['status'] = 'failed';
			$resp['err'] = $this->conn->error . "[{$sql}]";
		}
		if ($resp['status'] == 'success')
			$this->settings->set_flashdata('success', $resp['msg']);
		return json_encode($resp);
	}
	function delete_response()
	{
		extract($_POST);
		$del = $this->conn->query("DELETE FROM `response_list` where id = '{$id}'");
		if ($del) {
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success', " Response successfully deleted.");
		} else {
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);
	}
	function fetch_response()
	{
		extract($_POST);
		$kw = $this->conn->real_escape_string(trim($kw)); // Sanitize keyword
		$sql = "SELECT * FROM `response_list` WHERE id IN (SELECT response_id FROM `keyword_list` WHERE `keyword` = '{$kw}')";
		$resp['sql'] = $sql; // Debugging: Include the query for inspection
		$qry = $this->conn->query($sql);

		if ($qry) {
			if ($qry->num_rows > 0) {
				$result = $qry->fetch_array();
				$resp['status'] = 'success';
				$resp['response'] = $result['response'];

				// Fetch suggestions
				$sg_qry = $this->conn->query("SELECT suggestion FROM `suggestion_list` WHERE response_id = '{$result['id']}'");
				$suggestions = $sg_qry->num_rows > 0 ? array_column($sg_qry->fetch_all(MYSQLI_ASSOC), 'suggestion') : [];
				$resp['suggestions'] = $suggestions;
			} else {
				$resp['status'] = 'success';
				$resp['response'] = $this->settings->info('no_answer');
			}
		} else {
			$resp['status'] = "failed";
			$resp['msg'] = $this->conn->error;
		}
		return json_encode($resp);
	}
}

$Master = new Master();
$action = !isset($_GET['f']) ? 'none' : strtolower($_GET['f']);
$sysset = new SystemSettings();
switch ($action) {
	case 'delete_img':
		echo $Master->delete_img();
		break;
	case 'save_response':
		echo $Master->save_response();
		break;
	case 'delete_response':
		echo $Master->delete_response();
		break;
	case 'fetch_response':
		echo $Master->fetch_response();
		break;
	default:
		// echo $sysset->index();
		break;
}
