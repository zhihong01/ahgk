<?php

class sms_setting_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'sms_setting';
        $this->class_name = 'sms_setting_model';

        $this->table_field = array(
            'sms_password' => '',
            'sms_request_method' => 'GET', //get or post
            'sms_request_url' => '',
            'sms_response_fail' => '',
            'sms_response_format' => 'xml',
            'sms_response_success' => '',
            'sms_shoten_api_url' => '',
            'sms_shoten_url' => false,
            'sms_username' => '',
            'site_id' => '',
            'status' => true,
        );
    }

}

?>