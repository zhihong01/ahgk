<?php

class download extends MY_Controller {

    public function __construct() {
        parent::__construct();

    }


    public function index() {
	
        $_id = $this->input->get('_id');
        $mod = $this->input->get('mod');
		
		$this->load->model($mod . '_model', 'mod');
		
        $attachment = $this->mod->find(array('_id' => $_id, 'removed' => false), 1, 0);
		//print_r($attachment);die();
		if(!empty($attachment['source_table'])||!empty($attachment['src_table'])){
			$fileurl='http://www.huainan.gov.cn/'.$attachment['media_path'].$attachment['saved_name'];
			$file_is_exits=file_get_contents($fileurl);
			if(empty($file_is_exits)){
				header("location:/");
			}else{
				header("location:".$fileurl);
			}
		}else{
			
			 if (empty($attachment)) {
				header("Content-type: text/html; charset=utf-8");
				show_error('错误：记录不存在。');
			}

			$subdir = substr($attachment['saved_name'], 0, 6);
			//$full_file = $this->upload_url.$this->site_id.'/'.$subdir . '/' . $attachment['saved_name'];
            $full_file = $attachment['media_path'] . $attachment['saved_name'];
			header("Content-Type:" . $attachment['file_type']);

			header('Content-Disposition: attachment; filename="' . mb_convert_encoding($attachment['real_name'], 'GBK', 'UTF-8') . '"');

			header('Content-Length:' . $attachment['file_size']);

			ob_clean();
			flush();

			readfile($full_file);
				
		}
		

       
    }

}

?>