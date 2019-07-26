<?php

class site_leader_type_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'site_leader_type';
        $this->class_name = 'site_leader_type_model';
        $this->table_field = array(
		  
		  "name" => "",
		  "site_id" => "",
		  "status" => 1
        );
    }

  

}

?>
