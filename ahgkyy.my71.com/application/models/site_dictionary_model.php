<?php

class site_dictionary_model extends MY_Model {

    function __construct() {
        parent::__construct();
        $this->table_name = 'site_dictionary';
        $this->class_name = 'site_dictionary_model';
    }

}

/* End of file site_dictionary_model.php */
/* Location: ./application/models/site_dictionary_model.php */