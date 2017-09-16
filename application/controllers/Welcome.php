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
		$body = file_get_contents('php://input');
		$this->writeLog($body);

		$notifs = json_decode($body, true);
		foreach ($notifs['events'] as $event) {
			// $event['type']
			// $event['replyToken']
			// $event['source']['type']
			// $event['source']['userId']
			// $event['source']['userId']
			// $event['timestamp']
			// $event['message']['type']
			// $event['message']['id']
			// $event['message']['text']
			
			$uid = $event['source']['userId'];

			if($event['type'] == 'message'){
				if($event['message']['type'] == 'text')
					$this->respondTextMessage($event);
			}

			else if ($event['type'] == 'follow'){
				$this->respondFollow($event);
			}

			else if ($event['type'] == 'unfollow'){
				$this->respondUnfollow($event);
			}
		};
	}

	function respondTextMessage($event)
	{
		$uid = $event['source']['uid'];
		$text = $event['message']['text'];
		$opsi = ['Ya', 'Tidak', 'Boleh jadi', 'Ga mungkin'];
		$jawaban = $opsi[array_rand($opsi)];
		$messages[] = $this->line_m->createTextMessage($jawaban);

		$this->line_m->sendRequest($uid, 'push', $messages);
	}

	function respondFollow($event)
	{
		$users = $this->line_m->saveUser($event['source']['userId']);

		$messages[] = $this->line_m->createTextMessage("Selamat datang, ". $users['nama']);

		$this->line_m->sendRequest($users['uid'], 'push', $messages);
	}

	function respondUnfollow($event)
	{
		$this->line_m->deleteUser($event['source']['userId']);
	}

	function writeLog($content)
	{
		$this->load->helper('file');
		write_file('log.txt', $content, 'a+');
	}

}
