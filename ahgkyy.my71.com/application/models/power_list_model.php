<?php

class Power_list_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'power_list';
        $this->class_name = 'power_list_model';
        $this->table_field = array(
            //必须字段
            'create_date' => 0, //创建时间
            'release_date' => 0, //发文时间
            'update_date' => 0, //修改时间
            'site_id' => '', //站点ID
            'sort' => 200, //排序
            'removed' => FALSE,
            'status' => FALSE,
            'confirm_date' => 0, //审核时间
            'confirmer' => array('id' => '', 'name' => ''), //审核人
            'creator' => array('id' => '', 'name' => ''), //创建人
            'views' => 0, //点击 查看 次数
            //基础字段（公共字段）
            'power_type_id' => '', //事项类型（必填）
            'branch_id' => '', //部门ID
            'branch_type' => '', //部门类型
            'title' => '', //事项名称
            'link_url' => '', //链接地址
            //办事服务
            'type' => array(), //所属类型
            'law_time' => '', //法定期限
            'agree_time' => '', //承诺时限
            'transaction_branch' => '', //办理部门
            'transaction_time' => '', //办理时间
            'transaction_site' => '', //办理地点
            'phone' => '', //联系电话
            'supervise_phone' => '', //监督电话
            'set_gist' => '', //设定依据
            'apply_condition' => '', //申请条件
            'fee_scale' => '', //收费依据
            'procedure_status' => FALSE, //办理流程状态 true代表流程中存放的是图片 false代表是文本
            'procedure' => '', //办理流程
            'document' => '', //办理材料
            'download' => array(), //表格下载
            //信息公开
            'column_id' => '', //组配
            'column_code' => 0, //组配 编码
            'office_id' => '', // 科室
            'topic_id' => array(), //主题
            'no' => '', // 编号
            'serial_number' => '', //索 引 号
            'openness_date' => '', //发文 日期
            'body' => '', //内容
            'description' => '', //描述
            'document_number' => '', //发布文号
            'thumb_name' => '', //缩略图 名称
            'tag' => array(), //关键字
            'author' => '', //作　　者
            'copy_from' => '', // 信息来源
            'validity' => array(//有效期
                'effect_date' => 0, //生效日期
                'abolition_date' => 0, //废止日期
            ),
            'publisher' => array(), //发布机构
            'format' => '', //信息格式
            'language' => '中文', //语种
            'relation' => array(), //相关内容 信息
            //权力清单
            'orgName' => '', //实施主体
            'scope' => '', //承办机构
            'remark' => '', //备注
            'duty_items' => array(), //责任事项 [0=>['content'//责任内容,'remark'//备注信息]]
            'trace_situation' => array(), //追责情形 [0=>['content'//追责情形,'remark'//备注信息]]
            'implement_according_to' => array(), //实施依据 [0=>['content'//实施依据]]
            'incorrupt_risk_dot' => array(), //廉政风险点 [0=>['name'//环节名称,'content'//表现形式,'protect_level'//风险点等级,'control_type'//防控措施,'responsible_man'//责任人,'remark'//备注信息]]
            'external_flow_chart' => array(), //外部流程图 [0=>['file_name'//流程图,'remark'//备注信息]]
        );
    }
    
    public function findList($keyword, $filter_list = array(), $from_date = null, $to_date = null, $limit = 20, $offset = 0, $select = array(), $arr_sort = null) {
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
        $this->mongo_db->limit($limit);
        $this->mongo_db->offset($offset);
        $query = $this->mongo_db->get($this->table_name);
        return $query;
    }

    public function listCount($keyword, $filter_list = array(),$from_date = null, $to_date = null) {
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
    
    public function getOldTop($filter_list = array(), $oldId = null, $limit = 1, $offset = 0, $select = '*', $sort_array = null) {
        foreach ($filter_list as $key => $filter) {
            if ($filter !== "" && $filter !== NULL) {
                $this->mongo_db->where($key, $filter);
            }
        }
        if ($oldId) {
            if (!($oldId instanceof MongoId)) {
                $oldId = $this->SafeMongoId($oldId);
            }
            $this->mongo_db->where_ne('_id', $oldId);
        }
        if ($sort_array !== null && is_array($sort_array)) {
            $this->mongo_db->order_by($sort_array);
        } else {
            $this->mongo_db->order_by(array('_id' => 'DESC'));
        }
        $this->mongo_db->select($select);
        $this->mongo_db->limit($limit);
        $this->mongo_db->offset($offset);
        $query = $this->mongo_db->get($this->table_name);
        if ($query && $limit == 1) {
            return $query[0];
        } else {
            return $query;
        }
    }

}
