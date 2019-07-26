<?php

class interaction_comment_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'interaction_comment';
        $this->class_name = 'interaction_comment_model';

        $this->table_field = array(
            "title" => "",
            "description" => "",
            "branch" => array(),
            "vote_count" => array(),
            "body" => "",
            "ismember" => 0,
            "is_syncshow" => 0,
            "startdate" => 0,
            "overdate" => 0,
            "status" => false,
            "site_id" => "",
            "create_date" => 0,
            "creator" => array(
                "id" => "",
                "name" => ""
            ),
            "confirm_date" => 0,
            "confirmer" => array(
                "id" => "",
                "name" => ""
            ),
            "link_url" => "",
            "removed" => false,
            "sort" => 0,
            
            "member_list" => array(),
            "score_name_map" => array(),
            
           'views' => 0,  //点击次数
            
        );
    }

}

?>
