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

	// -テーブルのデータ取得処理---------------------------------------------
	public function select_where_user_terminal_id($user_id, $terminal_id) {
		// -データベースから取得-----------------------------------------
		$query = $this->db->get_where('terminal', array(
			'id'		=> $terminal_id,
			'user_id'	=> $user_id
		));

		foreach ($query->result() as $terminal) {
			return $terminal;
		}

		return False;
	}

	// -テーブルへのデータ追加処理-------------------------------------------
	public function insert($data) {
		return $this->db->insert('terminal', $data);
	}

	// -テーブルへのデータ消去処理-------------------------------------------
	public function delete_where_user_terminal_id($user_id, $terminal_id) {
		return $this->db->delete('terminal', array(
			'id'		=> $terminal_id,
			'user_id'	=> $user_id
		));
	}
}
?>

