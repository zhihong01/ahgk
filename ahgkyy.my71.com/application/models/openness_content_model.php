<?php

class openness_content_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'openness_content';
        $this->class_name = 'openness_content_model';
    }

    public function findList($branch_id = null, $filter_list = array(), $limit = 20, $offset = 0, $select = array(), $arr_sort = null, $code = 100,$keyword = '') {
        if ($branch_id !== null) {
            if (is_array($branch_id)) {
                $this->mongo_db->where_in('branch_id', $branch_id);
            } else {
                $this->mongo_db->where('branch_id', $branch_id);
            }
        }

        if (count($filter_list) > 0) {
            foreach ($filter_list as $key => $filter) {
                if (is_array($filter)) {
                    $this->mongo_db->where_in('key', $filter);
                } elseif ($filter !== '' && $filter !== NULL) {
                    $this->mongo_db->where($key, $filter);
                }
            }
        }


        if ($code > 0) {
            if (($code % 10000) === 0) {
                $this->mongo_db->where_gte('column_code', $code);
                $this->mongo_db->where_lt('column_code', $code + 10000);
            }else if (($code % 100) === 0) {
                $this->mongo_db->where_gte('column_code', $code);
                $this->mongo_db->where_lt('column_code', $code + 100);
            } else {
                $this->mongo_db->where('column_code', $code);
            }
        }
		
		if (strlen($keyword) > 1) {
            $this->mongo_db->like('title', $keyword);
        }

        if ($arr_sort != null && is_array($arr_sort)) {
            $this->mongo_db->order_by($arr_sort);
        } else {
            $this->mongo_db->order_by(array('sort' => 'DESC'));
        }

        $this->mongo_db->select($select);
        $this->mongo_db->limit($limit);
        $this->mongo_db->offset($offset);

        $query = $this->mongo_db->get($this->table_name);
//echo($this->mongo_db->last_query() );
        return $query;
    }

    public function listCount($branch_id = null, $filter_list = array(), $code = 100,$keyword = '') {

        if ($branch_id !== null) {
            if (is_array($branch_id)) {
                $this->mongo_db->where_in('branch_id', $branch_id);
            } else {
                $this->mongo_db->where('branch_id', $branch_id);
            }
        }

        if (count($filter_list) > 0) {
            foreach ($filter_list as $key => $filter) {
                if (is_array($filter)) {
                    $this->mongo_db->where_in('key', $filter);
                } elseif ($filter !== '' && $filter !== NULL) {
                    $this->mongo_db->where($key, $filter);
                }
            }
        }

        if ($code > 0) {
            if (($code % 10000) === 0) {
                $this->mongo_db->where_gte('column_code', $code);
                $this->mongo_db->where_lt('column_code', $code + 10000);
            }else if (($code % 100) === 0) {
                $this->mongo_db->where_gte('column_code', $code);
                $this->mongo_db->where_lt('column_code', $code + 100);
            } else {
                $this->mongo_db->where('column_code', $code);
            }
        }

		if (strlen($keyword) > 1) {
            $this->mongo_db->like('title', $keyword);
        }

        $query = $this->mongo_db->count($this->table_name);

        return $query;
    }
	
	
	public function searchList($branch_id = null, $filter_list = array(), $limit = 20, $offset = 0, $select = array(), $arr_sort = null, $like = array(),$from_date = null, $to_date = null) {
        if ($branch_id !== null) {
            if (is_array($branch_id)) {
                $this->mongo_db->where_in('branch_id', $branch_id);
            } else {
                $this->mongo_db->where('branch_id', $branch_id);
            }
        }

        if (count($filter_list) > 0) {
            foreach ($filter_list as $key => $filter) {
                if (is_array($filter)) {
                    $this->mongo_db->where_in('key', $filter);
                } elseif ($filter !== '' && $filter !== NULL) {
                    $this->mongo_db->where($key, $filter);
                }
            }
        }


        if (count($like) > 0) {
			foreach($like as $key=>$val){
				$this->mongo_db->like($key, $val);
			}
            
        }
		
		if ($from_date) {
            $this->mongo_db->where_gt('openness_date', $from_date);
        }

        if ($to_date) {
            $this->mongo_db->where_lt('openness_date', $to_date);
        }
		

        if ($arr_sort != null && is_array($arr_sort)) {
            $this->mongo_db->order_by($arr_sort);
        } else {
            $this->mongo_db->order_by(array('sort' => 'DESC'));
        }

        $this->mongo_db->select($select);
        $this->mongo_db->limit($limit);
        $this->mongo_db->offset($offset);

        $query = $this->mongo_db->get($this->table_name);

        return $query;
    }
	
	public function searchCount($branch_id = null, $filter_list = array(), $like =array(),$from_date = null, $to_date = null) {

        if ($branch_id !== null) {
            if (is_array($branch_id)) {
                $this->mongo_db->where_in('branch_id', $branch_id);
            } else {
                $this->mongo_db->where('branch_id', $branch_id);
            }
        }

        if (count($filter_list) > 0) {
            foreach ($filter_list as $key => $filter) {
                if (is_array($filter)) {
                    $this->mongo_db->where_in('key', $filter);
                } elseif ($filter !== '' && $filter !== NULL) {
                    $this->mongo_db->where($key, $filter);
                }
            }
        }

        if (count($like) > 0) {
			foreach($like as $key=>$val){
				$this->mongo_db->like($key, $val);
			}
            
        }

		if ($from_date) {
            $this->mongo_db->where_gt('openness_date', $from_date);
        }

        if ($to_date) {
            $this->mongo_db->where_lt('openness_date', $to_date);
        }
        $query = $this->mongo_db->count($this->table_name);

        return $query;
    }

}
?>