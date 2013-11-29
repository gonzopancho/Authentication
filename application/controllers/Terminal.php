<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.2.4 or newer
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the Academic Free License version 3.0
 *
 * This source file is subject to the Academic Free License (AFL 3.0) that is
 * bundled with this package in the files license_afl.txt / license_afl.rst.
 * It is also available through the world wide web at this URL:
 * http://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world wide web, please send an email to
 * licensing@ellislab.com so we can send you a copy immediately.
 *
 * @package		CodeIgniter
 * @author		EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2013, EllisLab, Inc. (http://ellislab.com/)
 * @license		http://opensource.org/licenses/AFL-3.0 Academic Free License (AFL 3.0)
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Terminal extends CI_Controller {
	/*
	 * 認証を通ってない場合、認証画面へ飛ばす
	 */
	function __construct() {
		parent::__construct();
		$this->load->helper('url');

		/*
		 * セッションの読み込み
		 */
		$this->load->driver('Session');

		/*
		 * 認証出来ているかの確認
		 */
		if ($this->session->userdata('authenticated') == False) {
			redirect('login', 'index');
		}
	}

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		/*
		 * ユーザーIDの取得
		 */
		$user_id = $this->session->userdata('user_id');

		/*
		 * terminal_modelの読み込み
		 */
		$this->load->model('terminal_model', '', True);
		$recode = $this->terminal_model->select_where_user_id($user_id);
		$terminals = array('terminals' => $recode);

		$this->parser->parse("terminal/index.html", $terminals);
	}

	public function add()
	{		
		/*
		 * 送信されたデータの取得
		 */
		$data = $this->input->post();

		/*
		 * ユーザーIDとユーザーエージェンドの取得
		 */
		$user_id	= $this->session->userdata('user_id');
		$user_limit	= $this->session->userdata('user_limit');
		$user_agent	= $this->input->user_agent();

		/*
		 * IP, ARP関連処理用ライブラリ読み込み
		 */
		$this->load->library('IP');
		$this->load->library('ARP');
		$ip_addr	= $this->ip->getAddress();
		$mac_addr	= $this->arp->findMACAddress($ip_addr);

		/*
		 * バリデーションの設定
		 */
		$this->load->library('form_validation');
		$this->form_validation->set_rules('name', '端末名', 'required');

		/*
		 * データが送信されたかの確認
		 */
		$error_message = '';
		$isSuccess = False;
		if (count($data)) {
			if ($this->form_validation->run() == TRUE) {
				if ($mac_addr == TRUE && $user_id == TRUE) {
					/*
					 * 送信可能であるかのフラグをセット
					 */
					$isSuccess = True;
				}
			} else {
				$error_message = '端末名を入力して下さい。';
			}
		}

		// 重複登録の確認
		if ($isSuccess == True) {
			// データベースに登録されていないかの確認
			$this->load->model('terminal_model', '', true);
			$terminals = $this->terminal_model->select_where_user_id($user_id);
			foreach ($terminals as $terminal) {
				if ($terminal->l2addr === $mac_addr) {
					$error_message = 'この端末は既に登録されています。';
					$isSuccess = False;
					break;
				}
			}
		}

		// 登録台数の確認
		if ($isSuccess == True) {
			if (count($terminals) >= $user_limit) {
				$error_message = sprintf('登録可能な端末の台数は%sまでです。', $user_limit);
				$isSuccess = False;
			}
		}

		// OpenFlowへの書き込み処理
		if (($isSuccess == True) && ($mac_addr == True)) {
			$controllers = unserialize(RYU_CONTROLLERS);
			for ($i = 0; $i < count($controllers); $i++) {
				$host = $controllers[$i]['HTTP_HOST'];
				$port = $controllers[$i]['HTTP_PORT'];

				if ($this->writeMACAddressFlow($host, $port, $mac_addr) == False) {
					break;
				}
			}

			if ($i < count($controllers)) {
				$error_message = 'MACアドレスを登録する事ができませんでした。';
				$isSuccess = False;
			}
		}

		if ($isSuccess == True) {
			// 登録する情報を作成
			$send_data = array(
				'id'		=> '',
				'user_id'	=> $user_id,
				'name'		=> $this->input->post('name'),
				'agent'		=> $this->input->user_agent(),
				'l2addr'	=> $mac_addr,
				'enabled'	=> True
			);

			// データベースへ登録
			$this->terminal_model->insert($send_data);

			$this->load->helper('url');
			redirect('login', 'ok');
		}

		/*
		 * viewに送信するデータを設定する
		 */
		$data = array(
			'ip_addr'	=> $ip_addr,
			'mac_addr'	=> $mac_addr,
			'error_message'	=> $error_message
		);

		/*
		 * ビューへデータを送信する
		 */
		$this->parser->parse("terminal/add.html", $data);
	}

	public function delete()
	{
		/*
		 * バリデーションの設定
		 */
		$this->load->library('form_validation');
		$this->form_validation->set_rules('terminal_id', '端末ID', 'required|numeric');

		/*
		 * 入力されたデータが正しい場合の処理
		 */
		if ($this->form_validation->run() == TRUE) {
			/*
			 * ユーザーIDと端末IDの取得
			 */
			$user_id	= $this->session->userdata('user_id');
			$terminal_id	= $this->input->post('terminal_id');
			/*
			 * 端末モデルの読み込みと取得
			 */
			$this->load->model('terminal_model', '', True);
			$terminal = $this->terminal_model->select_where_user_terminal_id($user_id, $terminal_id);
			if ($terminal == True) {
				/*
				 * OpenFlowスイッチ内のフローエントリ削除
				 */
				$controllers = unserialize(RYU_CONTROLLERS);
				for ($i = 0; $i < count($controllers); $i++) {
					/*
					 * ホストとポート番号の取得
					 */
					$host = $controllers[$i]['HTTP_HOST'];
					$port = $controllers[$i]['HTTP_PORT'];

					$this->removeMACAddressFlow($host, $port, $terminal->l2addr);
				}
			}

			/*
			 * データベースから消去
			 */
			$this->terminal_model->delete_where_user_terminal_id($user_id, $terminal_id);
		}

		redirect('terminal', 'index');
	}

	// MACアドレスの書き込み処理
	private function writeMACAddressFlow($hostname, $port, $mac_addr) {
		// URLの作成
		$url = sprintf('http://%s:%s/add/%s', $hostname, $port, $mac_addr);
		/*
		 * Curlの読み込み
		 */
		$this->load->library('curl');
		$json_str = $this->curl->simple_get($url);

		if (
			// JSONを取得できたかどうか？
			($json_str == True) &&
			// JSONをデコードできたかどうか？
			(($data = json_decode(trim($json_str), true)) == True) &&
			// JSONの書式チェック
			(array_key_exists('result', $data) == True) &&
			// JSONの中身チェック
			($data['result'] == True)
		) {
			return True;
		}

		return False;
	}

	// MACアドレスの消去処理
	private function removeMACAddressFlow($hostname, $port, $mac_addr) {
		// URLの作成
		$url = sprintf('http://%s:%s/remove/%s', $hostname, $port, $mac_addr);
		/*
		 * Curlの読み込み
		 */
		$this->load->library('curl');
		$json_str = $this->curl->simple_get($url);

		if (
			// JSONを取得できたかどうか？
			($json_str == True) &&
			// JSONをデコードできたかどうか？
			(($data = json_decode(trim($json_str), true)) == True) &&
			// JSONの書式チェック
			(array_key_exists('result', $data) == True) &&
			// JSONの中身チェック
			($data['result'] == True)
		) {
			return True;
		}

		return False;
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/Welcome.php */

