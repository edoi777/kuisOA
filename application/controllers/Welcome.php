<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('line_m');
	}

	public function index()
	{
		$uid = 'U3cc5055a5edee58cec04540a1ed0fe02';

		$messages[] = $this->line_m->createTextMessage('Haaaai');
		$messages[] = $this->line_m->createStickerMessage(1, 1);

		$this->line_m->sendRequest($uid, 'push', $messages);
	}

}
