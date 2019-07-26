<?php

class interaction_vote_result_model extends MY_Model {


    function __construct() {
        parent::__construct();

        $this->table_name = 'interaction_vote_result';
        $this->class_name = 'interaction_vote_result_model';
    }
}

?>
