<?php

class forum_member_group_model extends MY_Model {

    function __construct() {
        parent::__construct();        

        $this->table_name = 'forum_member_group';
        $this->class_name = 'forum_member_group_model';

        $this->table_field = array(

            "can_confirm" => true,
            "can_download" => true,
            "can_post" => true,
            "can_remove" => false,
            "can_set_good" => true,
            "can_set_top" => true,
            "can_thread" => true,
            "can_upload" => true,

            "confirmer" => array(
                "id" => "",
                "name" => ""
            ),
            "create_date" => 0,
            "creator" => array(
                "id" => "",
                "name" => ""
            ),

            "id" => 0,
            "name" => "",
            "level" => 0,
            "boards" => null,
            "file_number" => 5,
            "file_size" => 2048,
            "need_confirm" => 1,
            "riches" => 0,
            "site_id" => "",
            "star_number" => 1,
            "type" => ""
        );
    }    
 
}

?>
