<?php

class service_download_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'service_download';
        $this->class_name = 'service_download_model';
      
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
   

}

?>
