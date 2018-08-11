<?php
	
	include('curl.php');
	class reptile extends curl_tools{
		
		function __construct(){
			parent::__construct();
			$this->trace = [];
			$this->link_trace = [];
			$this->script_trace = [];
			$this->img_trace = [];
		}

		public function run(){
			$this->url = $this->main_url;
			$content = $this->curl();
			$this->dom = new DOMDocument();
			$this->dom->encoding = 'UTF-8';
			@$this->dom->loadHTML($content);
			if(!is_dir($this->file_folder)){
				mkdir($this->file_folder);
			}
			$this->get_sub_file($content);
			$file = $this->file_folder."/index.html";
			if($this->file_check($file)){
				$this->create_file($file,$content);	
			}
			$this->get_page_link();
		}

		public function get_sub_file($content){
			$this->get_style_link();
			$this->get_script();
			$this->get_img();
			$this->get_css_img($content);
		}

		public function create_file($file,$data){
			if(!is_file($file)){
				echo "CREATE FILE".$file.PHP_EOL;
				error_log($data,3,$file);	
			}
		}

		public function file_check($file){
			if(is_file($file)){
				echo "FILE EXISTS".$file.PHP_EOL;
				return false;
			}else{
				return true;
			}
		}

		public function get_page_link(){
			$a = $this->dom->getElementsByTagName('a');
			foreach ($a as $k => $v) {
				$link = $v->getAttribute('href');
				$link_analysis	=	parse_url($link);
				print_r($link_analysis);
				if($link_analysis && (!isset($link_analysis['scheme']) || in_array($link_analysis['scheme'], ['http','https'])) && (!isset($link_analysis['host']) || preg_match("/".$link_analysis['host']."/is", $this->main_url)) ){
					$tmp_route = explode("/", $link_analysis['path']);
					$tmp_sub_name = explode("?", array_pop($tmp_route));
					$sub_name = $this->customer_sub_name(explode(".",$tmp_sub_name[0])[0]);
					if($sub_name){
						$file = $this->file_folder."/".$sub_name.".html";
						$this->_echo($file);
						if($this->file_check($file)){
							if(!isset($link_analysis['host'])){
								$this->url = $this->main_url."/".$link;
							}else{
								$this->url = $link;
							}
							$content = $this->curl();
							$this->create_file($file,$content);
							@$this->dom->loadHTML($content);
							$this->get_sub_file($content);
						}	
					}
				}
			}
		}

		public function customer_sub_name($sub_name){
			switch ($sub_name) {
				case '.':
					$new_sub_name	=	'index';
					break;
				
				default:
					$new_sub_name	=	$sub_name;		
					break;
			}
			return $new_sub_name;
		}

		public function get_style_link(){
			$link = $this->dom->getElementsByTagName('link');
			foreach ($link as $k => $v) {
				$href = $v->getAttribute('href');
				if(!in_array($href, $this->link_trace)){
					$href_analysis	=	parse_url($href);
					$tmp = explode("/", $href_analysis['path']);
					$file_name = explode("?",array_pop($tmp))[0];
					$folder = "";
					foreach ($tmp as $k => $v2) {
						$folder .= $v2."/";
						if(!is_dir($this->file_folder."/".$folder)){
							mkdir($this->file_folder."/".$folder);
						}
					}
					$file = $this->file_folder."/".$folder.$file_name;
					if($this->file_check($file)){
						if(!isset($href_analysis['host'])){
							$this->url = $this->main_url."/".$href;
						}else{
							$this->url = $href;
						}
						$data = $this->curl();
						$this->create_file($file,$data);
						$this->get_css_img($data,$tmp[0]);
						$this->get_import_css($data,$tmp[0]);
					}
					$this->link_trace[] = $href;
				}
			}
			
		}

		public function get_script(){
			$script = $this->dom->getElementsByTagName('script');
			foreach ($script as $k => $v) {
				$src = $v->getAttribute('src');
				if($src && !in_array($src, $this->script_trace)){
					$src_analysis	=	parse_url($src);
					$tmp = explode("/", $src_analysis['path']);
					$file_name = explode("?",array_pop($tmp))[0];
					$folder = "";
					foreach ($tmp as $k => $v2) {
						$folder .= $v2."/";
						if(!is_dir($this->file_folder."/".$folder)){
							mkdir($this->file_folder."/".$folder);
						}
					}
					$file = $this->file_folder."/".$folder.$file_name;
					if($this->file_check($file)){
						if(!isset($src_analysis['host'])){
							$this->url = $this->main_url."/".$src;
						}else{
							$this->url = $src;
						}
						$data = $this->curl();
						$this->create_file($file,$data);
					}
					$this->script_trace[] = $src;
				}
			}
		}

		public function get_css_img($html,$sub=''){
			preg_match_all("/\((\"|')?(?<img_url>([a-zA-Z0-9\/\.\-_])+\.(png|jpg|gif))(\"|')?\)/is", $html ,$img);
			if(count($img['img_url'])>1){
				foreach ($img['img_url'] as $k => $src) {
					if($src && !in_array($src, $this->img_trace)){
						$tmp = explode("/", $src);
						$file_name = array_pop($tmp);
						$folder = ($sub)?$sub."/":"";
						foreach ($tmp as $k => $v2) {
							$folder .= $v2."/";
							if(!is_dir($this->file_folder."/".$folder) && $v2!="."){
								mkdir($this->file_folder."/".$folder);
							}
						}
						$file = $this->file_folder."/".$folder.$file_name;
						if($this->file_check($file)){
							$this->url = $this->main_url."/".$folder.$file_name;
							$data = $this->curl();
							$this->create_file($file,$data);
						}
						$this->img_trace[] = $src;
					}
				}
			}
		}

		public function get_import_css($html,$sub){
			preg_match_all("/@import\s(\"|')(?<css>\w+\.css)(\"|')/is", $html, $css);
			if(count($css['css'])>1){
				foreach ($css['css'] as $k => $v4) {
					$tmp_css = explode("/", $v4);
					$file_name = array_pop($tmp_css);
					$folder = $sub."/";
					foreach ($tmp_css as $k => $v2) {
						$folder .= $v2."/";
						if(!is_dir($this->file_folder."/".$folder) && $v2=="."){
							mkdir($this->file_folder."/".$folder);
						}
					}
					$file = $this->file_folder."/".$folder.$file_name;
					$this->url = $this->main_url."/".$folder.$file_name;
					$data = $this->curl();
					$this->create_file($file,$data);
					$this->get_css_img($data,$sub);
				}	
			}
		}

		public function get_img(){
			$img = $this->dom->getElementsByTagName('img');
			foreach ($img as $k => $v) {
				$src = $v->getAttribute('src');
				if($src && !in_array($src, $this->img_trace)){
					$src_analysis	=	parse_url($src);
					if(!preg_match("/png|jpg|gif/is", $src_analysis['path'])){
						$this->_echo("忽略檔案");
						$this->_echo($src_analysis);
						continue;
					}
					$tmp = explode("/", $src_analysis['path']);
					$file_name = explode("?",array_pop($tmp))[0];
					$folder = "";
					foreach ($tmp as $k => $v2) {
						if($v2){
							$folder .= $v2."/";
							if(!is_dir($this->file_folder."/".$folder)){
								mkdir($this->file_folder."/".$folder);
							}
						}
						
					}
					$file = $this->file_folder."/".$folder.$file_name;
					if($this->file_check($file)){
						if(!isset($src_analysis['host'])){
							$this->url = $this->main_url."/".$src;
						}else{
							$this->url = $src;
						}
						$data = $this->curl();
						$this->create_file($file,$data);
					}
					$this->img_trace[] = $src;
				}
			}
		}
	}

	$reptile = new reptile();
	$reptile->main_url = "";
	$reptile->cookie = "";
	$reptile->encoding = '';
	$reptile->file_folder = __DIR__."/".$reptile->cookie;
	$reptile->logs_folder = __DIR__."/logs/".$reptile->cookie;
	$reptile->run();

?>