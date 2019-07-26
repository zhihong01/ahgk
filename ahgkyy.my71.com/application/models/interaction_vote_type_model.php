<?php

class interaction_vote_type_model extends MY_Model {


    function __construct() {
        parent::__construct();

        $this->table_name = 'interaction_vote_type';
        $this->class_name = 'interaction_vote_type_model';
    }

}

?>
