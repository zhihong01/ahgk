<?php

/**
 * Description of supervision_rating_mean_model
 *
 * @author Administrator
 */
class supervision_rating_mean_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'supervision_rating_mean';
        $this->class_name = 'supervision_rating_mean_model';

        $this->table_field = array(
            "supervision_id" => "",
            "member_id" => "",
            "rating" => array(
                0 => 0,
                1 => 0,
                2 => 0,
                3 => 0,
                4 => 0,
                5 => 0,
            ),
            "mean" => array(
                "stat_0" => "",
                "stat_1" => "",
                "stat_2" => "",
                "stat_3" => "",
                "stat_4" => "",
                "stat_5" => "",
            ),
        );
    }

}

?>