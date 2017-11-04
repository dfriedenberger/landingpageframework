<?php

class Tracking
{

    private $userid = "";
    private $logfile;
    private $pdo;

    public function __construct($userid,$path) {
        
		$this->userid = $userid;
		$this->logfile = $path."/tracking.txt";
		$this->pdo = new PDO('sqlite:'.$path.'/tracking.db', '', '', array(
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		));
		

	    $this->pdo->exec(
		   'CREATE TABLE IF NOT EXISTS events (
                        userid VARCHAR (48) NOT NULL,
                        date DATETIME NOT NULL,
						event VARCHAR (64) NOT NULL,
						args TEXT
                      )'
		);
    
	     $this->pdo->exec(
		   'CREATE TABLE IF NOT EXISTS properties (
                        userid VARCHAR (48) NOT NULL,
                        date DATETIME NOT NULL,
						key VARCHAR (64) NOT NULL,
						value TEXT,
						current INTEGER
                      )'
		);
	
	}

	
	
    public function log($data) {

     $date = date(DATE_RFC822);

     $line = $date . " userid=".$this->userid." ".$data;
     file_put_contents($this->logfile,$line.PHP_EOL, FILE_APPEND);

    }
	
	public function raise($event,$args) {

   	  $this->log("[".$event."] ".$args);
      
	  //enter event
      $date = date("Y-m-d H:i:s");

	  $sql = 'INSERT INTO events(userid, date,event,args) VALUES(:userid, :date, :event, :args)';
      $stmt = $this->pdo->prepare($sql);
      $stmt->bindValue(':userid', $this->userid);
      $stmt->bindValue(':date', $date);
      $stmt->bindValue(':event', $event);
      $stmt->bindValue(':args', $args);
      $stmt->execute();
 
    }

	public function set($key,$value) {

	  
	  $this->log($key."=".$value);
	  //check if values changed
      $sql = "SELECT count(*) FROM properties WHERE userid = :userid AND key = :key AND value = :value AND current = 1"; 
      
	  $stmt = $this->pdo->prepare($sql);
      $stmt->bindValue(':userid', $this->userid);
      $stmt->bindValue(':key', $key);
      $stmt->bindValue(':value', $value);
      $stmt->execute();
      $number_of_rows = $stmt->fetchColumn(); 
	  
	  if($number_of_rows == 1)
	  {
		  //Not changed
		  return;
	  }
  
      $sql = "UPDATE properties SET current = 0 WHERE userid = :userid AND key = :key"; 
  	  $stmt = $this->pdo->prepare($sql);
      $stmt->bindValue(':userid', $this->userid);
      $stmt->bindValue(':key', $key);
      $stmt->execute();
  
      $date = date("Y-m-d H:i:s");

      $sql = 'INSERT INTO properties(userid, date,key,value,current) VALUES(:userid, :date, :key, :value,1)';
      $stmt = $this->pdo->prepare($sql);
      $stmt->bindValue(':userid', $this->userid);
      $stmt->bindValue(':date', $date);
      $stmt->bindValue(':key', $key);
      $stmt->bindValue(':value', $value);
      $stmt->execute();
 
    }


}
?>