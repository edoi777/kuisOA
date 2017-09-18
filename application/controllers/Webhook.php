<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Webhook extends CI_Controller {

	private $user;

	function __construct()
	{
		parent::__construct();
		$this->load->model('line_model');
	}

	public function index()
	{

		// if it is not POST request, just say hello
		if ($_SERVER['REQUEST_METHOD'] !== 'POST')
			die("Hi Guys. Service ready");

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
			
			// get userdata before doing any response
			$this->user = $this->line_model->getUser($event['source']['userId']);
			
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

			// when user follow (add friend or unblock) the bot
			else if($event['type'] == 'follow')
			{
				$this->responseFollowEvent($event);
			}

			// when user unfollow (block friend) the bot
			else if($event['type'] == 'unfollow')
			{
				echo $this->line_model->resetUser($this->user['uid']);
			} 
		}
	}

	function responseTextMessage($event)
	{
		$text = trim(strtolower($event['message']['text']));

		// if user starting the game
		if($this->user['state'] < 1)
		{
			// if message is not 'mulai'
			if(strpos('mulai', $text) === FALSE)
			{
				$this->line_model->pushTextMessage($this->user['uid'], 'ketik "mulai" dulu, Gan :D');
				return;

			} else {
				// update state to 3, means he start game and has 3 remain lives
				$this->line_model->updateState($this->user['uid'], 3);

				// generate the answer
				$question = $this->line_model->generateAnswer($this->user['uid'], $this->user['next_question']);

				// send question
				$this->line_model->pushTextMessage($this->user['uid'], "Soal: " . $question['hint']);
			}
		}

		// for next state, user has his own question placed in $this->user['answer']
		else {
			if($text == $this->user['answer'])
			{
				if($this->user['state'] == 3)
				{
					$this->line_model->pushStickerMessage($this->user['uid'], 1, 3);
					$this->line_model->pushTextMessage($this->user['uid'], "Hah? Kok bisa langsung kejawab?!");
				} else if($this->user['state'] == 2) {
					$this->line_model->pushStickerMessage($this->user['uid'], 1, 13);
					$this->line_model->pushTextMessage($this->user['uid'], "Hmm.. lumayan. Kali ini jawabanmu benar.");
				} else {
					$this->line_model->pushStickerMessage($this->user['uid'], 1, 2);
					$this->line_model->pushTextMessage($this->user['uid'], "Yeheeaaay, akhirnya bener juga! Selamat!");
				}

				// reset to 0
				$this->line_model->updateState($this->user['uid'], 0);
				$this->line_model->pushTextMessage($this->user['uid'], 'Mau coba lagi? Hmm kali ini aku akan kasih soal yang lebih sulit! Ketik "mulai" kapanpun Kamu siap. Kalo berani!');

			} else {
				if($this->user['state'] == 3)
				{
					$this->line_model->pushTextMessage($this->user['uid'], "Ow ow oww.. kurang tepat. Kesempatan menebak 2 kali lagi eaa");
				} else if($this->user['state'] == 2) {
					$this->line_model->pushTextMessage($this->user['uid'], "No. Masih salah. Ayo, satu kesempatan menebak lagi. Pikirkan baik-baik!");
				} else {
					$this->line_model->pushStickerMessage($this->user['uid'], 1, 100);
					$this->line_model->pushTextMessage($this->user['uid'], "Hahaha.. dasar pecund*ang! Pertanyaan gampang aja ga bisa. Huuu.. Jawabannya harusnya {$this->user['answer']}!");
					
					$this->line_model->pushTextMessage($this->user['uid'], 'Penasaran? Ga yakin Kamu bisa jawab. Yang barusan aja kagak. Tapi kalo masih penasaran, ketik "mulai"!');
				}

				// update user state
				$this->line_model->updateState($this->user['uid']);
			}
		}

	}

	function responseStickerMessage($event)
	{
		$this->line_model->pushStickerMessage($event['source']['userId'], $event['message']['packageId'], $event['message']['stickerId']);

	}

	function responseFollowEvent($event)
	{
		$user = $this->line_model->saveUser($event['source']['userId']);
		$this->line_model->pushTextMessage($user['uid'], "Halo {$user['nama']}, salam kenal!");
		$this->line_model->pushTextMessage($user['uid'], "Pada game ini Kamu diminta untuk menebak kata apa yang aku maksud. Aku akan memberikan satu petunjuk yang mengarah ke jawaban yang dimaksud. Kamu punya kesempatan maksimal 3 kali untuk menebak.");
		$this->line_model->pushTextMessage($user['uid'], 'Untuk memulai silakan ketikkan perintah "mulai"');
	}

	function coba()
	{
		$data = json_decode(file_get_contents('./questions.json'), true);

		print_r($data);
	}


}
