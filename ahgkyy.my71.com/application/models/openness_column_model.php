<?php

class openness_column_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'openness_column';
        $this->class_name = 'openness_column_model';
        
    }

    public function findList($parent_id = array(), $keyword = '', $filter_list = array(), $from_date = null, $to_date = null, $limit = 20, $offset = 0, $select = array(), $arr_sort = null,$way=null) {

        if (count($parent_id) > 0) {
            $this->mongo_db->where_in('parent_id', $parent_id);
        }
		if($way){
			if (strlen($keyword) > 1) {
            $this->mongo_db->like($way, $keyword);
			}
		}else{
			if (strlen($keyword) > 1) {
            $this->mongo_db->like('name', $keyword);
			}
		}
        

        if (count($filter_list) > 0) {
            foreach ($filter_list as $key => $filter) {
                if ($filter !== '' && $filter !== NULL) {
                    $this->mongo_db->where($key, $filter);
                }
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

        public function findFloder($filter_list = array(),$code = 100, $limit = 20, $offset = 0, $select = array(), $arr_sort = null) {

        if (count($filter_list) > 0) {
            foreach ($filter_list as $key => $filter) {
                if ($filter !== '' && $filter !== NULL) {
                    $this->mongo_db->where($key, $filter);
                }
            }
        }

        if ($arr_sort != null && is_array($arr_sort)) {
            $this->mongo_db->order_by($arr_sort);
        } else {
            $this->mongo_db->order_by(array('_id' => 'DESC'));
        }
        
        if ($code > 0) {//最新代码修正
            if (($code % 10000) === 0) {
                $this->mongo_db->where_gt('code', $code);
                $this->mongo_db->where_lt('code', $code + 10000);
            } elseif (($code % 100) === 0) {
                $this->mongo_db->where_gt('code', $code);
                $this->mongo_db->where_lt('code', $code + 100);
            } else {
                $this->mongo_db->where('code', $code);
            }
        }

        $this->mongo_db->select($select);
        $this->mongo_db->limit($limit);
        $this->mongo_db->offset($offset);

        $query = $this->mongo_db->get($this->table_name);

        return $query;
    }


}
?>