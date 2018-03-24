<?php

include('tools.php');

class curl_tools extends tools{

	public $header;
	public $cookie_folder;
	public $curl_timeout;
	public $url;
	public $post;
	public $data;
	public $cookie;
	public $useragent;
	public $encoding;
	public $log_file;
	private $curl_error;
	private $curl_errorno;

	public function __construct(){
		parent::__construct();
		$this->header = [
	                    'accept: application/json, text/javascript, */*; q=0.01',
	                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64; rv:53.0) Gecko/20100101 Firefox/53.0',
	                    'accept-Language: zh-TW,zh;q=0.8,en-US;q=0.5,en;q=0.3',
	                    'accept-Encoding: gzip, deflate, br',
	                    'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
	                    'Connection: keep-alive'
	                   ];
	    $this->cookie_folder = "C:/xampp/htdocs/tools/cookie";
	    $this->curl_timeout = 30000;
	    $this->url = "https://www.google.com.tw";
	    $this->cookie = 'google';
	    $this->useragent = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.89 Safari/537.36';
	    $this->encoding = "UTF-8";
	    
	}

	public function curl(){
		if(!is_dir($this->cookie_folder)){
			mkdir($this->cookie_folder);
		}
		$this->log_file = $this->cookie.date("YmdHis");
		$ch = curl_init();
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $this->header);
	    curl_setopt($ch, CURLOPT_HEADER,0);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	    curl_setopt($ch, CURLOPT_URL, $this->url);
	    curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	    curl_setopt($ch, CURLOPT_COOKIESESSION, TRUE); 
	    curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);
	    curl_setopt($ch, CURLOPT_ENCODING, $this->encoding);
	    curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
	    curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE);
	    curl_setopt($ch, CURLOPT_TIMEOUT, $this->curl_timeout);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_folder."/".$this->cookie);
	    curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_folder."/".$this->cookie);
	    if($this->post){
	        curl_setopt($ch, CURLOPT_POST, 1); 
	        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->post));
	    }
	    $contents = curl_exec($ch);
	    if(mb_detect_encoding($contents)!='UTF-8'){
	    	$contents = iconv("big5", "UTF-8", $contents);	
	    }
	    $this->_logs($this->log_file,'-----'.$this->cookie.'------');
	    $this->_logs($this->log_file,'URL: '.$this->url);
	    $this->_logs($this->log_file,'DATA: '.$this->post);
	    if(curl_error($ch)!=''){
	    	$this->curl_error = curl_error($ch);
	    	$this->curl_errno = curl_errno($ch);
	    	$this->_logs($this->log_file,'CURL ERRORNO: '.$this->curl_errno);
	    	$this->_logs($this->log_file,'CURL ERROR: '.$this->curl_error);
	    }
	    $this->_logs($this->log_file,$contents);
	    $this->_logs($this->log_file,'-----------------');
	    
	    curl_close($ch);
	    return $contents;
	}
    
}

?>