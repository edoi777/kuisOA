<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Webhook extends CI_Controller {

	private $myUID = "U3cc5055a5edee58cec04540a1ed0fe02";

	public function index()
	{
		$this->load->model('line_model');

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


}
