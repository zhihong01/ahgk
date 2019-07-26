<?php

class openness_request_counter_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'openness_request_counter';
        $this->class_name = 'openness_request_counter_model';
        $this->table_field = array(
            "site_id" => "",
            "branch_id" => "",
            "total" => 0,
        );
    }

    public function getBranchData( $access_branchs=null, $access_users=null, $filter_list = null, $from_date = null, $to_date = null) {
        $my_filter_array = array();

        if ( ($access_branchs != null) && !(is_array($access_branchs))) {
            $my_filter_array["branch_id"] = $access_branchs;
        } else if (($access_branchs != null) && (is_array($access_branchs)) ) {
            $my_filter_array["branch_id"] = array( "\$in" => $access_branchs );
        }
        if ( ($access_users !== null) && !(is_array($access_users))) {
            $my_filter_array['creator.id'] = $access_users;
        }
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
            "mapreduce" => "openness_request",
            "map" => "function Map() {
                        emit(
                                {
                                'site_id' : this.site_id,
                                'branch_id' : this.request_branch},
                                {'total' : 1}
                        );
                    }",
            "reduce" => "function Reduce(key, values) {

                            var reduced = {total:0};

                            values.forEach(function(val) {
                                    reduced.total += val.total;
                            });

                            return reduced;

                    }",

            "finalize" => " function Finalize(key, reduced) {
                        return reduced;
                    } ",
            "query"  => $my_filter_array,
            "out" => "openness_request_counter"
            //array( "replace" => "openness_request_counter"   )
            ////array("inline" => 1 )
            );

//echo $this->mongo_db->last_query();
        $result = $this->mongo_db-> command ($query);
//print_r($result);
        return $result;

    }

    public function getBranchData_aggregate( $filter_list = null, $from_date = null, $to_date = null) {
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
            "aggregate" =>  "openness_request",
            "pipeline" => array(
                        array("\$match" => $my_filter_array ),
                        array("\$group" => array( "_id" => "\$request_branch" ,
                                            "total"  => array("\$sum" => 1) ) ),
                        array("\$sort" => array( "total" => -1 ) ),
                    ));

        $result = $this->mongo_db-> command ($query);
//echo $this->mongo_db->last_query();
//print_r($result);
        return $result['result'];

    }

    public function create_stat($data) {
        if (is_array($data)) {

           $result = $this->mongo_db->insert('openness_request_counter', $data);

            return $result;
        }
        return null;
    }

}


?>