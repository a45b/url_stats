<?php

error_reporting(E_ALL ^ E_WARNING);

require('simple_html_dom.php');

class Solution
{
	private $url;
	private $total_size = 0;
	private $total_request = 0;

	public function __construct(){
		
	}

	public function setUrl($url){
		$this->url = $url;
		$this->solve($url);
	}

	public function solve($url){

		if(!$this->is_html($url)){
			$this->total_size += $this->getFileSize($url);
		}
		else{
			$html = file_get_html($url);
			$this->getElementInfo($html);
		}		
	}


	public function getElementInfo($html){

		foreach($html->find('img') as $element){
			$this->total_size += (int)$this->getFileSize($element->src);
			$this->total_request += 1;
		}

		foreach($html->find('link') as $element){		
			if (strpos($element->href,'.css') !== false) {				
				$this->total_size += (int)$this->getFileSize($element->href);					
				$this->total_request += 1;
			}
		}

		foreach($html->find('script') as $element){
			if (strpos($element->src,'.js') !== false) {				
				$this->total_size += (int)$this->getFileSize($element->src);
				$this->total_request += 1;
			}
		}

		foreach($html->find('iframe') as $element){
			$this->solve($element->src);
		}
		
	}

	public function is_html($url){		
		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => array('User-Agent: Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.5; en-US; rv:1.9.1.3) Gecko/20090824 Firefox/3.5.3'),
			CURLOPT_HEADER => true,
			CURLOPT_NOBODY => true,
		));
		
		$data = curl_exec($ch);
		$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
		curl_close($ch);

		if (strpos($contentType,'text/html') !== false){		
			return TRUE;
		}
		else{
			return FALSE;	
		}		
	}

	public function getFileSize($url){

		$headers = get_headers($url, 1);

	    if (isset($headers['Content-Length'])){
	    	return $headers['Content-Length'];	
	    }	    
	    elseif (isset($headers['Content-length'])){
	    	return $headers['Content-length'];	
	    }	
	    else{
			$c = curl_init();
			curl_setopt_array($c, array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HTTPHEADER => array('User-Agent: Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.5; en-US; rv:1.9.1.3) Gecko/20090824 Firefox/3.5.3'),
			));
			curl_exec($c);

			$size = curl_getinfo($c, CURLINFO_SIZE_DOWNLOAD);              
			curl_close($c);
			return $size;	 
	    }        	   		
	}

	public function getUrl(){
		return $this->url;
	}

	public function getTotalSize(){
		return $this->total_size;
	}

	public function getTotalRequest(){
		return $this->total_request;
	}
}



if (!isset($argv) || empty($argv[1])) {
    echo "Error Input!!!\n";
	echo "e.g. php solution.php http://www.example.com";
    die;
}

$answer = new Solution();
$answer->setUrl($argv[1]);

echo "Total size ".$answer->getTotalSize()." and Total Request ".$answer->getTotalRequest();


?>
