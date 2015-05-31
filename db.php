<?php 
class DB {
    private $db;
    private $connection;
    public function __construct($host, $dbname) {
        $this->connect($host,$dbname);    
    }
    private function connect($host,$dbname) {                        
        try {
            $this->connection = new MongoClient($host);
            return $this->db = $this->connection->selectDB($dbname);
        } catch(Exception $e) {
            return false;
        }
    }
    public function close(){
        $this->connection->close();
    }
    
    public function getCollection($collection) {
        return $this->db->selectCollection($collection);
    }
    
    public function isCollectionEmpty($col) {
        $cursor = $col->find();
        if ($cursor->count() == 0) {
            return true;
        } else {
            return false;
        }
    }
}