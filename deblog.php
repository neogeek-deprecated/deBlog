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
			
			$string = $this->dom->getElementsByTagName('h1')->item(0)->nodeValue;
			
			$title = $this->dom->query('//title')->item(0);
			
			$title->nodeValue = htmlentities($string) . ' - ' . $title->nodeValue;
			
		}
		
		public function Description () {
			
			$string = $this->dom->getElementsByTagName('p')->item(0)->nodeValue;
			
			$this->dom->query('//meta[@name="description"]')->item(0)->setAttribute('content', htmlentities($string));
			
		}
		
		public function URL () {
			
			preg_match('/^[a-z]+/i', $_SERVER['SERVER_PROTOCOL'], $protocol);
			
			$url = strtolower($protocol[0]) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['PATH_INFO'];
			
			$this->dom->query('//link[@rel="canonical"]')->item(0)->setAttribute('href', $url);
			
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