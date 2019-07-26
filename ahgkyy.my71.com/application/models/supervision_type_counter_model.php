<?php

class supervision_type_counter_model extends MY_Model {

    function __construct() {
        parent::__construct();

        $this->table_name = 'supervision_type_counter';
        $this->class_name = 'supervision_type_counter_model';
    }

    public function genTypeCount($access_branchs = null, $access_users = null, $filter_list = null, $from_date = null, $to_date = null) {
        $my_filter_array = array();

        if (($access_branchs != null) && !(is_array($access_branchs))) {
            $my_filter_array["branch_id"] = $access_branchs;
        } else if ( is_array($access_branchs)  ) {
            $my_filter_array["branch_id"] = array("\$in" => $access_branchs );            
        }
        if (($access_users !== null) && !(is_array($access_users))) {
            $my_filter_array['creator.id'] = $access_users;
        }
        if ($filter_list && is_array($filter_list)) {
            if (isset($filter_list['_id']) && !($filter_list['_id'] instanceof MongoId)) {
                $filter_list['_id'] = $this->SafeMongoId($filter_list['_id']);
            }
            foreach ($filter_list as $key => $val) {
                $my_filter_array[$key] = $val;
            }
        }
        $tmp_arr = array();
        if ($from_date) {
            $tmp_arr["\$gte"] = (int) strtotime($from_date . ' 00:00:00');
        }

        if ($to_date) {
            $tmp_arr["\$lt"] = (int) strtotime('+1 day', strtotime($to_date));
        }

        if (count($tmp_arr) > 0) {
            $my_filter_array['create_date'] = $tmp_arr;
        }

//print_r($my_filter_array);
        $query = array(
            "mapreduce" => "supervision",
            "map" => "function Map() {

                        emit(
                                {
                                        'product_id' : this.product_id,
                                        'question_id': this.question_id,
                                        'site_id': this.site_id,
                                },
                                {
                                        'total' : 1
                                }
                        );
                    }",
            "reduce" => "function Reduce(key, values) {

                        var reduced = { total : 0 }; // initialize a doc (same format as emitted value)
                        values.forEach(function(val) {
                                reduced.total   += val.total; 	// reduce logic
                        });

                        return reduced;

                    }",
            "finalize" => " function Finalize(key, reduced) {
                        return reduced;
                    } ",
            "query" => $my_filter_array,
            "out" => "supervision_type_counter"

        );

//echo $this->mongo_db->last_query();
        $result = $this->mongo_db->command($query);
//print_r($result);

        return $result['ok'];
//        if ($result['ok']) {
//            return $this->genBranchTotalCount();
//        } else {
//            return array();
//        }
    }


    
    public function genTypeStatusCount($access_branchs = null, $access_users = null, $filter_list = null, $from_date = null, $to_date = null) {
        $my_filter_array = array();

        if (($access_branchs != null) && !(is_array($access_branchs))) {
            $my_filter_array["branch_id"] = $access_branchs;
        } else if ( is_array($access_branchs)  ) {
            $my_filter_array["branch_id"] = array("\$in" => $access_branchs );            
        }
        if (($access_users !== null) && !(is_array($access_users))) {
            $my_filter_array['creator.id'] = $access_users;
        }
        if ($filter_list && is_array($filter_list)) {
            if (isset($filter_list['_id']) && !($filter_list['_id'] instanceof MongoId)) {
                $filter_list['_id'] = $this->SafeMongoId($filter_list['_id']);
            }
            foreach ($filter_list as $key => $val) {
                $my_filter_array[$key] = $val;
            }
        }
        $tmp_arr = array();
        if ($from_date) {
            $tmp_arr["\$gte"] = (int) strtotime($from_date . ' 00:00:00');
        }

        if ($to_date) {
            $tmp_arr["\$lt"] = (int) strtotime('+1 day', strtotime($to_date));
        }

        if (count($tmp_arr) > 0) {
            $my_filter_array['create_date'] = $tmp_arr;
        }

//print_r($my_filter_array);
        $query = array(
            "mapreduce" => "supervision",
            "map" => "function Map() {

                        emit(
                                {
                                        'site_id': this.site_id,
                                        'product_id' : this.product_id,
                                        'question_id': this.question_id,
                                        'process_status' : this.process_status
                                },
                                {
                                        'total' : 1
                                }
                        );
                    }",
            "reduce" => "function Reduce(key, values) {

                        var reduced = { total : 0 }; // initialize a doc (same format as emitted value)
                        values.forEach(function(val) {
                                reduced.total   += val.total; 	// reduce logic
                        });

                        return reduced;

                    }",
            "finalize" => " function Finalize(key, reduced) {
                        return reduced;
                    } ",
            "query" => $my_filter_array,
            "out" => array("inline" => 1)

        );

//echo $this->mongo_db->last_query();
        $result = $this->mongo_db->command($query);
//print_r($result);

        return isset($result['results'])?$result['results']:array();

    }

    
    
}

?>