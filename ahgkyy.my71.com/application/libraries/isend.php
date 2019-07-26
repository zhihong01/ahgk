<?php

class isend {

    public $_moblie = '';
    public $_content = '';
    public $_send_date;
    public $_error = '';
    public $_access_key = '';
    public $_request_url = '';
    public $_request_method = 'GET';
    public $_response = '';

    public function send() {

        $now = time();
        $data = array(
            'phone' => $this->_moblie,
            'content' => urlencode($this->_content),
            'time' => $now,
            //'send_date' => $this->_send_date,
            'key' => md5($this->_moblie . $this->_access_key . $now),
        );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        if (strcasecmp($this->_request_method, 'post') == 0) {
            curl_setopt($ch, CURLOPT_URL, $this->_request_url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('application/x-www-form-urlencoded'));
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        } else {
            $url = str_replace(array('[PHONE]', '[CONTENT]', '[TIME]', '[KEY]'), $data, $this->_request_url);
            curl_setopt($ch, CURLOPT_URL, $url);
        }

        $this->_response = curl_exec($ch);
        curl_close($ch);
    }

}

?>
