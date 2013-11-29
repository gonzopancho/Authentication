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
class ARP extends CI_Parser {
	/*
	 * ARPテーブルの場所
	 */
	protected $table_path = '/proc/net/arp';

	/*
	 * ARPテーブルの取得
	 */
	public function getARPTable()
	{
		/*
		 * ARPTableを取得
		 */
		$arp_table = explode("\n", file_get_contents($this->table_path));

		/*
		 * ARPTable内の無駄な情報の消去
		 * -先頭には項目の説明が入っている
		 * -最後には、空の行が存在する
		 */
		array_shift($arp_table);
		array_pop($arp_table);

		return $arp_table;
	}

	/*
	 * IPアドレスからARPテーブルを取得
	 */
	public function findMACAddress($client_ip_address) {
		/*
		 * ARPTableを取得
		 */
		$lines			= $this->getARPTable();
		$client_ip_address	= $this->convertIPAddress($client_ip_address);

		foreach ($lines as $line) {
			/*
			 * line分割
			 */
			$line = preg_split("/[\s,]+/", $line);

			/*
			 * 各要素の取得
			 */
			$arp_ip_addr	= $this->convertIPAddress($line[0]);
			$arp_hw_type	= $line[1];
			$arp_flags	= $line[2];
			$arp_mac_addr	= $line[3];
			$arp_mask	= $line[4];
			$arp_device	= $line[5];

			/*
			 * IPアドレスがマッチすればMACアドレスの返却
			 */
			if ($client_ip_address === $arp_ip_addr) return $arp_mac_addr;
		}

		return False;
	}

	/*
	 * IPアドレスの比較
	 */
	public function convertIPAddress($ip_addr) {
		/*
		 * 例外(::1の場合)
		 */
		if ($ip_addr === '::1') $ip_addr = '127.0.0.1';

		/*
		 * IPアドレスを分割
		 */
		$ip_addr_proto = explode('.', $ip_addr);
		$ip_addr_array = array_map("intval", $ip_addr_proto);

		return implode($ip_addr_array, '.');
	}
}

