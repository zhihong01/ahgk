<?php

class do_result_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'do_result';
        $this->class_name = 'do_result_model';
        $this->table_field = array(
            'site_id' => '',
            'removed' => false,
            'status' => false,
            'sort' => 0,
            'confirm_date' => 0,
            'confirmer' => array('id' => '', 'name' => ''),
            'create_date' => 0,
            'creator' => array('id' => '', 'name' => ''),
            'no' => 0, //受理编号
            'type' => 0,
            'accept' => '', //申报项目(受理事项)
            'proposer' => '', //法人 (申请人) ,  
            'accepted_date' => '', //受理日期
            'result' => '', //结果
            
            "apply_address" => "" , //地址
            "apply_branch" => "" ,  //申报单位
            "person_in_charge" => "" ,  //企业负责人
            "quality_person" => "" ,  //质量负责人
            "business_scope" => "" ,  //经营范围
            "phone" => "" ,  //联系电话
            "check_person" => "" ,  //现场验收人员
            "complete_date" => 0,  //办结确认时间
            'cancel_result' => '', //取消状态
            "id" => 0 //文档中的序列号
        );
    }

    public function findList($keyword = '', $filter_list = array(), $from_date = null, $to_date = null, $limit = 20, $offset = 0, $select = array(), $arr_sort = null) {


        if (strlen($keyword) > 1) {
            $this->mongo_db->like('accept', $keyword);
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
        $this->mongo_db->limit($limit);
        $this->mongo_db->offset($offset);

        $query = $this->mongo_db->get($this->table_name);
        //print_r($this->mongo_db->last_query());
        return $query;
    }

    public function listCount($keyword = '', $filter_list = array(), $from_date = null, $to_date = null) {

        if (strlen($keyword) > 1) {
            $this->mongo_db->like('accept', $keyword);
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