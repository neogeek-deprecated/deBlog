<?php

if (!class_exists('deBlog')) {
	
	if (!defined('de_root_dir')) {
		define('de_root_dir', '/');
	}
	
	if (!defined('de_content_xpath')) {
		define('de_content_xpath', '//div[@id="content"]');
	}
	
	if (!defined('de_apc_cache_timeout')) {
		define('de_apc_cache_timeout', 0);
	}
	
	class deBlog
	{
		
		public $cwd;
		
		public $dom;
		
		public $uid;
		
		public $dir;
		public $page;
		
		public $url;
		
		public $content;
		
		public function __construct () {
			
			$this->cwd = getcwd();
			
			$this->dom = new DOM;
			
			$this->uid = 'html_' . sha($_SERVER['PATH_INFO']);
			
			$this->dir = path_info();
			$this->page = path_info(1);
			
			$this->url = $this->URL();
			
			if (!isset($_GET['nocache']) && function_exists('apc_fetch') && $html = apc_fetch($this->uid)) {
				
				@$this->dom->loadHTML($html);
				
				$this->content = $this->dom->query(constant('de_content_xpath'))->item(0);
				
				return;
				
			}
			
			@$this->dom->loadHTMLFile('index.html');
			
			$this->content = $this->dom->query(constant('de_content_xpath'))->item(0);
			
			if ($this->dir && is_dir($this->dir)) {
				
				$links = $this->dom->query('//a[@href="' . constant('de_root_dir') . $this->dir . '/"]');
				
				foreach ($links as $link) {
					
					$link->setAttribute('class', 'active');
					
				}
				
				if ($this->page && is_file($this->dir . '/' . $this->page . '.html')) {
					
					$this->Import($this->dir . '/header.html');
					$this->Import($this->dir . '/' . $this->page . '.html');
					$this->Import($this->dir . '/footer.html');
					
					$this->Description();
					
				} else {
					
					$this->Import($this->dir . '/index.html');
					
				}
				
				$this->Title();
				
				$link_tag = $this->dom->query('//link[@rel="canonical"]')->item(0);
					
				if ($link_tag) {
					
					$link_tag->setAttribute('href', $this->url);
					
				}
				
			}
			
			if (function_exists('apc_store')) {
				
				apc_store($this->uid, $this->dom->saveHTML(), constant('de_apc_cache_timeout'));
				
			}
			
		}
		
		public function Import ($file) {
			
			if (is_file($file)) {
				
				$this->content->appendChild($this->dom->import($file));
				
			}
			
		}
		
		public function Title () {
			
			$title_tag = $this->dom->getElementsByTagName('title')->item(0);
			
			$h1_tag = $this->dom->query(constant('de_content_xpath') . '//h1')->item(0);
			
			if ($title_tag && $h1_tag) {
				
				$title_tag->nodeValue = htmlentities($h1_tag->nodeValue) . ' - ' . $title_tag->nodeValue;
				
			}
			
		}
		
		public function Description () {
			
			$meta_tag = $this->dom->query('//meta[@name="description"]')->item(0);
			
			$p_tag = $this->dom->query(constant('de_content_xpath') . '//p')->item(0);
			
			if ($meta_tag && $p_tag) {
				
				$meta_tag->setAttribute('content', htmlentities($p_tag->nodeValue));
				
			}
			
		}
		
		public function URL () {
			
			preg_match('/^[a-z]+/i', $_SERVER['SERVER_PROTOCOL'], $protocol);
			
			$url = strtolower($protocol[0]) . '://' . preg_replace('/\/+/', '/', $_SERVER['HTTP_HOST'] . constant('de_root_dir') . $_SERVER['PATH_INFO']);
			
			return $url;
			
		}
		
		public function __destruct () {
			
			if ($this->dom) {
				
				if ($this->content && !$this->content->childNodes->length) {
					
					$this->Import($this->cwd . '/404.html');
					
				}
				
				if (!headers_sent()) {
					
					header('Content-type: text/html; charset=utf-8');
					
				}
				
				echo $this->dom->saveHTML();
				
			}
			
		}
		
	}
	
}

?>