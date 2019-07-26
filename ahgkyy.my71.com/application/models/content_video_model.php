<?php

class content_video_model extends MY_Model {

    function __construct() {
        parent::__construct();        

        $this->table_name = 'content_video';
        $this->class_name = 'content_video_model';
    }

}

?>
