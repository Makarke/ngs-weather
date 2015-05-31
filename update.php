<?php
    error_reporting(E_ALL);
    include 'db.php';
    include 'func.php';
    $db = new DB('localhost','test');
    $collection = $db->getCollection('archive');
    $my_collection = $db->getCollection('mycity');
    $cursor = $my_collection->find();
    foreach ($cursor as $doc) {
        /* Обновление архива всех избранных городов */
        $weather = getWeatherInCity($doc['cityalias']);
        $today = date("Y-m-d H:i:s");
        $update = array("updtime" => $today,
            "cityid" => $doc['_id'],
            "temperature" => $weather['temperature'],
            "humidity" => $weather['humidity']);
        $collection->insert($update);
    }
    $db->close();
?>
