<?php

class interaction_comment_result_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'interaction_comment_result';
        $this->class_name = 'interaction_comment_result_model';

        $this->table_field = array(
              "comments_id" => "",
              "site_id" => "",
              "branch_id" => "",
              "filed_id" => "",
              "total" => 0,
              "removed" => true,
        );
    }

}

?>