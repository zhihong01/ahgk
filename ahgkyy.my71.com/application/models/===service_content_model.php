<?php

class service_content_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'service_content';
        $this->class_name = 'service_content_model';
      
    }
    public function findList($keyword = '', $filter_list = array(), $limit = 20, $offset = 0, $select = array(), $arr_sort = null) {
		
        if (strlen($keyword) > 1) {
            $this->mongo_db->like('title', $keyword);
        }

        foreach ($filter_list as $key => $filter) {
            if ($filter !== '' && $filter !== NULL) {
                $this->mongo_db->where($key, $filter);
            }
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

	public function listCount($keyword = '', $filter_list = array()) {
		
        if (strlen($keyword) > 1) {
            $this->mongo_db->like('title', $keyword);
        }

        foreach ($filter_list as $key => $filter) {
            if ($filter !== '' && $filter !== NULL) {
                $this->mongo_db->where($key, $filter);
            }
        }
		
        $query = $this->mongo_db->count($this->table_name);

        return (int) $query;
    }
    
public function searchList($channel_id = array(), $keyword = '', $filter_list = array(), $from_date = null, $to_date = null, $limit = 20, $offset = 0, $select = array(), $arr_sort = null, $field =null) {

        if (count($channel_id) > 0) {
            $this->mongo_db->where_in('type', $channel_id);
        }
        if (strlen(trim($keyword)) > 1) {
			if(!empty($field)){
				$this->mongo_db->like("$field", trim($keyword));
			}else{
				$this->mongo_db->like('title', trim($keyword));
			}
            
        }

        foreach ($filter_list as $key => $filter) {
            if ($filter !== '' && $filter !== NULL) {
                $this->mongo_db->where($key, $filter);
            }
        }

        if ($from_date) {
            $this->mongo_db->where_gt('release_date', strtotime($from_date . ' 00:00:00'));
        }

        if ($to_date) {
            $this->mongo_db->where_lt('release_date', strtotime('+1 day', strtotime($to_date)));
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

    public function searchCount($channel_id = array(), $keyword = '', $filter_list = array(), $from_date = null, $to_date = null,$field=null) {
        if (count($channel_id) > 0) {
            $this->mongo_db->where_in('type', $channel_id);
        }
        if (strlen(trim($keyword)) > 1) {
			if(!empty($field)){
				$this->mongo_db->like("$field", trim($keyword));
			}else{
				$this->mongo_db->like('title', trim($keyword));
			}
            
        }

        foreach ($filter_list as $key => $filter) {
            if ($filter !== '' && $filter !== NULL) {
                $this->mongo_db->where($key, $filter);
            }
        }

        if ($from_date) {
            $this->mongo_db->where_gt('release_date', strtotime($from_date . ' 00:00:00'));
        }

        if ($to_date) {
            $this->mongo_db->where_lt('release_date', strtotime('+1 day', strtotime($to_date)));
        }

        $query = $this->mongo_db->count($this->table_name);
        return (int) $query;
    }

}

?>
