<?php
class content_dept_counter_model extends MY_Model {
	
    function __construct() {
        parent::__construct();

        $this->table_name = 'content_dept_counter';
        $this->class_name = 'content_dept_counter_model';
    }
}

?>