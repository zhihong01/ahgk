<?php

class forum_counter_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'forum_counter';
        $this->class_name = 'forum_counter_model';
        $this->table_field = array(
            'create_date' => 0,
            'group_id' => '',
            'member_count' => 0,
            'site_id' => '',
            'today_post' => 0,
            'today_thread' => 0,
            'total_online' => 0,
            'total_online_logged' => 0,
            'total_online_visitor' => 0,
            'total_post' => 0,
            'total_thread' => 0,
            'total_visit' => 0,
            'yesterday_post' => 0,
            'yesterday_thread' => 0
        );
    }

}

?>
