<?php
	
	include('curl.php');
	$time= microtime(true);
	$curl = new curl_tools;
	$curl->url = "https://tw.stock.yahoo.com/h/getclass.php";
	$curl->cookie = "stock";
	$curl->logs_folder = "C:/xampp/htdocs/tools/logs/sotck";

	class curl_thread extends Collectable {

	  public function __construct($curl,$times){
	    $this->curl = $curl;
	    $this->times = $times;
	  }

	  public function run(){
	  	echo $this->times.PHP_EOL;
	   	$this->data = $this->curl->curl();
	   	$this->process_data();
	   	$this->setGarbage();
	  }

	  public function getResult(){
	  	return $this->data;
	  }

	  public function process_data(){
		
	  }

	}

	$data = $curl->curl();
	preg_match_all("/<table\swidth=\"100%\"\sid=\"market\">(.*?>)<\/table>.*?yk/is", $data, $match);
	preg_match_all("/<table\sborder=\"0\"\scols=2\swidth=100%\scellspacing=\"0\"\scellpadding=\"7\">(.*?)<\/table>/is", $match[1][0], $table);
	$link = [];
	foreach ($table[1] as $k => $v) {
		preg_match_all("/<a\shref=\"(?<link>.*?)\">(?<name>.*?)</is", $v, $list);
		foreach ($list['link'] as $k2 => $v2) {
			$link[] = $v2;
		}
	}

	$pool_limit = 50;
	$total = 0;
	$pool = new Pool($pool_limit);
	$pool_list = [];
	echo count($link).PHP_EOL;
	foreach ($link as $k => $v) {
		$curl->url = "https://tw.stock.yahoo.com".$v;
		$pool_list[] = new curl_thread($curl,$k);	
	}
	$count = 0;
	foreach ($pool_list as $task) {
		$pool->submit($task);
	}
	$return = [];
	$pool->shutdown();
	foreach ($pool_list as $task) {
		print_r("task:".$task->getResult());
		$return[] = $task->getResult();
	}
	print_r($return);
	$pool->collect(function($checkingTask){
		  return $checkingTask->isGarbage();
		});
	echo "TOTAL TIME:".(microtime(true)-$time).PHP_EOL;
?>