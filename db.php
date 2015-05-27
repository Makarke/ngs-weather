<?php 
class DB {
	private $db;
	private $connection;
	public function __construct($host,$dbname) {					//Конструктор с подключением к БД
		$this->connect($host,$dbname);	
	}
	private function connect($host,$dbname) {						
		try {
			$this->connection = new MongoClient($host);				//Подключение к бд
	    	return $this->db = $this->connection->selectDB($dbname);//Выбор базы
		} catch(Exception $e) {
			return false;
		}
	}
	public function close(){
		$this->connection->close();									//Закрытие соединения
	}
	
	public function getCollection($collection) {
		return $this->db->selectCollection($collection);			//Выбор коллекции
	}
	
	public function isCollectionEmpty($col) {						//Проверка на наличие док-тов в коллекции
		$cursor = $col->find();
		if($cursor->count() == 0) {
			return true;
		} else {
			return false;
		}
			
	}
}