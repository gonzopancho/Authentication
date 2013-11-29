<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CI Smarty
 *
 * Smarty templating for Codeigniter
 *
 * @package   CI Smarty
 * @author       Dwayne Charrington
 * @copyright  2013 Dwayne Charrington and Github contributors
 * @link            http://ilikekillnerds.com
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 * @version     2.0
 */
class IP extends CI_Parser {
	/*
	 * IPアドレスの取得
	 */
	public function getAddress()
	{
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			return $_SERVER['HTTP_CLIENT_IP'];
		} else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		}

		return $_SERVER['REMOTE_ADDR'];
	}
}

