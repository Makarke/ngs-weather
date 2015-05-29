<?php
	error_reporting(E_ALL);
	session_start();
	header("Content-Type: text/html; charset=utf-8");
	echo '<link rel="stylesheet" type="text/css" href="style.css" />';
	include 'db.php';
	include 'func.php';
	$db = new DB('localhost','test');
	$collection = $db->getCollection('archive');
	$my_collection = $db->getCollection('mycity');
	$cursor = $my_collection->find();
	foreach ($cursor as $doc) {
		/* Обновление архива всех избранных городов */
		$weather = getWeatherInCity($doc['cityalias']);
		$today = date("Y-m-d H:i:s");			//Получение текущей даты
		$update = array("updtime" => $today,
			"cityid" => $doc['_id'],
			"temperature" => $weather['temperature'],
			"humidity" => $weather['humidity']);
		$collection->insert($update);
	}
	$db->close();
?>
