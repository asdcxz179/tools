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
			$this->get_style_link();
			$this->get_script();
			$this->get_img();
			$this->get_css_img($content);
			$file = $this->file_folder."/index.html";
			if($this->file_check($file)){
				$this->create_file($file,$content);	
			}
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

		public function get_style_link(){
			$link = $this->dom->getElementsByTagName('link');
			foreach ($link as $k => $v) {
				$href = $v->getAttribute('href');
				if(!in_array($href, $this->link_trace)){
					$tmp = explode("/", $href);
					$file_name = array_pop($tmp);
					if(preg_match("/^http/is", $href)){
						$file = $this->file_folder."/".$file_name;
						if($this->file_check($file)){
							$this->url = $href;
							$data = $this->curl();
							$this->create_file($file,$data);
						}
					}else{
						$folder = "";
						foreach ($tmp as $k => $v2) {
							$folder .= $v2."/";
							if(!is_dir($this->file_folder."/".$folder)){
								mkdir($this->file_folder."/".$folder);
							}
						}
						$file = $this->file_folder."/".$folder.$file_name;
						if($this->file_check($file)){
							$this->url = $this->main_url."/".$href;
							$data = $this->curl();
							$this->create_file($file,$data);
							$this->get_css_img($data,$tmp[0]);
							$this->get_import_css($data,$tmp[0]);
						}
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
					$tmp = explode("/", $src);
					$file_name = array_pop($tmp);
					if(preg_match("/^http/is", $src)){
						$file = $this->file_folder."/".$file_name;
						if($this->file_check($file)){
							$this->url = $src;
							$data = $this->curl();
							$this->create_file($file,$data);
						}
					}else{
						$folder = "";
						foreach ($tmp as $k => $v2) {
							$folder .= $v2."/";
							if(!is_dir($this->file_folder."/".$folder)){
								mkdir($this->file_folder."/".$folder);
							}
						}
						$file = $this->file_folder."/".$folder.$file_name;
						if($this->file_check($file)){
							$this->url = $this->main_url."/".$src;
							$data = $this->curl();
							$this->create_file($file,$data);
						}
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
					$tmp = explode("/", $src);
					$file_name = array_pop($tmp);
					if(preg_match("/^http/is", $src)){
						$file = $this->file_folder."/".$file_name;
						if($this->file_check($file)){
							$this->url = $src;
							$data = $this->curl();
							$this->create_file($file,$data);
						}
					}else{
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
							$this->url = $this->main_url."/".$src;
							$data = $this->curl();
							$this->create_file($file,$data);
						}
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
	$reptile->cookie_folder = "";
	$reptile->file_folder = "".$reptile->cookie;
	$reptile->logs_folder = "".$reptile->cookie;
	$reptile->run();

?>