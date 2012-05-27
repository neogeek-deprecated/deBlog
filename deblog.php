<?php

if (!class_exists('deBlog')) {

	class deBlog
	{
		
		public $cwd;
		
		public $dom;
		
		public $dir;
		public $page;
		
		public $url;
		
		public $content;
		
		public function __construct() {
			
			$this->cwd = getcwd();
			
			$this->dom = new DOM;
			@$this->dom->loadHTMLFile('index.html');
			
			$this->dir = path_info();
			$this->page = path_info(1);
			
			$this->url = $this->URL();
			
			$this->content = $this->dom->getElementById('content');
			
			if (is_dir($this->dir)) {
				
				foreach ($this->dom->query('//a[@href="/' . $this->dir . '/"]') as $link) {
					
					$link->setAttribute('class', 'active');
					
				}
				
				if (!$this->page) {
					
					$this->Import($this->dir . '/index.html');
					
					$this->Title();
					
				} else if ($this->page && is_file($this->dir . '/' . $this->page . '.html')) {
					
					$this->Import($this->dir . '/header.html');
					$this->Import($this->dir . '/' . $this->page . '.html');
					$this->Import($this->dir . '/footer.html');
					
					$this->Title();
					$this->Description();
					
				}
				
			}
			
		}
		
		public function Import($file) {
			
			if (is_file($file)) {
				
				$this->content->appendChild($this->dom->import($file));
				
			}
			
		}
		
		public function Title () {
			
			$h1_tag = $this->dom->query('//*[@id="content"]//h1')->item(0);
			
			$title_tag = $this->dom->getElementsByTagName('title')->item(0);
			
			if ($title_tag && $h1_tag) {
				
				$title_tag->nodeValue = htmlentities($h1_tag->nodeValue) . ' - ' . $title_tag->nodeValue;
				
			}
			
		}
		
		public function Description () {
			
			$p_tag = $this->dom->query('//*[@id="content"]//p')->item(0);
			
			$meta_tag = $this->dom->query('//meta[@name="description"]')->item(0);
			
			if ($meta_tag && $p_tag) {
				
				$meta_tag->setAttribute('content', htmlentities($p_tag->nodeValue));
				
			}
			
		}
		
		public function URL () {
			
			preg_match('/^[a-z]+/i', $_SERVER['SERVER_PROTOCOL'], $protocol);
			
			$url = strtolower($protocol[0]) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['PATH_INFO'];
			
			$link_tag = $this->dom->query('//link[@rel="canonical"]')->item(0);
			
			if ($link_tag) {
				
				$link_tag->setAttribute('href', $url);
				
			}
			
			return $url;
			
		}
		
		public function __destruct() {
			
			if ($this->content && !$this->content->childNodes->length) {
				
				$this->Import($this->cwd . '/404.html');
				
			}
			
			echo $this->dom->saveHTML();
			
		}
		
	}
	
}

?>