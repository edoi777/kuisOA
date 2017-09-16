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


}