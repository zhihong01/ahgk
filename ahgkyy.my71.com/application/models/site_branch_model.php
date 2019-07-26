<?php

class site_branch_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'site_branch';
        $this->class_name = 'site_branch_model';

        $this->table_field = array(
            'address' => array(
                'province' => '',
                'city' => '',
                'area' => '',
                'street' => ''
            ),
            'branch_code' => 0,
            'city_code' => '',
            'create_date' => 0,
            'creator' => array(
                'id' => '',
                'name' => ''
            ),
            'duty' => '',
            'email' => '',
            'full_name' => '',
            'id' => 11,
            'mobile' => '',
            'name' => '',
            'office_address' => '',
            'parent_id' => '',
            'phone' => '',
            'site_id' => '',
            'sort' => 0,
            'status' => 1,
            'type_id' => 0,
            'website' => ''
        );
    }

    public function getBranchInfo($_id_list = array(), $select = array()) {
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

    public function findList($type_id = array(), $keyword = '', $filter_list = array(), $from_date = null, $to_date = null, $limit = 20, $offset = 0, $select = array(), $arr_sort = null) {

        if (!empty($type_id)) {
            $this->mongo_db->where('type_id', $type_id);
        } else {
            $this->mongo_db->where('type_id !=', null);
        }


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

        if ($arr_sort != null && is_array($arr_sort)) {
            $this->mongo_db->order_by($arr_sort);
        } else {
            $this->mongo_db->order_by(array('_id' => 'DESC'));
        }

        $this->mongo_db->select($select);
        if ($limit != '') {
            $this->mongo_db->limit($limit);
        }
        $this->mongo_db->offset($offset);

        $query = $this->mongo_db->get($this->table_name);

        return $query;
    }

    public function listCount($type_id = array(), $keyword = '', $filter_list = array(), $from_date = null, $to_date = null) {

        if (!empty($type_id)) {
            $this->mongo_db->where('type_id', $type_id);
        } else {
            $this->mongo_db->where('type_id !=', null);
        }


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

}

?>
