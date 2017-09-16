<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Line_m extends CI_Model {

	private $accessToken = "ZUHwYH9xBxGMEc99U3IvT6XzKhLnc5vBTXWyAmetxkUNEH/pZ7uK2oVb4PKG5SgPo4YdQr8Z1ZeCyVgBe1OvmoLh7r290dXETVdKyRE6R6hMQyHRt+BwrGOvBI08f4y3O8eK9d50MY6g2WMARxZJQAdB04t89/1O/w1cDnyilFU=";

	function createTextMessage($text)
	{
		return [
			'type' => 'text',
			'text' => $text
		];
	}

	function createStickerMessage($packageId, $stickerId)
	{
		return [
			'type' => 'sticker',
			'packageId' => $packageId,
			'stickerId' => $stickerId
		];
	}	

	function createImageMessage($imageUrl, $thumbnailUrl = false)
	{
		return [
		    "type" => "image",
		    "originalContentUrl" => $imageUrl,
		    "previewImageUrl" => $thumbnailUrl ? $thumbnailUrl : $imageUrl,
		];
	}	

	function createVideoMessage($videoUrl, $thumbnailUrl)
	{
		return [
		    "type" => "video",
		    "originalContentUrl" => $videoUrl,
		    "previewImageUrl" => $thumbnailUrl,
		];
	}	

	function createAudioMessage($audioUrl, $duration)
	{
		return [
		    "type" => "audio",
		    "originalContentUrl" => $audioUrl,
		    "duration" => $duration
		];
	}

	function createLocationMessage($title, $address, $lat, $long)
	{
		return [
		    "type" => "location",
		    "title" => $title,
		    "address" => $address,
		    "latitude" => $lat,
		    "longitude" => $long
		];
	}

	function createImagemapMessage($baseImage, $altText, $width, $height, $action = [])
	{
		$action = [
			[
				"type" => "uri",
				"linkUri" => "https =>//example.com/",
				"area" => [
					"x" => 0,
					"y" => 0,
					"width" => 520,
					"height" => 1040
				]
			],
			[
				"type" => "message",
				"text" => "hello",
				"area" => [
					"x" => 520,
					"y" => 0,
					"width" => 520,
					"height" => 1040
				]
			]
		];

		return [
			"type" => "imagemap",
			"baseUrl" => $baseImage,
			"altText" => $altText,
			"baseSize" => [
				"height" => $height,
				"width" => $width
			],
			"actions" => $action
		];
	}

	function writeLog($content)
	{
		$this->load->helper('file');
		write_file('log.txt', $content, 'a+');
	}

	function sendRequest($uid, $uri, $messages = [])
	{	
		$url = 'https://api.line.me/v2/bot/message/' . $uri;

		$data = [
			'to' => $uid,
			'messages' => $messages
		];

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

	private function sendGetRequest($uri)
	{
		$url = 'https://api.line.me/v2/bot/'.$uri;

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


	function saveUser($uid)
	{
		// ambil data profile dari line
		$profile = json_decode($this->sendGetRequest("profile/".$uid), true);

		// simpan data user ke database
		$data = [
			'uid' => $uid,
			'nama' => $profile['displayName'],
			'avatar' => $profile['pictureUrl']
		];

		$this->db->insert('users', $data);
		return $data;
	}

	function deleteUser($uid)
	{
		$this->db->where('uid', $uid)->delete('users');
		return $this->db->affected_rows();
	}

	function checkState($uid)
	{
		$data = $this->db->where('uid', $uid)->get('users')->row_array();
		return $data['state'];
	}

	function updateState($uid, $state = 0)
	{
		$this->db->set('state', $state);
		$this->db->where('uid', $uid)->update('users');
	}

	function checkNextQuestion($uid)
	{
		$data = $this->db->where('uid', $uid)->get('users')->row_array();
		return $data['next_question'];
	}

	function saveAnswer($uid, $answer)
	{
		$this->db->set('answer', $answer);
		$this->db->where('uid', $uid)->update('users');
	}

	function checkAnswer($uid, $answer)
	{
		$data = $this->db->where('uid', $uid)->get('users')->row_array();
		$rightAnswer = $data['answer'];
		if(strtolower($rightAnswer) == strtolower($answer))
			return true;

		return false;
	}

	function setNextQuestion($uid)
	{
		$last_question = $this->checkNextQuestion($uid);

		if($last_question >= 4)
			$this->db->set('next_question', 0);
		else
			$this->db->set('next_question', 'next_question + 1', false);

		$this->db->where('uid', $uid)->update('users');
	}
}