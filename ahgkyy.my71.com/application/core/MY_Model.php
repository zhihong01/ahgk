<?php

class MY_Model extends CI_Model {

    protected $table_name;    // Table name to use
    protected $class_name;    // Class name for logging errors
    protected $table_field = array();

    public function __construct() {
        parent::__construct();

        $this->load->library('mongo_db');
    }

    public function field() {
        return $this->table_field;
    }

    protected function clearField($data) {
        if (count($this->table_field) > 0) {
            foreach ($data as $key => $val) {
                if (!isset($this->table_field[$key])) {
                    unset($data[$key]);
                }
            }
        }
        return $data;
    }

    public function find($where_array = null, $limit = 1, $offset = 0, $select = '*', $sort_array = null) {
        // Apply filters
        if (is_array($where_array)) {
            if (isset($where_array['_id']) && !($where_array['_id'] instanceof MongoId)) {
                $where_array['_id'] = new MongoId($where_array['_id']);
            }
            foreach ($where_array as $key => $val) {
                $this->mongo_db->where($key, $val);
            }
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

    public function count($where_array = null) {

        if (is_array($where_array)) {
            if (isset($where_array['_id']) && !($where_array['_id'] instanceof MongoId)) {
                $where_array['_id'] = new MongoId($where_array['_id']);
            }
            foreach ($where_array as $key => $val) {
                $this->mongo_db->where($key, $val);
            }
        }

        $query = $this->mongo_db->count($this->table_name);
        return (int) $query;
    }

    public function create($data) {
        if (is_array($data)) {
            if (isset($data['_id']) && !($data['_id'] instanceof MongoId)) {
                $data['_id'] = new MongoId($data['_id']);
            }

            $data = array_merge($this->table_field, $this->clearField($data));
            $result = $this->mongo_db->insert($this->table_name, $data);

            return $result;
        }
        return null;
    }

    public function update($where_array, $data, $option = array()) {
        if (is_array($where_array)) {
            if (isset($where_array['_id']) && !($where_array['_id'] instanceof MongoId)) {
                $where_array['_id'] = new MongoId($where_array['_id']);
            }
            foreach ($where_array as $key => $val) {
                $this->mongo_db->where($key, $val);
            }
        }

        $data = $this->clearField($data);
        $this->mongo_db->set($data);
        return $this->mongo_db->update($this->table_name, $option);
    }

    public function delete($where_array) {
        if (is_array($where_array)) {
            if (isset($where_array['_id']) && !($where_array['_id'] instanceof MongoId)) {
                $where_array['_id'] = new MongoId($where_array['_id']);
            }
            foreach ($where_array as $key => $val) {
                $this->mongo_db->where($key, $val);
            }

            if ($this->mongo_db->delete($this->table_name)) {
                return true;
            }
        }
        return false;
    }

    public function incCounter($where_array, $fields = array(), $value = 0) {
        if (isset($where_array['_id']) && !($where_array['_id'] instanceof MongoId)) {
            $where_array['_id'] = new MongoId($where_array['_id']);
        }
        foreach ($where_array as $key => $val) {
            $this->mongo_db->where($key, $val);
        }

        $fields = $this->clearField($fields);
        if (count($fields)) {
            foreach ($fields as $k => $v) {
                $this->mongo_db->inc($k, $v);
            }
            return $this->mongo_db->update($this->table_name);
        } else {
            return NULL;
        }
    }

    public function findName($where_array = null) {
        $this->mongo_db->select(array('_id', 'name'));

        if (is_array($where_array)) {
            $this->mongo_db->where($where_array);
        }

        $result = $this->mongo_db->get($this->table_name);
        $name_list = array();
        $count = count($result);

        for ($i = 0; $i < $count; $i++) {
            $name_list[(string) $result[$i]['_id']] = $result[$i]['name'];
        }
        return $name_list;
    }

    public function getMaps($where_array = null) {
        if (is_array($where_array)) {
            if (isset($where_array['_id']) && (!is_array($where_array['_id'])) && !($where_array['_id'] instanceof MongoId)) {
                $where_array['_id'] = new MongoId($where_array['_id']);
            }
            foreach ($where_array as $key => $val) {
                $this->mongo_db->where($key, $val);
            }
        }

        $result = $this->mongo_db->get($this->table_name);
        $ret_map = array();
        $count = count($result);
        for ($i = 0; $i < $count; $i++) {
            $ret_map[(string) $result[$i]['_id']] = $result[$i];
        }
        return $ret_map;
    }

}

?>
