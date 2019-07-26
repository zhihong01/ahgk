<?php

class supervision_branch_counter_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'supervision_branch_counter';
        $this->class_name = 'supervision_branch_counter_model';
    }

	public function findList($branch_id = array(), $filter_list = array(), $limit = 20, $offset = 0, $select = array(), $arr_sort = null) {

        if (count($branch_id) > 0) {
            $this->mongo_db->where_in('branch_id', $branch_id);
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

}

?>
