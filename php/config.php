<?php
class Database {
    public $con;

   
    public function __construct() {
        
        $hostname = 'localhost';  
        $username = 'root';       
        $password = '';           
        $dbname = 'useraccount';

        
        $this->con = new mysqli($hostname, $username, $password, $dbname);

      
        if ($this->con->connect_error) {
            die("Koneksi gagal: " . $this->con->connect_error); 
        }
    }

    
    public function query($sql) {
        return $this->con->query($sql);
    }
    
   
    public function escape($data) {
        return $this->con->real_escape_string($data);
    }
}
?>
