<?php
/**
 * lib/HttpRequest.php
 */

/**
 * HttpRequest
 *
 * A simple library for doing HTTP requests with PHP's built-in cURL library.
 *
 */
class HttpRequest {
    
    private $curl;
    public $curlDefaults = array(CURLOPT_RETURNTRANSFER => true,
                                 CURLOPT_SSL_VERIFYPEER => false,
                                 CURLOPT_HEADER => true,
                                 CURLINFO_HEADER_OUT => true,
                                 CURLOPT_TIMEOUT => 120
                                );
    public $curlHttpAuthType = 0;
    public $curlHttpAuthOpts = array();

    /**
     * Initialize any dependency objects.
     */
    public function __contsruct() {
        // No dependencies
    }

    /**
     * Before executing an HTTP request, this function should be called to set
     * any cURL options that are generic to all request methods.
     *
     * @param string $webserviceUri The full URI query for the request.
     *
     * @return void
     */
    private function initializeRequest(string $webserviceUri)
    {
        $this->curl = curl_init($webserviceUri);
        foreach($this->curlDefaults as $option => $value)
        {
            curl_setopt($this->curl, $option, $value);
        }
        if(!empty($this->curlHttpAuthType))
        {
            foreach($this->curlHttpAuthOpts as $option => $value)
            {
                curl_setopt($this->curl, $option, $value);
            }
        }
    }

    /**
     * Use curl_exec to send the HTTP request.  Then parse the response into
     * an associative array.
     *
     * @param void
     *
     * @return array $responseArray An associative array containing the entire
     *      response from the service.  The structure of the response is:
     *  array(5) {
     *    ["header"]=>
     *    string(133) "HTTP/1.1 200 OK
     *  Date: Mon, 22 Jan 2018 19:10:19 GMT
     *  Server: Apache
     *  Content-Length: 390
     *  Content-Type: text/html; charset=UTF-8
     *  
     *  "
     *    ["body"]=>
     *    string(390) "{"Baan_OB_Invoice_Test_800":{"Path":"C:\/usmn01mslnbatch\/edi\/xt-edi\/appl_from\/",
                                                    "Filename":"inv001.txt"}}"
     *    ["curl_error"]=>
     *    string(0) ""
     *    ["http_code"]=>
     *    int(200)
     *    ["last_url"]=>
     *    string(60) "https://nilfisk.ediadmin.com/hwe/Lookup/FileboxDirectory/All"
     *  }
     */
    private function executeRequest()
    {
        $rawResponse      = curl_exec($this->curl);
        $error            = curl_error($this->curl);
        $headerSize       = curl_getinfo ($this->curl, CURLINFO_HEADER_SIZE);
        $httpCode         = curl_getinfo ($this->curl, CURLINFO_HTTP_CODE);
        $httpEffectiveUrl = curl_getinfo ($this->curl, CURLINFO_EFFECTIVE_URL);

        curl_close($this->curl);
        $responseArray = array('headers' => '',
                               'body' => '',
                               'curl_error' => '',
                               'http_code' => '',
                               'last_url' => '');
        
        // If we get some type of error, display the error and exit.
        if ( $error != "" )
        {
            $responseArray['curl_error'] = $error;
            return $responseArray;
        }
        $responseHeader = substr($rawResponse, 0, $headerSize);
        foreach (explode("\r\n", $responseHeader) as $i => $line)
        {
            if ($i === 0)
            {
                $responseHeaders['http_code'] = $line;
            }
            
            // int strpos ( string $haystack , mixed $needle [, int $offset = 0 ] )
            //elseif(!empty($line))
            elseif(strpos($line, ":"))
            {
                list($key, $value) = explode(': ', $line);

                if(!empty($key) and !empty($value))
                {
                    $responseHeaders[$key] = $value;
                }
            }
        }
        $responseArray['headers']    = $responseHeaders;
        $responseArray['body']      = substr( $rawResponse, $headerSize );
        $responseArray['http_code'] = $httpCode;
        $responseArray['last_url']  = $httpEffectiveUrl;

        return $responseArray;
    }

    /**
     * Set the CURLOPT_HTTPAUTH authentication type to basic username password.
     *
     * @param string $username The HTTP username.
     *
     * @param string $password The HTTP password.
     *
     * @return void
     */
    public function setHttpAuthBasic (string $username, string $password)
    {
        //$this->credentials = $username . ":" . $password;
        $this->curlHttpAuthType = CURLAUTH_BASIC;
        $this->curlHttpAuthOpts = array(CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                                        CURLOPT_USERPWD => $username . ":" . $password
                                       );

    }

    /**
     * Uses curl_exec to execute an HTTP GET request against the specified URI.
     *
     * @param string $webserviceUri The full query URI that will be requested.
     *
     * @param array $httpHeader The HTTP Header field data.
     *
     * @param string $postData Any post data that should be part of the request.
     *
     * @return $this->executeRequest()
     */
    public function httpGet (string $webserviceUri, array $httpHeader = array(), string $postData = "")
    {
        $this->initializeRequest($webserviceUri);
        if(!empty($httpHeader))
        {
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, $httpHeader);
        }
        if(!empty($postData))
        {
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postData);
        }

        return $this->executeRequest();
    }

    /**
     * Uses curl_exec to execute an HTTP POST request against the specified URI.
     *
     * @param string $webserviceUri The full query URI that will be requested.
     *
     * @param array $httpHeader The HTTP Header field data.
     *
     * @param string $postData Any post data that should be part of the request.
     *
     * @return array $this->executeRequest()
     */
    public function httpPost (string $webserviceUri, array $httpHeader = array(), string $postData = "")
    {
        $this->initializeRequest($webserviceUri);
        curl_setopt($this->curl, CURLOPT_POST, true);
        if(!empty($httpHeader))
        {
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, $httpHeader);
        }
        if(!empty($postData))
        {
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postData);
        }
        
        return $this->executeRequest();
    }

    /**
     * Uses curl_exec to execute an HTTP PUT request against the specified URI.
     *
     * @param string $webserviceUri The full query URI that will be requested.
     *
     * @param array $httpHeader The HTTP Header field data.
     *
     * @param string $postData Any post data that should be part of the request.
     *
     * @return array $this->executeRequest()
     */
    public function httpPut (string $webserviceUri, array $httpHeader = array(), string $postData = "")
    {
        $this->initializeRequest($webserviceUri);
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'PUT');
        if(!empty($httpHeader))
        {
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, $httpHeader);
        }
        if(!empty($postData))
        {
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postData);
        }
        
        return $this->executeRequest();
    }

    /**
     * Uses curl_exec to execute an HTTP DELETE request against the specified URI.
     *
     * @param string $webserviceUri The full query URI that will be requested.
     *
     * @param array $httpHeader The HTTP Header field data.
     *
     * @param string $postData Any post data that should be part of the request.
     *
     * @return array $this->executeRequest()
     */
    public function httpDelete (string $webserviceUri, array $httpHeader = array(), string $postData = "")
    {
        $this->initializeRequest($webserviceUri);
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
        if(!empty($httpHeader))
        {
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, $httpHeader);
        }
        if(!empty($postData))
        {
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postData);
        }
        
        return $this->executeRequest();
    }
    
}

?>