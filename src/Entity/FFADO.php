<?php

namespace FidelizeFood\Entity;

abstract class FFADO extends \ADOdb_Active_Record{
	
	protected $conn = null;
	
	private $nameId = "";
	
	public function __construct(){
		global $db;
		
		parent::__construct();
		
		if($db==null){
			echo "db not connected";		
			return;
		}
		
		$this->conn = $db;		
	}
	
	public function getConn(){		
		
		return $this->conn;
	}
	
	public function save(){
   	
		$nameId = $this->getNameId();
   			
		if($this->$nameId == "")
			$this->$nameId = $this->conn->nextId($this->getNameTable() . "_" .  $this->getNameId() . "_seq"); 
   	
		return parent::save();
	}
	
	protected function getNameTable(){
		return $this->_table;
	}	
	
	protected function getNameId(){
		return $this->nameId;   	
	}
	
	public function LoadById($id){
   	
		return $this->Load($this->getNameId() . " = '" . $id."'");
	}
   
	public function lastInsertID(&$db, $primaryKey) {
		
		$sql = "SELECT MAX($primaryKey) AS last_id FROM " . $this->getNameTable();
		$res = $db->Execute($sql);
		
		return $res->fields("last_id");
		
	}
}
