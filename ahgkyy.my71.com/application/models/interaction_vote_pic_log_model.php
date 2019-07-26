<?php
  class interaction_vote_pic_log_model extends MY_Model {

      function __construct() {
        parent::__construct();
        $this->table_name = 'interaction_vote_pic_log';
        $this->class_name = 'interaction_vote_pic_log_model';
        $this->table_field = array(
            "account_id" => '',
            "name" => '',
            'client_ip' => '',
            'creator' => array('id' => '', 'name' => ''),
            "site_id" => '',
            "type_id" => '',
            'create_date' => 0,
			'create_time' => 0,
            'status' => false,
            'item_id' => '',   // 当前图片项的id
            "removed" => false,
        );  
      }
  }

?>