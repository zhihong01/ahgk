<?php

class interaction_vote_data_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'interaction_vote_data';
        $this->class_name = 'interaction_vote_data_model';
        $this->table_field = array(
        	"vote_id" => '',		// 投票记录_id
            "form_id" => '',		// 表单_id
            "log_id" => '',			// 投票记录_id
            "field_value" => '',	// 所选或填写具体数据
            "field_type" => '',		// 表单类型
            "field_id" => '',                   //图片 投票的 序列号
            "site_id" => '',
            "status" => false,
            'create_date' => 0,
            'creator' => array('id' => '', 'name' => '')
        );
    }


}

?>
