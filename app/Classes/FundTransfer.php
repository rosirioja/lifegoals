<?php

namespace App\Classes;

use Log;

class FundTransfer {

    private $url = 'http://lifegoals.cloudapp.net:8000/';
    private $coins_accesstoken = '1O0K7zIRaAp1mvV6nk9nUVHayVre53';
    private $coins_targetaddress = 'd4e07e1d7a88470eba5dbfb326ca5228';
    private $ubank_accountno = '000000014148';

    public $account_type = '';
    public $access_token = '';
    public $account_no = '';
    public $target_address = '';
    public $transaction_id = '';

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
     * Fund Transfer
     *
     * @param string action type (CASHIN CASHOUT)
     * @param float $amount
     * @return boolean
     */
    public function fundTransfer($action_type = '', $amount = '')
    {
        $url = $this->url. 'api/v1/transfers/';

        /* if account type = CASHIN
         * COINS - access_token
         * UBANK = account_no
         *
         * CASHOUT
         * COINS - target_address
         * UBANK - account_no
         */

        $fields = [
             'amount' => $amount,
             'type' => $this->account_type
        ];

        if ($this->account_type == 'COINS') {
            if ($action_type == 'CASHIN') {
                $fields['access_token'] = $this->access_token;
                $fields['target_address'] = $this->coins_targetaddress;
            }

            if ($action_type == 'CASHOUT') {
                $fields['access_token'] = $this->coins_accesstoken;
                $fields['target_address'] = $this->target_address;
            }
        }

        if ($this->account_type == 'UBANK') {
            if ($action_type == 'CASHIN') {
                $fields['source_address'] = $this->account_no;
                $fields['target_address'] = $this->ubank_accountno;
                $fields['transaction_id'] = $this->transaction_id;
            }

            if ($action_type == 'CASHOUT') {
                $fields['source_address'] = $this->ubank_accountno;
                $fields['target_address'] = $this->account_no;
                $fields['transaction_id'] = $this->transaction_id;
            }
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
