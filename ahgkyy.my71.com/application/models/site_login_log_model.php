<?php

class site_login_log_model extends MY_Model {

    function __construct() {
        parent::__construct();
        $this->table_name = 'site_login_log';
        $this->class_name = 'site_login_log_model';
        $this->table_field = array(
            'client_ip' => '',
            'create_date' => 0,
            'status' => 1,
            'email' => '',
            'referer'=>'http://www.luan.gov.cn/myweb/',
            'account_id' => ''
        );
    }

    
    
}    