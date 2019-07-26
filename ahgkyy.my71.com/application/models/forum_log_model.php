<?php

class forum_log_model extends MY_Model {

    function __construct() {
        parent::__construct();        

        $this->table_name = 'forum_log';
        $this->class_name = 'forum_log_model';
        $this->table_field = array(
            'client_ip' => '',
            'create_date' => 0,
            'creator_id'=>'',
            'thread_id'=>'',
            'post_id'=>'',
            'style'=>'',
        );
    }

    
    public function listCount($keyword, $filter_list = array(), $from_date = null, $to_date = null) {
        if (strlen($keyword) > 1) {
            $this->mongo_db->like('title', $keyword);
        }

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
    
    public function getLogInfoByPosts($thread_id, $_id_list = array(), $select = array()) {

        $this->mongo_db->select($select);
        $this->mongo_db->where('thread_id', $thread_id);
        $this->mongo_db->where_in('post_id', $_id_list);

        $result = $this->mongo_db->get($this->table_name);
        $nickname_list = array();
        $count = count($result);

        for ($i = 0; $i < $count; $i++) {
            $nickname_list[(string) $result[$i]['post_id']] = $result[$i];
        }
        return $nickname_list;
    }
}

?>