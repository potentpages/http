<?php 
class Http {
    private $multiHandle;
    private $requests;
    
    public function clear() {
        $this->requests = Array();
        
        //Reset multi handle
        curl_multi_close ( $this->multiHandle );
        $this->multiHandle = curl_multi_init();
    }
    
    public function __construct() {
        $this->multiHandle = curl_multi_init();
        $this->clear();
    }
    
    public function __descruct() {
        curl_multi_close ( $this->multiHandle );
    }
    
    public function parseResponse($text) {
        $data = Array();
        
        $responseSuccess = null;
        $proxySuccess = null;
        $message = "";
        $header = null;
        $header_array = null;
        $body = null;
        
        if($text == "") {
            $message = "Blank Response\n";
            $responseSuccess = false;
            $proxySuccess = false;
        } else {
            while(1) {
                if(stripos($text, "http") !== 0) {
                    break;
                }
                
                //Separate Header and Body
                $separator = "\r\n\r\n";
                $header = substr( $text, 0, strpos( $text, $separator ) );
                //echo "Header: $header [END]\n";
                $header = trim($header);
                $text_start = strlen( $header ) + strlen( $separator );
                $text = substr( $text, $text_start, strlen( $text ) - $text_start );
                //echo substr($text, 0, 1000). "\n";
                $body = $text;
                
                //Parse Headers
                $header_array = Array();
                foreach ( explode ( "\r\n", $header ) as $index => $line ) {
                    if($index === 0) {
                        $header_array['http_code'] = $line;
                        $status_info = explode( " ", $line );
                        $header_array['status_info'] = $status_info;
                    } else {
                    list ( $key, $value ) = explode ( ': ', $line );
                        $header_array[$key] = $value;
                    }
                }
                
                //echo "Header: $status_info[1]\n";
                if($status_info[1] >= 300 && $status_info[1] <= 399 || $status_info[2] != "OK") {
                    //Redirect, get next header
                } else {
                    break;
                }
            }
            
            if(is_array($header_array) && count($header_array['status_info']) < 2) {
                //echo "PROXY BAD: $proxy_ipAddress:$proxy_port\n";
                $message = "Bad Proxy";
                $responseSuccess = false;
                $proxySuccess = false;
            } elseif( is_array($header_array) && array_key_exists('status_info', $header_array) && is_array($header_array['status_info']) && array_key_exists(1, $header_array['status_info']) && ($header_array['status_info'][1] < 300 | $header_array['status_info'][1] >= 400)) {
                //echo "Successfully Downloaded\n";
                if(strlen($body) > 0) {
                    $message = "Success";
                    $responseSuccess = true;
                    $proxySuccess = true;
                } else {
                    $message = "Blank Response";
                    $responseSuccess = true;
                    $proxySuccess = true;
                }
            } else {
                //echo "STATUS BAD: ". $header_array['status_info'][1]. "\n";
                $message = "Bad Status";
                $responseSuccess = false;
                $proxySuccess = true;
            }
        }
        
        $data['responseSuccess'] = $responseSuccess;
        $data['proxySuccess'] = $proxySuccess;
        $data['message'] = $message;
        $data['header'] = $header;
        $data['header_array'] = $header_array;
        $data['body'] = $body;
        
        return $data;
    }
    
    public function run() {
        
        //Execute the Handles
        $running = null;
        do {
            curl_multi_exec($this->multiHandle, $running);
            curl_multi_select($this->multiHandle);
        } while ($running > 0);
        
        //Process Requests Data
        $data = Array();
        foreach($this->requests as $index => $request) {
            $handle = $request['handle'];
            
            //Close file, if defined
            if($request['out'] != null) {
                fclose($request['out']);
            }
            
            $output = curl_multi_getcontent( $handle );
            
            $parsed = null;
            if($request['fileOut'] != null) {
                $filesize = filesize(getcwd(). '/'. $request['fileOut']);
                //echo $request['fileOut']. ' '. $filesize. "\n";
                if($filesize > 0) {
                    $data[$index]['success'] = true;
                    $data[$index]['message'] = "Saved to File";
                } else {
                    $data[$index]['success'] = false;
                    $data[$index]['message'] = "Blank File";
                }
                
                $data[$index]['file'] = getcwd(). '/'. $request['fileOut'];
                $data[$index]['header'] = null;
                $data[$index]['header_array'] = null;
                $data[$index]['body'] = null;
            } else {
                //Parse Response
                $parsed = $this->parseResponse($output);
                
                //Form Return Array
                $data[$index]['success'] = $parsed['responseSuccess'];
                $data[$index]['message'] = $parsed['message'];
                $data[$index]['file'] = null;
                
                $data[$index]['header'] = $parsed['header'];
                $data[$index]['header_array'] = $parsed['header_array'];
                $data[$index]['body'] = $parsed['body'];
            }
            
            $data[$index]['request'] = Array(
                "url" => $request['url'],
                "referer" => $request['referer'],
                "user_agent" => $request['userAgent'],
                "cookies_file" => $request['cookiesFile']
            );
            if(!$request['proxy_ipAddress'] || !$request['proxy_port']) {
                $data[$index]['request']['proxy'] = Array("success" => null, "ip" => null, "port" => null);
            } else {
                $proxySuccess = false;
                if($parsed != null && array_key_exists('proxySuccess', $parsed)) {
                    $proxySuccess = $parsed['proxySuccess'];
                } else {
                    $proxySuccess = $data[$index]['success'];
                }
                
                $data[$index]['request']['proxy'] = Array("success" => $proxySuccess, "ip" => $request['proxy_ipAddress'], "port" => $request['proxy_port']);
            }
        }
        
        $this->clear();
        
        return $data;
    }
    
    public function add($url, $referer = "", $userAgent = null, $method = "GET", $postData = null, $cookiesFile = null, $clearCookies = false, $proxy_ipAddress = null, $proxy_port = null, $timeout_connect = 3, $timeout_complete = 15, $fileOut = null, $http_headers = null) {
        
        //Save Data
        $index = count($this->requests);
        $dataArray = Array();
        $dataArray['url'] = $url;
        $dataArray['referer'] = $referer;
        $dataArray['userAgent'] = $userAgent;
        $dataArray['method'] = $method;
        $dataArray['postData'] = $postData;
        $dataArray['cookiesFile'] = $cookiesFile;
        $dataArray['clearCookies'] = $clearCookies;
        $dataArray['proxy_ipAddress'] = $proxy_ipAddress;
        $dataArray['proxy_port'] = $proxy_port;
        $dataArray['timeout_connect'] = $timeout_connect;
        $dataArray['timeout_complete'] = $timeout_complete;
        $dataArray['fileOut'] = $fileOut;
        $dataArray['http_headers'] = $http_headers;
        
        //Intialize Handle
        $handle = curl_init();
        if($userAgent == null) {
            $userAgent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:60.0) Gecko/20100101 Firefox/60.0";
        }
        
        //If Cookies file and need to clear cookies, clear them
        if($cookiesFile != null && $clearCookies == true && file_exists(getcwd(). '/'. $cookiesFile)) {
            unlink(getcwd(). '/'. $cookiesFile);
        }
        
        //Primary settings
        curl_setopt ( $handle, CURLOPT_URL, $url );
        curl_setopt ( $handle, CURLOPT_REFERER, $referer );
        curl_setopt ( $handle, CURLOPT_USERAGENT, $userAgent);
        curl_setopt ( $handle, CURLOPT_FOLLOWLOCATION, 1 );
        curl_setopt ( $handle, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $handle, CURLOPT_HEADER, 1 );
        curl_setopt ( $handle, CURLOPT_CONNECTTIMEOUT, $timeout_connect );
        curl_setopt ( $handle, CURLOPT_TIMEOUT, $timeout_complete );
        
        //If save to file
        $dataArray['out'] = null;
        if($fileOut != null) {
            $dataArray['out'] = fopen(getcwd(). '/'. $fileOut, "w+");
            if ($dataArray['out'] == false){ 
                return null;
            }
            curl_setopt ( $handle, CURLOPT_FILE, $dataArray['out']);
            
            //Additional Settings
            curl_setopt ( $handle, CURLOPT_HEADER, 0 );
            curl_setopt ( $handle, CURLOPT_BINARYTRANSFER, true); // Allow for binary data
        }
        
        //Method & POST Data
        curl_setopt ( $handle, CURLOPT_CUSTOMREQUEST, $method );
        if($method == "GET") {
            curl_setopt($handle, CURLOPT_POST, 0);
        } else {
            if($method == "POST" || $postData) {
                curl_setopt($handle, CURLOPT_POST, 1);
            }
            if($postData) {
                curl_setopt($handle, CURLOPT_POSTFIELDS, $postData);
            }
        }
        
        //Proxy
        if($proxy_ipAddress && $proxy_port) {
            curl_setopt ( $handle, CURLOPT_PROXY, $proxy_ipAddress );
            curl_setopt ( $handle, CURLOPT_PROXYPORT, $proxy_port );
        }
        
        //Cookies
        if($cookiesFile != null) {
            curl_setopt ( $handle, CURLOPT_COOKIEFILE, $cookiesFile );
            curl_setopt ( $handle, CURLOPT_COOKIEJAR, $cookiesFile );
        }
        
        if(defined(CURL_HTTP_VERSION_2_0)) {
            curl_setopt ( $handle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0 );
        } else {
            curl_setopt ( $handle, CURLOPT_HTTP_VERSION, 3 );
        }
        
        //If extra headers
        if($http_headers && is_array($http_headers)) {
            curl_setopt ( $handle, CURLOPT_HTTPHEADER, $http_headers );
        }
        
        //Add to multi handle
        curl_multi_add_handle( $this->multiHandle, $handle );
        
        $dataArray['handle'] = $handle;
        
        $this->requests[$index] = $dataArray;
    }
    
    public function add_get($url, $referer = "", $userAgent = null, $cookiesFile = null, $clearCookies = false, $proxy_ipAddress = null, $proxy_port = null, $timeout_connect = 3, $timeout_complete = 15, $fileOut = null, $http_headers = null) {
        return $this->add($url, $referer, $userAgent, "GET", null, $cookiesFile, $clearCookies, $proxy_ipAddress, $proxy_port, $timeout_connect, $timeout_complete, $fileOut, $http_headers);
    }
}


