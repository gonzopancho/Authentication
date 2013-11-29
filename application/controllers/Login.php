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

class Login extends CI_Controller {
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
		 * セッションの読み込み
		 */
		$this->load->driver('Session');

		/*
		 * 送信されたデータの取得
		 */
		$data = $this->input->post();

		/*
		 * バリデーションの設定
		 */
		$this->load->library('form_validation');
		$this->form_validation->set_rules('name', 'ユーザー名', 'required|alpha_numeric');
		$this->form_validation->set_rules('password', 'パスワード', 'required|alpha_numeric');

		/*
		 * データが送信されたかの確認
		 */
		$error_message = '';
		if (count($data) && ($this->form_validation->run() == TRUE)) {
			/*
			 * ユーザー名、パスワードの取得
			 */
			$name		= $this->input->post('name');
			$password	= $this->input->post('password');

			/*
			 * user_modelの読み込み
			 */
			$this->load->model('user_model', '', True);

			/*
			 * 認証処理
			 */
			$recode = $this->user_model->select_where_name_password($name, $password);
			if ($recode == True) {
				// 認証できた場合の処理
				$this->session->set_userdata('authenticated', True);
				$this->session->set_userdata('user_id', $recode->id);
				$this->session->set_userdata('user_limit', $recode->limit);

				$this->load->helper('url');
				redirect('terminal', 'index');
			} else {
				$error_message = "ユーザー名またはパスワードが一致しません。";
			}
		}

		$this->parser->parse("login/index.html", array('error_message' => $error_message));
	}

	public function ok()
	{
		$this->parser->parse("login/ok.html", NULL);
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/Welcome.php */

