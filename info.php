<?php
	error_reporting(E_ALL);
	session_start();
	
	header("Content-Type: text/html; charset=utf-8");
	echo '<link rel="stylesheet" type="text/css" href="style.css" />';
	
	include 'db.php';
	include 'func.php';
	$db = new DB('localhost','test');
	
	define('FOR_DAYS',3);									//Константа для указания размера архива погоды
	$_SESSION['id'] = $_GET['id'];													
	$collection = $db->getCollection('mycity');
	if(!$db->isCollectionEmpty($collection)) {
		$cursor = $collection->find(array('_id' => new MongoId($_SESSION['id'])));	//Выбор нужного города по id
		foreach ($cursor as $city_doc) {
			$_SESSION['lasttitle'] = $city_doc['cityname'];				//Сохранение последнего выбранного города
			$_SESSION['lastcity'] = $city_doc['cityalias'];
		}			
	}
	$weather = getWeatherInCity($_SESSION['lastcity']);					//Получение текущей погоды
	$forecast = getForecastInCity($_SESSION['lastcity'],FOR_DAYS);				//Получение прогноза погоды
	echo '<div id="content"><div id="top">
		'.$_SESSION['lasttitle'].'<br> 
		Температура:'.$weather['temperature'].'&degС 
		Давление:'.$weather['pressure'].'мм рт.ст. 
		Влажность:'.$weather['humidity'].'%</div>';
	/* Вывод таблиц со списками прогноза погоды и архива погоды */
	echo '<h1>'.$_SESSION['lasttitle'].'</h1>										
	<table id="cities">
	<tr>
		<td>Прогноз погоды на 3 дня<br>
			<table>
				<tr>
					<td>Дата</td>
					<td>Температура</td>
					<td>Влажность</td>
				</tr>';
				/* Вывод прогноза погоды */
				for($i=0;$i<FOR_DAYS;$i++)
				{
					$date = date('d.m.Y', strtotime($forecast[$i]['date']));//Изменение формата даты
					echo '<tr>
						<td>'.$date.'</td>
						<td>'.$forecast[$i]['temperature'].'&degС</td>
						<td>'.$forecast[$i]['humidity'].'%</td>
					</tr>';
				}
	echo '  </table>
		</td>
		<td>Архив погоды<br>
			<table>
				<tr>
					<td>Дата</td>
					<td>Температура</td>
					<td>Влажность</td>
				</tr>';
				/* Вывод архива погоды */
				$archive_col = $db->getCollection('archive');
				if(!$db->isCollectionEmpty($archive_col)) {
					$cursor = $archive_col->find(array('cityid' => new MongoId($_SESSION['id'])));
					$cursor->sort(array('_id' => -1));					//Обратная сортировка
					$cursor->limit(3);							//Выборка последних 3 записей из архива
					foreach ($cursor as $city_doc) {
						echo '<tr>
							<td>'.$city_doc['updtime'].'</td>
							<td>'.$city_doc['temperature'].'&degС</td>
							<td>'.$city_doc['humidity'].'%</td>
						</tr>';
					}	
				}
	echo '	</table>
		</td>
	</tr>
	</table>
	<form action="update.php">
		<button type="submit">Обновить</button>
	</form>';
	$db->close();
	exit();
?>
