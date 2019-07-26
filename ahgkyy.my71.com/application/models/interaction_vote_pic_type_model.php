<?php

class interaction_vote_pic_type_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'interaction_vote_pic_type';
        $this->class_name = 'interaction_vote_pic_type_model';
        $this->table_field = array(
            'name' => '',
            'site_id' => '',
        );
    }

}

?>
