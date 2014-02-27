<?php

class RESTFirebase {
	public function __construct($secret,$base){
		$tokenGen = new Services_FirebaseTokenGenerator($secret);
		$this->base = $base;
		$this->token = $tokenGen->createToken(array("id" => $this->base));
		$this->ch = curl_init();
	}
	
	protected function init($path){
		curl_setopt($this->ch, CURLOPT_URL, "https://{$this->base}.firebaseio.com/$path.json?auth={$this->token}");
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
	}
	
	public function put($path, $data){
		$this->init($path);
		
		$putFileName = 'put.json';
		$json = json_encode($data);
		file_put_contents($putFileName, $json);
		$putFileResource = fopen($putFileName, 'r');
			
		curl_setopt($this->ch, CURLOPT_PUT, true);
		curl_setopt($this->ch, CURLOPT_INFILE, $putFileResource);
		curl_setopt($this->ch, CURLOPT_INFILESIZE, strlen($json));
		
		$this->exec();
	}
	
	public function post($path, $data){
		$this->init($path);
		
		curl_setopt($this->ch, CURLOPT_POST, true);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($data));
		
		$this->exec();
	}
	
	protected function exec(){
		$response = curl_exec($this->ch);
		
		$error = curl_error($this->ch);
		$info = curl_getinfo($this->ch);
		
		if ( $info['http_code'] != 200 ) throw new Exception($error);
	}
}