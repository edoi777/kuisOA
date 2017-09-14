<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Install_table_users extends CI_Migration {

	public function up()
	{
		$sql = "CREATE TABLE `users` (
		  `id` int(11) NOT NULL,
		  `uid` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
		  `nama` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `avatar` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `state` tinyint(1) NOT NULL DEFAULT '0',
		  `answer` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `next_question` int(11) NOT NULL DEFAULT '0'
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

		$this->db->query($sql);
		return true;
	}

	public function down(){ return true; }
}
