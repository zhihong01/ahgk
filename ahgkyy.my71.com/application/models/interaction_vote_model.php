<?php

class interaction_vote_model extends MY_Model {


    function __construct() {
        parent::__construct();

        $this->table_name = 'interaction_vote';
        $this->class_name = 'interaction_vote_model';
        $this->table_field = array(
            "type_id" => '',
            "channel_id" => '',
            "name" => '',
            "description" => '',
            "content" => '',
            "site_id" => '',
            "status" => false,
            'create_date' => 0,
            "startdate" => 0,
            "overdate" => 0,
            'confirm_date' => 0,
            'confirmer' => array('id' => '', 'name' => ''),
            'creator' => array('id' => '', 'name' => ''),
            'release_date' => 0,

            "is_realname" => false,
            "is_syncshow" => true,
            "is_recordvoter" => false,

            "is_closed" => false,
            "close_date" => 0,
            "close_remark" => "",
            "voter_count" => 0,
            'removed' => false,
            "sort" => 0,

            "voter_count" => 0,		// 投票次数
            
            "link_url" => "",
            "is_pic" => false,
            "pic_item_list" => array(),
            
            
            'vote_per_ip' => 1,
            'vote_restrict_type' => 'vote' ,  //'day', 'none'                    
            
            'is_changed' => false,
            "interpolator"  => array('id' => '', 'name' => '', 'update_date'=>0), 
            
            'template'=>"",
            
            );
    }

    public function findList($keyword, $filter_list = array(), $from_date = null, $to_date = null, $limit = 20, $offset = 0, $select =array(), $arr_sort = null) {


        if (strlen($keyword) > 1) {
            $this->mongo_db->like('name', $keyword);
        }

        foreach ($filter_list as $key => $filter) {
            if ($filter !== "" && $filter !== NULL) {
                $this->mongo_db->where($key, $filter);
            }
        }

        if ($from_date) {
            $this->mongo_db->where_gt('startdate', strtotime($from_date . " 00:00:00"));
        }

        if ($to_date) {
            $this->mongo_db->where_lt('startdate', strtotime("+1 day", strtotime($to_date)));
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
//echo $this->mongo_db->last_query();
        return $query;
    }

    public function listCount($keyword, $filter_list = array(), $from_date = null, $to_date = null) {

        if (strlen($keyword) > 1) {
            $this->mongo_db->like('name', $keyword);
        }

        foreach ($filter_list as $key => $filter) {
            if ($filter !== "" && $filter !== NULL) {
                $this->mongo_db->where($key, $filter);
            }
        }

        if ($from_date) {
            $this->mongo_db->where_gt('startdate', strtotime($from_date . " 00:00:00"));
        }

        if ($to_date) {
            $this->mongo_db->where_lt('startdate', strtotime("+1 day", strtotime($to_date)));
        }

        $query = $this->mongo_db->count($this->table_name);
        return (int) $query;
    }

    public function findExists($oldId=null, $filter_list = array()) {

        foreach ($filter_list as $key => $filter) {
            if ($filter !== "" && $filter !== NULL) {
                $this->mongo_db->where($key, $filter);
            }
        }

        if (!empty($oldId) && !($oldId instanceof MongoId)) {
            $oldId = $this->SafeMongoId($oldId);
        }

        $this->mongo_db->where_ne('_id', $oldId);

        $this->mongo_db->limit(1);
        $query = $this->mongo_db->get($this->table_name);

        return $query;
    }

    public function getFormNames($idList){
        foreach($idList as $k=>$v){
            $idList[$k] = $this->SafeMongoId($v);
        }
        $this->mongo_db->where_in('_id', $idList);
        $query = $this->mongo_db->get($this->table_name);
        $name_list = array();

        for ($i = 0; $i < count($query); $i++) {
            $name_list[(string) $query[$i]['_id']] = $query[$i]['name'];
        }
        return $name_list;
    }
}

?>
