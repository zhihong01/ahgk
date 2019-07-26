<?php

class site_login_session_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'site_login_session';
        $this->class_name = 'site_login_session_model';

        $this->table_field = array(
            'account_id' => '',
            'session_key' => '',
            'branch_id' => '',
            'expiration_date' => 0,
            'update_date' => 0,
            'client_ip' => '',
            'name' => '',
            'site_id' => '',
        );
    }
}

?>
