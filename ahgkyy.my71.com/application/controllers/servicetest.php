<?php

class servicetest extends MY_Controller {

    public function __construct() {
        parent::__construct();
    }

	public function index() { 

		$this->load->model('web_service_model', 'web_service'); 
		$this->load->model('service_content_model', 'service_content'); 

		$item_list = $this->web_service->find(array(), null); 
		$i=0;
		foreach ($item_list as $value) { 
		
			$info_url = "http://www.tlx.gov.cn/include/service_view.php?ty=".$value['id']."&id=".$value['id']; 
//echo $info_url."<br/>";
			$ch = curl_init($info_url); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 获取数据返回 
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, true); // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回 

			$output = curl_exec($ch); 

			// 错误链接 
			if(strpos($output, "Not Found")){ 
				continue; 
			} 

			$mode = "/<table width=\"100%\"[\s\S]*?>[\s\S]*?<\/table>/";
			if(preg_match_all($mode,$output,$arr)){  
				foreach($arr as $v){ 
					if(empty($v)){ 
						continue; 
					} 
					//header("Content-Type: text/html; charset=gbk");
					//print_R($v[0]); exit;
					//echo $value['info_id']; 
					$content=iconv('GB2312', 'UTF-8', $v[0]);
					$result = $this->service_content->update(array('old_Id' => $value['id']), array('content' => $content)); 

					if($result){ 
						echo $i.'<br/>'; 
						$i++; 
					} 
				} 
			} 
			 
		} 
	} 
}
