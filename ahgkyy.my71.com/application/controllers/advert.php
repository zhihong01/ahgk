<?php

class advert extends MY_Controller {

    protected $media_type = array('0' => '未知', '1' => '文本', '2' => '图片', '3' => 'Flash');

    public function __construct() {
        parent::__construct();
	  
    }


    public function getCode() {
		
        header('Content-type: text/javascript; charset=utf-8');
        $location_id = $this->input->get('_id');
		
        $this->load->model('advert_resource_model', 'advert_resource');
        $advert = $this->advert_resource->find(array('location_id' => $location_id, 'status' => TRUE, 'removed' => false), 1, 0, array('_id', 'site_id', 'code'));

        if ($advert) {

            $this->load->model('advert_display_log_model', 'advert_display');
            $data = array('resource_id' => $advert['_id'], 'client_ip' => $this->input->ip_address());
            $opens = $this->advert_display->find($data, 1);

            if ($opens) {
                $this->advert_display->update($data, array('opens' => $opens['opens'] + 1));
            } else {

                $data['site_id'] = $advert['site_id'];
                $data['create_date'] = time();
                $data['user_agent'] = mb_substr($_SERVER['HTTP_USER_AGENT'],0,100);
                $this->advert_display->create($data);
            }

            echo($advert['code']);
        }
    }

    public function go() {
        $resource_id = $this->input->get('_id');
        $this->load->model('advert_resource_model', 'advert_resource');
        $advert = $this->advert_resource->find(array('_id' => $resource_id, 'status' => TRUE, 'removed' => false), 1, 0, array('_id', 'site_id', 'target_url'));

        if ($advert) {
            $this->load->model('advert_click_log_model', 'advert_click');
            $data = array('resource_id' => $resource_id, 'client_ip' => $this->input->ip_address());
            $click = $this->advert_click->find($data, 1);
            if ($click) {
                $this->advert_click->update($data, array('clicks' => $click['clicks'] + 1));
            } else {

                $data['site_id'] = $advert['site_id'];
                $data['create_date'] = time();
                $data['user_agent'] = mb_substr($_SERVER['HTTP_USER_AGENT'],0,100);
                $this->advert_click->create($data);
            }
			if(empty($advert['target_url'])){
				$advert['target_url'] = '/';
			}
            header('Location:' . $advert['target_url']);
            exit();
        }
    }



}

