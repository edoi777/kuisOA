<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Webhook extends CI_Controller {

	private $myUID = "U3cc5055a5edee58cec04540a1ed0fe02";

	function __construct()
	{
		parent::__construct();
		$this->load->model('line_model');
	}

	public function index()
	{

		// if it is not POST request, just say hello
		if ($_SERVER['REQUEST_METHOD'] !== 'POST')
			die("Hai gaes. Nothing here :P");

		$body = file_get_contents('php://input');
		$this->line_model->writeLog($body);
		
		$bodyArray = json_decode($body, true);
		$events = $bodyArray['events'];

		foreach ($events as $event) {
			// $event['type']
			// $event['replyToken']
			// $event['source']['userId']
			// $event['source']['type']
			// $event['timestamp']
			// $event['message']['type']
			// $event['message']['id']
			// $event['message']['text']
			
			// bila eventnya bertipe message
			if($event['type'] == 'message') 
			{
				// if message is text
				switch($event['message']['type'])
				{
					case 'text':
						$this->responseTextMessage($event);
						break;
					case 'sticker':
						$this->responseStickerMessage($event);
						break;
					// another case?

					default: continue;
				}
			} 

			// when user follow/add friend the bot
			else if($event['type'] == 'follow')
			{
				$this->responseFollowEvent($event);
			} 
		}
	}

	function responseTextMessage($event)
	{
				

		$this->line_model->pushTextMessage($event['source']['userId'], $event['message']['text']);

	}

	function responseStickerMessage($event)
	{
		$this->line_model->pushStickerMessage($event['source']['userId'], $event['message']['packageId'], $event['message']['stickerId']);

	}

	function responseFollowEvent($event)
	{
		$user = $this->line_model->saveUser($event['source']['userId']);
		$this->line_model->pushTextMessage($user['uid'], "Halo {$user['nama']}, salam kenal!");
		$this->line_model->pushTextMessage($user['uid'], "Pada game ini Kamu diminta untuk menebak kata apa yang aku maksud. Kamu bisa menebak huruf demi huruf atau langsung menebak katanya. Kamu punya kesempatan maksimal 3 kali tebakan salah.");
		$this->line_model->pushTextMessage($user['uid'], 'Untuk memulai silakan ketikkan perintah "mulai"');
	}

	function coba()
	{
		$data = $this->line_model->getProfile($this->myUID);

		print_r($data);
	}


}
