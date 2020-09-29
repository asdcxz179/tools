<?php
	require 'vendor/autoload.php';

	use GuzzleHttp\Client;
	use GuzzleHttp\Pool;
	use GuzzleHttp\Psr7\Request;
	use GuzzleHttp\Psr7\Response;
	use GuzzleHttp\Exception\RequestException;
	/**
	 * 
	 */
	class guzzle_tools
	{
		public $client;
		public $base_uri	=	'https://www.google.com/';
		public $timeout 	=	30000;
		public $concurrency 		=		50;

		function __construct()
		{
			$this->client 	=	new Client([
				'base_uri'	=>	$this->base_uri,
				'timeout'	=>	$this->timeout,
			]);
		}

		public function pool($function){
			$pool =		new Pool($this->client,$function(),[
				'concurrency'	=>	$this->concurrency,
				'fulfilled'		=>	function($response,$index){
					echo $index.PHP_EOL;
				},
				'rejected'		=>	function(RequestException $reason,$index){

				}
			]);
			$promise = $pool->promise();
        	$promise->wait();
		}

		public function run(){
			$f 	=	function(){
				for($i=0;$i<=100;$i++){
					yield function(){
						return $this->client->getAsync($this->base_uri);
					};
				}
			};
			$this->pool($f);


		}


	}

	$guzzle_tools 	=	new guzzle_tools();
	$guzzle_tools->run();