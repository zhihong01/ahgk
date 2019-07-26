<?php

class site_member_model extends MY_Model {

    function __construct() {
        parent::__construct();
        $this->table_name = 'site_member';
        $this->class_name = 'site_member_model';
		$this->table_field = array(
            'account_id' => '',
            'company_address' => '',
            'company_name' => '',
            'forum_blocked' => 0,
            'forum_group_id' => "",
            'forum_riches' => 0,
            'forum_total_post' => 0,
            'forum_total_thread' => 0,
            'job_title' => '',
            'row_per_page' => 20,
            'site_id' => '',
            'time_zone' => '+0800',
            'update_date' => 0,

            'forum_old_group_id' => 5,
        );
    }
    
    public function getAccountInfo($_id_list = array(), $select = array()) {
        $_id_list = array_unique($_id_list);
        foreach ($_id_list as $key => $value) {
            $_id_list[$key] = new MongoId($value);
        }

        $this->mongo_db->select($select);
        $this->mongo_db->where_in('_id', $_id_list);

        $result = $this->mongo_db->get($this->table_name);
        $nickname_list = array();
        $count = count($result);

        for ($i = 0; $i < $count; $i++) {
            $nickname_list[(string) $result[$i]['_id']] = $result[$i];
        }
        return $nickname_list;
    }
    
    public function getInfo($_id_list = array()) { //, $select = '*'
        $_id_list = array_filter($_id_list);
        
//        $this->mongo_db->select($select);
        $this->mongo_db->where_in('account_id', $_id_list);

        $result = $this->mongo_db->get($this->table_name);
        $nickname_list = array();
        $count = count($result);

        for ($i = 0; $i < $count; $i++) {
            $nickname_list[(string) $result[$i]['account_id']] = $result[$i];
        }
        return $nickname_list;
    }
}    
