<?php

class tools{

	public $logs_folder;

	public function __construct(){
		$this->logs_folder = "C:/xampp/htdocs/tools/logs";
	}

	public function _echo($str){
		if(is_array($str)){
			print_r($str);
		}else{
			echo $str.PHP_EOL;
		}
	}

	public function _logs($filename,$data){
		if(!is_dir($this->logs_folder)){
			mkdir($this->logs_folder);
		}

		if(is_array($data)){
			$data = json_encode($data);
		}

		$file = fopen($this->logs_folder."/".$filename, 'a+');
		fwrite($file, '['.date('Y-m-d H:i:s').']'.$data.PHP_EOL);
	}

}

?>