<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Line_model extends CI_Model {

	private $accessToken = "CHANNEL ACCESS TOKEN";

	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	public function pushTextMessage($uid, $text)
	{
		$data['to'] = $uid;
		$data['messages'] = [
			[
				'type' => 'text', 
				'text' => $text
			]
		];

		$result = $this->sendRequest('https://api.line.me/v2/bot/message/push', $data);
		$this->writeLog($result);
		return $result;
	}

	public function pushStickerMessage($uid, $packageId, $stickerId)
	{
		$data['to'] = $uid;
		$data['messages'] = [
			[
			  "type" => "sticker",
			  "packageId" => $packageId,
			  "stickerId" => $stickerId
			]
		];

		$result = $this->sendRequest('https://api.line.me/v2/bot/message/push', $data);
		$this->writeLog($result);
		return $result;
	}

	public function pushImageMessage($uid, $thumbnailUrl, $imageUrl = false)
	{
		$data['to'] = $uid;
		$data['messages'] = [
			[
			    "type" => "image",
			    "previewImageUrl" => $thumbnailUrl,
			    "originalContentUrl" => $imageUrl ? $imageUrl : $thumbnailUrl
			]
		];

		$result = $this->sendRequest('https://api.line.me/v2/bot/message/push', $data);
		$this->writeLog($result);
		return $result;
	}

	public function pushVideoMessage($uid, $thumbnailUrl, $videoUrl)
	{
		$data['to'] = $uid;
		$data['messages'] = [
			[
			    "type" => "video",
			    "previewImageUrl" => $thumbnailUrl,
			    "originalContentUrl" => $videoUrl
			]
		];

		$result = $this->sendRequest('https://api.line.me/v2/bot/message/push', $data);
		$this->writeLog($result);
		return $result;
	}

	public function pushAudioMessage($uid, $audioUrl, $duration)
	{
		$data['to'] = $uid;
		$data['messages'] = [
			[
			    "type" => "audio",
			    "originalContentUrl"=> $audioUrl,
			    "duration" => $duration
			]
		];

		$result = $this->sendRequest('https://api.line.me/v2/bot/message/push', $data);
		$this->writeLog($result);
		return $result;
	}

	public function pushLocationMessage($uid, $title, $address, $lat, $long)
	{
		$data['to'] = $uid;
		$data['messages'] = [
			[
			    "type" => "location",
			    "title" => $title,
			    "address" => $address,
			    "latitude" => $lat,
			    "longitude" => $long
			]
		];

		$result = $this->sendRequest('https://api.line.me/v2/bot/message/push', $data);
		$this->writeLog($result);
		return $result;
	}

	private function sendRequest($url, $data = [])
	{
		$header[] = "Content-type: application/json";
		$header[] = "Authorization: Bearer " . $this->accessToken;

		$opts = [
			'http' => [
				'method'  => 'POST',
				'header'  => implode("\n", $header),
				'content' => json_encode($data)
				]
			];
		$context  = stream_context_create($opts);

		return $result = file_get_contents($url, false, $context);
	}

	private function sendGetRequest($url)
	{
		$header[] = "Authorization: Bearer " . $this->accessToken;

		$opts = [
			'http' => [
				'method'  => 'GET',
				'header'  => implode("\n", $header)
				]
			];
		$context  = stream_context_create($opts);

		return $result = file_get_contents($url, false, $context);
	}
	
	function writeLog($message = "", $filename = 'log.txt')
	{
		$this->load->helper('file');
		write_file($filename, $message."\n", 'a+');
	}

	function getProfile($uid)
	{
		$url = "https://api.line.me/v2/bot/profile/". $uid;
		return $this->sendGetRequest($url);
	}

	function saveUser($uid)
	{
		if($user = $this->db->where('uid', $uid)->get('users')->row_array())
			return $user;

		$data = json_decode($this->getProfile($uid), true);
		$user = [
			'uid' => $data['userId'],
			'nama' => $data['displayName'],
			'avatar' => $data['pictureUrl']
		];
		$this->db->insert('users', $user);
		$user['id'] = $this->db->insert_id();
		return $user;
	}

	function resetUser($uid)
	{
		$this->db->where('uid', $uid)->delete('users');
		return $this->db->affected_rows();
	}

	function getUser($uid)
	{
		$data = $this->db->where('uid', $uid)->get('users')->row_array();
		if(!empty($data))
			return $data;

		return false;
	}

	function updateState($uid, $state = false)
	{
		if($state !== false)
			$this->db->set('state', $state);
		else
			$this->db->set('state', 'state - 1', FALSE);

		$this->db->where('uid', $uid)->update('users');

		return $this->db->affected_rows();
	}

	function generateAnswer($uid, $next_question)
	{
		$questions = json_decode(file_get_contents('questions.json'), true);
		if($next_question >= count($questions))
			$next_question = 0;

		// take one next question to be returned
		$question = $questions[$next_question];

		// save answer and index for next question
		$data['answer'] = $questions[$next_question]['answer'];
		$data['next_question'] = ++$next_question;
		$this->db->where('uid', $uid)->update('users', $data);

		return $question;
	}

}