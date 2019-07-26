<?php

class respond_template_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'respond_template';
        $this->class_name = 'respond_template_model';

    }

}
?>