<?php
	/* Получение текущей погоды по алиасу города */
	function getWeatherInCity($city)
	{
		$service_url = 'http://pogoda.ngs.ru/api/v1/forecasts/current?city='.$city;
		$curl = curl_init($service_url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$curl_response = curl_exec($curl);
		curl_close($curl);
		$json = json_decode($curl_response, true);
		$result['temperature'] = $json['forecasts'][0]['temperature'];
		$result['pressure'] = $json['forecasts'][0]['pressure'];
		$result['humidity'] = $json['forecasts'][0]['humidity'];
		return $result;
	}
	/* Получение прогноза погоды по алиасу города */
	function getForecastInCity($city,$days)
	{
		$service_url = 'http://pogoda.ngs.ru/api/v1/forecasts/forecast?city='.$city;
		$curl = curl_init($service_url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$curl_response = curl_exec($curl);
		curl_close($curl);
		$json = json_decode($curl_response, true);
		if($days > $json['metadata']['resultset']['count']){
			$days = $json['metadata']['resultset']['count'];
		}
		for($i = 0; $i < $days; $i++) {
			$result[$i]['date'] = $json['forecasts'][$i]['date'];
			$result[$i]['temperature'] = $json['forecasts'][$i]['hours'][2]['temperature']['avg'];
			$result[$i]['humidity'] = $json['forecasts'][$i]['hours'][2]['humidity']['avg'];
		}
		return $result;
	}
	/* Получение списка городов */
	function insertCitiesList($collection)
	{
		$service_url = 'http://pogoda.ngs.ru/api/v1/cities';
		$curl = curl_init($service_url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$curl_response = curl_exec($curl);
		curl_close($curl);
		$json = json_decode($curl_response, true);
		$total_cities = $json['metadata']['resultset']['count'];
		for($i = 0; $i < $total_cities; $i++) {
			$collection->insert($json['cities'][$i]);
		}
	}
?>
