<?php

class reptile_db{

	public $db;

	public function __construct($host,$dbname,$dbuser,$dbpassword){
		$this->db = new PDO('mysql:host='.$host.';charset=utf8;dbname='.$dbname,$dbuser,$dbpassword,array(
				PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8", PDO::ATTR_TIMEOUT => 5,
			));
	}

	public function create_table($data,$table){
		$sql = "CREATE TABLE ".$table."(`id` int(20) NOT NULL AUTO_INCREMENT";
		foreach ($data as $k => $v) {
			if(gettype($v)=="string"){
				$sql .= ",`".$k."` varchar(".(strlen($v)*10).")";	
			}else{
				$sql .= ",`".$k."` ".gettype($v)."(".(strlen($v)*10).")";
			}
		}
		$sql .= ",PRIMARY KEY (`id`));";
		$result = $this->db->query($sql);
		return $result;
	}

	public function check_field($data,$table){
		$add_field = array();
		$sql = "SELECT column_name FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name='".$table."'";
		$result = $this->db->query($sql);
		$fields = $result->fetchAll(PDO::FETCH_ASSOC);

		foreach ($data as $k => $v) {
			$check = false;
			foreach ($fields as $key => $fields_arr) {
				if($fields_arr['column_name']==$k){
					$check=true;
				}
			}
			if(!$check){
				$add_field[] = $k;
			}
		}
		return $add_field;
	}

	public function create_field($data,$table){
		$check_field = $this->check_field($data,$table);
		$sql = "ALTER TABLE ".$table." ";
		$alter_arr = array();
		foreach ($check_field as $k => $v) {
			if(gettype($data[$v])=='string'){
				$alter_arr[] = "ADD ".$v." varchar(".(strlen($data[$v])*10).")";
			}else{
				$alter_arr[] = "ADD ".$v." ".gettype($data[$v])."(".(strlen($data[$v])*10).")";
			}
		}
		$sql .= implode(",", $alter_arr).";";
		$result = $this->db->query($sql);
		return $result;
	}

	public function insert_data($data,$table){
		$fields = implode(",", array_keys($data));
		$values = implode("','",$data);
		$sql = "INSERT INTO ".$table." (".$fields.") VALUES ('".$values."');";
		$result = $this->db->query($sql);
		if(!$result){
			$error = $this->db->errorInfo();
			$match = preg_match("/Table.*doesn't\sexist/is", $error[2]);
			if($match){
				$result = $this->create_table($data,$table);
				$this->insert_data($data,$table);
			}
			$match = $match = preg_match("/Unknown\scolumn.*field\slist/is", $error[2]);
			if($match){
				$result = $this->create_field($data,$table);
				$this->insert_data($data,$table);
			}
		}
	}

}

?>