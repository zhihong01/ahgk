<?php

class interaction_vote_log_model extends MY_Model {

    function __construct() {
        parent::__construct();        

        $this->table_name = 'interaction_vote_log';
        $this->class_name = 'interaction_vote_log_model';

        $this->table_field = array(
            'create_date' => 0,
            'status' => false, 
            'creator' => array('id' => '', 'name' => ''),
            "site_id" => '',
            'removed' => false,

            "form_id" => '',		// 图片投票_id
            "vote_id" => '',		// 投票_id            
            "account_id" => '',		// 会员_id（开启会员投票）,
            "name" => '',
            "client_ip" => '',
            "vote_source" => 'web',	// 投票来源 web,user

            // 开启记录投票人信息
            "voter_paper_id" => "",
            "voter_tel" => "",
            "voter_addr" => "",

			//"hit_rate" => 0,		// 命中率
        );
    }

    public function findList($filter_list = array(), $from_date = null, $to_date = null, $limit = 20, $offset = 0, $select = array(), $arr_sort = null) {

        foreach ($filter_list as $key => $filter) {
            if ($filter !== '' && $filter !== NULL) {
                $this->mongo_db->where($key, $filter);
            }
        }

        if ($from_date) {
            $this->mongo_db->where_gt('create_date', strtotime($from_date . ' 00:00:00'));
        }

        if ($to_date) {
            $this->mongo_db->where_lt('create_date', strtotime('+1 day', strtotime($to_date)));
        }

        if ($arr_sort != null && is_array($arr_sort)) {
            $this->mongo_db->order_by($arr_sort);
        } else {
            $this->mongo_db->order_by(array('_id' => 'DESC'));
        }

        $this->mongo_db->select($select);
        $this->mongo_db->limit($limit);
        $this->mongo_db->offset($offset);

        $query = $this->mongo_db->get($this->table_name);

        return $query;
    }

    public function listCount($filter_list = array(), $from_date = null, $to_date = null) {

        foreach ($filter_list as $key => $filter) {
            if ($filter !== '' && $filter !== NULL) {
                $this->mongo_db->where($key, $filter);
            }
        }

        if ($from_date) {
            $this->mongo_db->where_gt('create_date', strtotime($from_date . ' 00:00:00'));
        }

        if ($to_date) {
            $this->mongo_db->where_lt('create_date', strtotime('+1 day', strtotime($to_date)));
        }

        $query = $this->mongo_db->count($this->table_name);
        return (int) $query;
    }


}

?>
