<?php

class openness_topic_counter_model  extends MY_Model {
    

    function __construct() {
        parent::__construct();

        $this->table_name = 'openness_topic_counter';
        $this->class_name = 'openness_topic_counter_model';
        $this->table_field = array(
                "site_id" => "" ,
                "topic_id" => "" ,
                "branch_id" => "" ,
                "total" =>  0,    
                "removed" => false,
            );
    }    
    
    
    public function genTopicBranchData( $filter_list = null, $from_date = null, $to_date = null, $limit=20) {
        $my_filter_array = array();

        if ($filter_list && is_array($filter_list)) {
            if (isset($filter_list['_id']) && !($filter_list['_id'] instanceof MongoId)) {
                $filter_list['_id'] = $this->SafeMongoId($filter_list['_id']);
            }
            foreach ($filter_list as $key => $val) {
                $my_filter_array[$key] =  $val;
            }
        }
        $tmp_arr = array();
        if ($from_date) {
            $tmp_arr["\$gte"] = (int)strtotime($from_date . ' 00:00:00') ;
        }

        if ($to_date) {
            $tmp_arr["\$lt"] =  (int)strtotime('+1 day', strtotime($to_date));
        }

        if(count($tmp_arr)>0) {
            $my_filter_array['create_date'] = $tmp_arr;
        }

//print_r($my_filter_array);
        $query = array(
            "aggregate" =>  "openness_content",
            "pipeline" => array(
                        array("\$match" => $my_filter_array ),
                        array("\$project"  => array( "topic_id" => 1 , "branch_id" => 1 ) ),
                        array("\$unwind" => "\$topic_id"),
                        array("\$group" => array( "_id" =>array("topic_id" => "\$topic_id", "branch_id" => "\$branch_id" ),
                                            "total"  => array("\$sum" => 1) ) ),
                        array("\$sort" => array( "total" => -1 ) ),
                        array("\$limit" => $limit ),                
                    ));

        $result = $this->mongo_db-> command ($query);
//echo $this->mongo_db->last_query();
//print_r($result);
        return $result['result'];

    }        
    
    
}

?>
