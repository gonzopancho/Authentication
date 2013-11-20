<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	// -テーブルのデータ取得処理---------------------------------------------
	public function select() {
		// -データベースから取得-----------------------------------------
		$query = $this->db->get('user');
		return $query->result();
	}

	// -テーブルのデータをidから取得-----------------------------------------
	public function select_where_id($id) {
		// -データベースから取得したレコードを配列にコピー---------------
		$query = $this->db->get_where('user', array('id' => $id));
		foreach ($query->result() as $row)
			return $row;
		return false;
	}

	// -認証----------------------------------------------------------------
	public function select_where_name_password($name, $password) {
		// -データベースから取得したレコードを配列にコピー---------------
		$query = $this->db->get_where('user', array(
			'name'		=> $name,
			'password'	=> $password
		));
		foreach ($query->result() as $row)
			return $row;
		return false;
	}

	// -テーブルからデータ消去処理-------------------------------------------
	public function deleate($id_array) {
		// -テーブルからIDのレコード消去---------------------------------
		foreach ($id_array as $id)
			$this->db->delete('user', array('id' => $id));
	}

	// -テーブルへのデータ追加処理-------------------------------------------
	public function insert($data) {
		return $this->db->insert('user', $data);
	}
}
?>

