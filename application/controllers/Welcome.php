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
		$this->line_m->writeLog($body);

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
		$uid = $event['source']['userId'];

		// bila statenya 0, maka kita hanya terima kata 'mulai'
		if(! $state = $this->line_m->checkState($uid))
		{
			// tampilkan soal
			if(strtolower($event['message']['text']) == 'mulai')
			{
				// update statenya jadi 3
				$this->line_m->updateState($uid, 3);


				//  ambil next question
				$answers = json_decode(file_get_contents('./questions.json'), true);
				$question_id = $this->line_m->checkNextQuestion($uid);
				$answer = $answers[$question_id];

				// set answer ke database
				$this->line_m->saveAnswer($uid, $answer['answer']);

				// tampilkan soal
				$messages[] = $this->line_m->createTextMessage($answer['hint']);
				$this->line_m->sendRequest($uid, 'push', $messages);
				return;
			}

			// bila dia memasukkan kata kunci selain 'mulai'
			else {
				$messages[] = $this->line_m->createTextMessage('Ketik "mulai" dulu Bro!');
				$this->line_m->sendRequest($uid, 'push', $messages);
				return;
			}
		}

		// bila state > 0, cek text sebagai jawaban
		else {
			$text = $event['message']['text'];
			if($this->line_m->checkAnswer($uid, $text))
			{
				$messages[] = $this->line_m->createTextMessage("Yup benaaar, Kamu berhasil!");
				$messages[] = $this->line_m->createStickerMessage(1, 4);
				$this->line_m->sendRequest($uid, 'push', $messages);

				// update state ke 0
				$this->line_m->updateState($uid, 0);

				// update next question
				$this->line_m->setNextQuestion($uid);
			}

			// bila jawaban masih salah
			else {
				// kirim pesan jawaban salah
				$messages[] = $this->line_m->createStickerMessage(1, 15);
				$messages[] = $this->line_m->createTextMessage("Owww.. masih salah Bro.");
				$this->line_m->sendRequest($uid, 'push', $messages);

				if($state == 1)
				{
					$messages[] = $this->line_m->createTextMessage("Coba lagi?? Silakan ketik 'mulai'!");
					$this->line_m->sendRequest($uid, 'push', $messages);

					// update next question
					$this->line_m->setNextQuestion($uid);
				}

				// kurangi state
				$this->line_m->updateState($uid, $state - 1);
			}

		}
	}

	function respondFollow($event)
	{
		$users = $this->line_m->saveUser($event['source']['userId']);

		$messages[] = $this->line_m->createTextMessage("Halo {$user['nama']}, salam kenal!");
		$messages[] = $this->line_m->createTextMessage("Pada game ini Kamu diminta untuk menebak kata apa yang aku maksud. Aku akan memberikan satu petunjuk yang mengarah ke jawaban yang dimaksud. Kamu punya kesempatan maksimal 3 kali untuk menebak.");
		$messages[] = $this->line_m->createTextMessage('Untuk memulai silakan ketikkan perintah "mulai"');

		$this->line_m->sendRequest($users['uid'], 'push', $messages);
	}

	function respondUnfollow($event)
	{
		$this->line_m->deleteUser($event['source']['userId']);
	}

}
