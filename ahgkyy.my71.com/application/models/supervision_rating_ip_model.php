<?php

/**
 * Description of supervision_rating_ip_model
 *
 * @author Administrator
 */
class supervision_rating_ip_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'supervision_rating_ip';
        $this->class_name = 'supervision_rating_ip_model';

        $this->table_field = array(
            "supervision_id" => "",
            "ip" => "",
            "create_date" => 0,
        );
    }

}

?>