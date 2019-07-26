<?php

class visit extends MY_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $table_name = $this->input->get('table');
        $record_id = $this->input->get('_id');

        if (empty($table_name) || empty($record_id)) {
            return;
        }

        $data = array(
            'site_id' => $this->site_id,
            'table_name' => $table_name,
            'record_id' => $record_id,
        );


        $this->load->model($table_name . '_model', 'table');
        $content = $this->table->find(array('_id' => $record_id), 1, 0, array('_id', 'title', 'views'));
        if ($content) {
            $this->table->update(array('_id' => $record_id), array('views' => $content['views'] + 1));
        }
        $data['views'] = $content['views'] + 1;

        $View = new Blitz('template/visit.html');
        $View->display($data);
    }

    public function getWebVisit() { 
        $count = $this->getVisit(); 
        echo 'parent.document.getElementById("visit").innerHTML = ' . $count . ';'; 
    }

}

?>