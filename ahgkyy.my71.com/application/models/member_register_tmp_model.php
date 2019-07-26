<?php

class member_register_tmp_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'member_register_tmp';
        $this->class_name = 'member_register_tmp_model';
        $this->table_field = array(
            'password' => '',
            'email' => '',
            'site_id' => '',
            'client_ip' => '',
            'create_date' => 0,
            'nickname' => '',
            'rand_key' => '',
            'status' => false,
        );
    }

}

?>
