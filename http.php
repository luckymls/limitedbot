<?php
class http {

  
    function __construct($method, $url, $data = false, $args = false) {
        $method = strtolower($method);
        
        if ($method == "post" || $method == "get") $this->method = $method;
        

        $this->url  = $url;
        $this->data = $data;
        $this->doRequest();
    }



    function getResponse() {
        return $this->response;
    }

   



    private function doRequest() {$this->doCurl();}

    private function doCurl() {
        $c = curl_init();



        curl_setopt($c, CURLOPT_URL, $this->url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER ,true);
        curl_setopt($c, CURLOPT_FOLLOWLOCATION, $this->args[followRedirect]);
        curl_setopt($c, CURLOPT_HTTPHEADER, array('Expect:'));
		curl_setopt($c, CURLOPT_HEADERFUNCTION, array(&$this,'readHeader'));

        if($this->method == "post") {  
            curl_setopt($c, CURLOPT_POST, true);

            curl_setopt($c, CURLOPT_POSTFIELDS, http_build_query($this->data));
        }




        $this->response    = curl_exec($c);
		$this->headers     = array_merge($this->requestInfo, $this->headers);
        curl_close($c);
    }

	private function readHeader($ch, $header) {
        $key = trim(substr($header, 0, strpos($header, ":")));
        $val = trim(substr($header, strpos($header, ":") + 1));
        if (!empty($key) && !empty($val)) {
            $this->headers[$key] = $val;
        }
        return strlen($header);
	}
	
	function getHeaders($key = false) {
        if ($key) {
            return $this->headers[$key];
        } else {
    		return $this->headers;
        }
	}
}
?>

