<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Terminal_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	// -テーブルのデータ取得処理---------------------------------------------
	public function select() {
		// -データベースから取得-----------------------------------------
		$query = $this->db->get('terminal');
		return $query->result();
	}

	// -テーブルのデータ取得処理---------------------------------------------
	public function select_where_user_id($user_id) {
		// -データベースから取得-----------------------------------------
		$query = $this->db->get_where('terminal', array('user_id' => $user_id));
		return $query->result();
	}

	// -テーブルへのデータ追加処理-------------------------------------------
	public function insert($data) {
		return $this->db->insert('terminal', $data);
	}
}
?>

