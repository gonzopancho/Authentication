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
		 * ARP関連処理用ライブラリ読み込み
		 */
		$this->load->library('ARP');
		$ip_addr	= $this->input->ip_address();
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
			// URLの作成
			$url = sprintf('http://%s:%s/add/%s', RYUMON_HTTP_HOST, RYUMON_HTTP_PORT, $mac_addr);

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
				// None
			} else {
				$error_message = 'MACアドレスの登録に失敗しました。';
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
			redirect('login', 'index');
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
}

/* End of file welcome.php */
/* Location: ./application/controllers/Welcome.php */

