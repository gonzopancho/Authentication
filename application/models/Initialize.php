<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Initialize extends CI_Model {
	/**
	 * constructor
	 */
	public function __construct() {
		// -親クラスのコンストラクタを呼び出し---------------------------
		parent::__construct();

		// -データベースへの接続-----------------------------------------
		$this->load->database("initialize");
		$this->load->dbforge();
	}

	/**
	 * check apout database exist or not
	 **/
	public function databaseExist() {
		// -MYSQLからデータベースの取得---------------------------------
		$query = $this->db->query('SHOW DATABASES;');
		$rows = array();
		foreach ($query->result_array() as $row)
			$rows[] = $row['Database'];
		// -システムデータベースが存在するかのチェック-------------------
		if (in_array(AUTH_MYSQL_DATABASE, $rows) == false) {
			return false;
		}

		return true;
	}

	/**
	 * create database
	 **/
	public function create_database() {
		// -データベースの作成-------------------------------------------
		$this->dbforge->create_database(AUTH_MYSQL_DATABASE);
		return $this->databaseExist();
	}

	/*
	 * init user
	 */
	public function init_user($user_count = 256, $terminal_count = 2) {
		/*
		 * user_modelの読み込み
		 */
		$this->load->model('user_model', '', True);

		// ランダム文字列生成
		function get_random_str($length) {
			$strinit = "abcdefghkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ012345679";
			$strarray = preg_split("//", $strinit, 0, PREG_SPLIT_NO_EMPTY);

			$str = '';
			for ($j = 0; $j < $length; $j++) {
				$str .= $strarray[array_rand($strarray, 1)];
			}

			return $str;			
		}

		/*
		 * 管理者ユーザーの作成
		 */
		$data = array(
			'id'		=> '',
			'name'		=> AUTH_MYADMIN_USER,
			'password'	=> AUTH_MYADMIN_PASS,
			'limit'	=> PHP_INT_MAX,
			'role'		=> 0
		);

		$this->user_model->insert($data);

		/*
		 * ユーザーを1000個作成する
		 */
		for ($i = 1; $i < $user_count; $i++) {
			$name = $i;
			$password = get_random_str(10);

			$data = array(
				'id'		=> '',
				'name'		=> $name,
				'password'	=> $password,
				'limit'	=> $terminal_count,
				'role'		=> 1
			);

			$this->user_model->insert($data);
		}
	}

	/*
	 * create table
	 */
	public function create_table() {
		// -データベースレイアウトファイルの読み込み---------------------
		require_once BASEPATH . '../etc/database.php.ini';
		if ($this->db->db_select(AUTH_MYSQL_DATABASE) === false) {
			return false;
		}

		// -ユーザーテーブルの作成---------------------------------------
		$this->dbforge->add_field($db['user']['field']);
		$this->dbforge->add_key($db['user']['primary_key'], true);
		$this->dbforge->create_table('user', true);

		// -端末テーブルの作成-------------------------------------------
		$this->dbforge->add_field($db['terminal']['field']);
		$this->dbforge->add_key($db['terminal']['primary_key'], true);
		$this->dbforge->create_table('terminal', true);

		return true;
	}

	/**
	 * drop database
	 **/
	public function drop() {
		// -データベースの消去-------------------------------------------
		$this->dbforge->drop_database(AUTH_MYSQL_DATABASE);
	}
}
?>

