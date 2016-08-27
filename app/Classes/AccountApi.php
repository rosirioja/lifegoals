<?php

namespace App\Classes;

use Log;

class AccountApi {

    private $url = 'http://lifegoals.cloudapp.net:8000/';

    public $type = '';
    public $access_token = '';
    public $account_no = '';

    public function __construct($params) {

        $this->initialize($params);
    }

    /**
	* Initialization
	*
	* Set global variables
	*
	* @access public
	* @param void
	* @return void
	*/
	function initialize($params = array())
	{
		foreach ($params as $key => $val)
		{
			if (isset($this->$key)) $this->$key = $val;
		}
	}

    /**
     * Get User Account Based on Type
     *
     * @param void
     * @return void
     */
    public function getAccountInfo()
    {
        $url = $this->url. 'api/v1/accounts/';

        $fields = [
            'type' => $this->type
        ];

        if ($this->type == 'COINS') {
            $fields['access_token'] = $this->access_token;
        }

        if ($this->type == 'UBANK') {
            $fields['account_no'] = $this->account_no;
        }

        $response = $this->_curlPost($url, $fields);

        if (isset($response['errors'])) {
            return false;
        }

        return $response;
    }

    /**
     * Connecting to API
     *
     * @param  string url
     * @param  array post data
     * @return [type]
     */
    protected function _curlPost($url, $fields = array()) {

        Log::info('Curl Post '. $url);
        Log::info($fields);

        $string = '';
        foreach ($fields as $key => $value) {
            $string .= "-----011000010111000001101001\r\nContent-Disposition: form-data; name=\"";
            $string .= $key;
            $string .= "\"\r\n\r\n";
            $string .= $value;
            $string .= "\r\n";
        }
        $string .= "-----011000010111000001101001--";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_PORT, '8000');
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "cache-control: no-cache",
            "content-type: multipart/form-data; boundary=---011000010111000001101001"
        ));

        $response = curl_exec($ch);

        curl_close($ch);

        $response = json_decode($response, true);
        Log::info($response);

        return $response;
    }
}
