<?php
	error_reporting(E_ALL); 											//Вывод ошибок
	session_start(); 													//Запуск сессии
	header("Content-Type: text/html; charset=utf-8"); 					//Установка кодировки
	echo '<link rel="stylesheet" type="text/css" href="style.css" />';	//Подключение css

	include 'db.php';
	include 'func.php';
	
	$db = new DB('localhost','test');									//Инициализация класса базы данных
	$collection = $db->getCollection('cities');							//Выбор коллекции
	if($db->isCollectionEmpty($collection)) {							//Проверка на пустоту коллекции
		insertCitiesList($collection);									//Заполнение коллекции списком городов
	}

	if(!isset($_SESSION['lastcity']) || !isset($_SESSION['lasttitle'])) {	
		$city_cursor = $collection->find()->limit(1);					//Выборка первого города
		foreach ($city_cursor as $city_doc) {	
			$_SESSION['lastcity']=$city_doc['alias'];					//Занесение полученных данных в сессию
			$_SESSION['lasttitle']=$city_doc['title'];
		}
	}

	if(isset($_POST['add'])) {													
		$city_cursor = $collection->findOne(array('alias' => $_POST['citieslist']));//Выборка города по его алиасу
		$fav_col = $db->getCollection('mycity');						
		$city = array("cityname" => $city_cursor['title'], 
			"cityalias" => $city_cursor['alias']);						//Условия для выборки "избранных" городов
		$fav_col->insert($city);										//Вставка в базу данных
	}
	$weather = getWeatherInCity($_SESSION['lastcity']);					//Получение массива с погодой
	echo '<div id="content"><div id="top">	
		'.$_SESSION['lasttitle'].'<br>
		Температура:'.$weather['temperature'].'&degС 
		Давление:'.$weather['pressure'].'мм рт.ст. 
		Влажность:'.$weather['humidity'].'%</div>';						//Вывод информации
	
	echo '<form action="chat.php" method="post">
			<button type="submit" name="add">Добавить город</button>
			<select name="citieslist">';								//Создание формы для добавления города в список
	$collection = $db->getCollection('cities');
	$cursor = $collection->find(array(),array('title','alias'));		//Выборка только 2х столбцов
	foreach ($cursor as $doc) {
		echo '<option value='.$doc['alias'].'>'.$doc['title'].'</option>';//Вывод списка городов
	}
	echo '</select>';
	
	if(isset($_POST['del']) || isset($_POST['save']))
	{
		$city_col = $db->getCollection('mycity');							
		$city_cursor = $city_col->find();								//Выборка избранных городов
		$i=1;
		foreach ($city_cursor as $city_doc) {	
			/* Удаление города из списка по его id */
			if(isset($_POST['del'])){
				foreach($_POST['del'] as $index => $var){
					if($index == $i){
						$city_col->remove(array('cityalias' => $city_doc['cityalias']));
					}
				}
			}
			/* Сохранение города после редактирования в списке по его id */
			if(isset($_POST['save'])){
				$cursor = $collection->find(array('alias' => $_POST['textalias'])); //Проверка на существование алиаса
				if($cursor->count() > 0) {
					foreach($_POST['save'] as $index => $var) {
						if($index == $i) {
							$newdata = array('$set' => 
							array("cityalias" => $_POST['textalias'],
								"cityname" => $_POST['texttitle']));
							$city_col->update(array('cityalias' => $city_doc['cityalias']),$newdata);
						}
					}
				}
			}
			$i++;
		}
	}
	/* Вывод таблицы со списком избранных городов */
	echo '<table cellpadding="5" width="100%" id="cities">
		<tr>
			<td>№</td>
			<td>Город</td>
			<td></td>
			<td></td>
		</tr>';
		$city_col = $db->getCollection('mycity');
		if($db->isCollectionEmpty($city_col) == false){
			$city_cursor = $city_col->find();
			$i=1;
			/* Вывод списка городов */
			foreach ($city_cursor as $city_doc) {
				echo '<tr>
					<td>'.$i.'</td>';
				if(isset($_POST['edit'])){	
					/* Вывод полей для редактирования города */
					foreach($_POST['edit'] as $index => $var){
						if($index == $i) {
							echo '<td><input type="text" name="texttitle" value="'.$city_doc['cityname'].'"><input type="text" name="textalias" value="'.$city_doc['cityalias'].'"></td>';
							echo '<td><button type="submit" name="save['.$i.']">Сохранить</button></td>';
						} else {
							echo '<td><a href="info.php">'.$city_doc['cityname'].'</a></td>';
							echo '<td><button type="submit" name="edit['.$i.']">Изменить</button></td>';
						}
					}
				} else {
					echo '<td><a href="info.php?id='.$city_doc['_id'].'">'.$city_doc['cityname'].'</a></td>';
					echo '<td><button type="submit" name="edit['.$i.']">Изменить</button></td>';
				}	
				echo '<td><button type="submit" name="del['.$i.']">Удалить</button></td>
				</tr>'; 
				$i++;
			}
		}
	echo '</table>
	</form>
	</div>';
	$db->close();								//Закрытие подключения к БД
	exit();
?>
