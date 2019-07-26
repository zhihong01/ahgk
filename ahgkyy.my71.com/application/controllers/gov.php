<?php

/* * ***
 * 宣城市人民政府 政务大厅
 * *** */
require_once(APPPATH . 'libraries/nusoap/nusoap.php');
class gov extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('service_content_model', 'service_content');
        $this->load->model('site_attach_model', 'service_download');
        $this->load->model('service_type_model', 'service_type');
        $this->load->model('service_policy_model', 'service_policy');
    }

    // 部门列表
    protected function itemBranch($channel_id, $limit = 20, $offset = 0, $length = 10, $current_id = FALSE) {

        $this->load->model('site_branch_model', 'site_branch');

        $filter = array('parent_id' => (string) $channel_id, 'status' => true, 'service_on' => true, 'removed' => False, 'site_id' => $this->site_id);
        $select = array('_id', 'name', 'id');
        $arr_sort = array('sort' => 'DESC');

        $item_list = $this->site_branch->find($filter, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            if ($item['_id'] == $current_id) {
                $item_list[$key]['aon'] = 'class="aon"';
            } else {
                $item_list[$key]['aon'] = '';
            }
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['name'], 0, $length);
            } else {
                $item_list[$key]['short_title'] = $item['name'];
            }
            $item_list[$key]['url'] = '/serviceBranch/?type=' . $channel_id . '&_id=' . $item['_id'];
        }
        return $item_list;
    }

    //表格下载(老版附件在service_download中，新版在)
    protected function serviceDownloadList($service_type, $limit = 10, $offset = 0, $length = 60, $date_format = 0) {
        $this->load->model('site_attach_model', 'site_attach');
        $select = array('_id', 'title', 'release_date','branch_id');
        $sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        if ($service_type) {
            $filter = array('service_type' => $service_type, 'removed' => false, 'status' => true, 'module' => "serviceDownload", 'site_id' => $this->site_id);
        } else {
            $filter = array('removed' => false, 'status' => true, 'module' => "serviceDownload", 'site_id' => $this->site_id);
        }
        $item_list = $this->site_attach->find($filter, $limit, $offset, $select, $sort);
		$this->branch_list = $this->getBranchName();
        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = '/download/?mod=site_attach&_id=' . $item['_id'];
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
			$item_list[$key]['branch_name'] = $this->branch_list[$item['branch_id']];
			$item_list[$key]['short_title'] = $item_list[$key]['branch_name'] ."&nbsp;--&nbsp;".$item_list[$key]['short_title'];
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }
        return $item_list;
    }

    //办事指南
    protected function serviceContentList($service_type, $limit = 10, $offset = 0, $length = 60, $date_format = 0) {
        $this->load->model('service_content_model', 'service_content');
        $select = array('_id', 'title', 'release_date');
        //$sort = array('sort' => 'DESC');
        $sort = array('release_date' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        if ($service_type) {
            $filter = array('service_type' => $service_type, 'removed' => false, 'status' => true, 'site_id' => $this->site_id);
        } else {
            $filter = array('removed' => false, 'status' => true, 'site_id' => $this->site_id);
        }
        $item_list = $this->service_content->find($filter, $limit, $offset, $select, $sort);
        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = '/gov/detail/' . $item['_id'] . '.html';
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }
        return $item_list;
    }

    // 服务指南
    protected function itemServiceContent($_id, $limit = 50, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {

        $this->load->model('service_content_model', 'service_content');

        $filter = array('type' => (string) $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id);
        $select = array('_id', 'title', 'branch_id', 'confirm_date', 'download', 'policy', 'link_url');
        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];

        $item_list = $this->service_content->find($filter, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = $item['link_url'] ? $item['link_url'] : '/gov/contentDetail/' . $item['_id'] . '.html';
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            // 取部门
            if (isset($this->branch_list[$item['branch_id']])) {
                $item_list[$key]['branch'] = $this->branch_list[$item['branch_id']];
                $item_list[$key]['branch_url'] = '/supervision/branch/?_id=' . $item['branch_id'];
            } else {
                $item_list[$key]['branch'] = '';
            }
            $item_list[$key]['date'] = ($item['confirm_date']) ? date($date_format, $item['confirm_date']) : '';
        }
        return $item_list;
    }

    // 服务类型
    protected function itemServiceType($_id, $limit = 50, $offset = 0, $length = 60) {

        $this->load->model('service_type_model', 'service_type');

        $filter = array('parent_id' => $_id, 'status'=>true, 'removed' => false, 'site_id' => $this->site_id);
        $select = array('_id', 'name');
        $sort = array('sort' => 'DESC');

        $item_list = $this->service_type->find($filter, $limit, $offset, $select, $sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = '/gov/type/?type=' . $_id . '&_id=' . $item['_id'];
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['short_name'] = mb_substr($item['name'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_name'] = $item['name'];
            }
        }
        return $item_list;
    }

    protected function serviceTypeList($parent_id, $limit = 50, $offset = 0, $length = 60) {

        $this->load->model('service_type_model', 'service_type');

        $filter = array('parent_id' => $parent_id, 'removed' => false, 'site_id' => $this->site_id);
        $select = array('_id', 'name');
        $sort = array('sort' => 'DESC');

        $item_list = $this->service_type->find($filter, $limit, $offset, $select, $sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = '/gov/type/?type=' . $parent_id . '&_id=' . $item['_id'];
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['name'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['name'];
            }
        }
        return $item_list;
    }

    protected function getMenu($channel_id, $limit = 50, $offset = 0, $length = 60) {
        $result = array();
        $this->load->model("site_channel_tree_model", "site_channel_tree");
        $channel_tree = $this->site_channel_tree->find(array('_id' => $channel_id), 1);

        if (isset($channel_tree['child'])) {
            $i = 0;
            foreach ($channel_tree['child'] as $key => $value) {
                if ($i >= $limit) {
                    break;
                }
                if ($i < $offset) {
                    continue;
                }
                $result[$key] = mb_substr($value, 0, $length);
                $i++;
            }
        }
        return $result;
    }
	
	 // 友情链接
    protected function friendLinkList($_id_list, $limit = 10, $offset = 0, $length = 60, $date_format = 0) {

        $this->load->model('friend_link_model', 'friend_link');

        $filter = array("type_id" => $_id_list, 'status' => true, 'removed' => False, 'site_id' => $this->site_id);
        $arr_sort = array('sort' => 'DESC');

        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'link_url', 'file_path', 'width', 'height', 'target', 'confirm_date');

        $item_list = $this->friend_link->find($filter, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = (string) ($item['link_url']);
            $item_list[$key]['thumb'] = (string) ($item['file_path']);
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }

            $item_list[$key]['date'] = ($item['confirm_date']) ? date($date_format, $item['confirm_date']) : '';
        }
        return $item_list;
    }
	protected function bjgkList($limit = 50, $offset = 0, $length = 60) {

        $MY_OAWSDL = "http://zwfw.xuancheng.gov.cn/jiekou/index.php?wsdl"; 
        $client = new nusoap_client($MY_OAWSDL, true);
        $err = $client->getError();
        if ($err) {
            $this->resultJson("获取用户 接口失败。");
            exit();
        }
        $client->soap_defencoding = 'utf-8';
        $client->decode_utf8 = false;
        $client->xml_encoding = 'utf-8';
        
        $oa_key = 'qDWkR1idgpGyLIqK';
        $time = date('YmdHis');
        $key= md5($oa_key.$time);
		
        $user_data = array(
            "key" => $key,
            "curTime" => $time,
            "limit" => $limit,
            "BizID" => 0
        );
        
        $result = $client->call('GetGongkai', $user_data);
		$item_list = $this->parseXML($result);
		foreach ($item_list as $key => $item) {
            $item['name'] = strip_tags(html_entity_decode($item['projectname']));
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['name'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['name'];
            }
			$item_list[$key]['date'] = ($item['accept_time']) ? mb_substr($item['accept_time'],0,10) : '';
        }
        return $item_list;
    }
	protected function tongjiInfo($limit = 50, $offset = 0, $length = 60) {

        $MY_OAWSDL = "http://zwfw.xuancheng.gov.cn/jiekou/index.php?wsdl"; 
        $client = new nusoap_client($MY_OAWSDL, true);
        $err = $client->getError();
        if ($err) {
            $this->resultJson("获取用户 接口失败。");
            exit();
        }
        $client->soap_defencoding = 'utf-8';
        $client->decode_utf8 = false;
        $client->xml_encoding = 'utf-8';
        
        $oa_key = 'qDWkR1idgpGyLIqK';
        $time = date('YmdHis');
        $key= md5($oa_key.$time);
		
        $user_data = array(
            "key" => $key,
            "curTime" => $time,
            "limit" => $limit,
            "BizID" => 0
        );
        
        $result = $client->call('GetTongji', $user_data);
		$item_list = $this->parseXML($result);
        return $item_list;
    }
	public function parseXML($result) {

        //解析XML
        $xml = new DOMDocument('1.0');
        $xml->loadXML($result['datas']);
        $xml_array = (array)simplexml_import_dom($xml);
		$data=array();
		foreach($xml_array as $key=>$val){
			if(is_array($val)){
				foreach($val as $k=>$v){
					$data[$k][$key]=$v;
				}
			}else{
				$data=array($xml_array);
			}
		}
        return $data;
    }


    public function index() {
        $page = (int) $this->input->get('page');
        if ($page == 0) {
            $page = 1;
        }
        $data = array();
        $View = new Blitz('template/gov.html');
        $struct_list = $View->getStruct();
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                if ($action == 'servicedownload') {
                    list($service_type, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);
                    if ($service_type == 'all') {
                        $service_type = NULL;
                    }
                    $item_list = $this->serviceDownloadList($service_type, $limit, $offset, $length, $date_format);
                } elseif ($action == 'servicecontent') {
                    list($service_type, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);
                    if ($service_type == 'all') {
                        $service_type = NULL;
                    }
                    $item_list = $this->serviceContentList($service_type, $limit, $offset, $length, $date_format);
                } elseif ($action == 'servicetype') {

                    list($parent_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    $item_list = $this->serviceTypeList($parent_id, $limit, $offset, $length);
                } elseif ($action == 'type') {
                    //服务类型
                    list($type, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($type == 'all') {
                        $type = null;
                    }
                    $item_list = $this->itemServiceType($type, $limit, $offset, $length);
                }elseif ($action == 'bjgk') {
                    list($limit, $offset, $length) = explode('_', $matches[2]);
                    $item_list = $this->bjgkList($limit, $offset, $length);
                } elseif ($action == 'content') {
                    // 办事指南
                    list($parent_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    if ($parent_id == 'current') {
                        $type = (string) $current_type['_id'];
                    } else {
                        $type = $parent_id;
                    }
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->itemServiceContent($type, $limit, $offset, $length, $sort_by, $date_format);

                    foreach ($item_list as $key => $item) {
                        if (!empty($item['download'])) {
                            $item_list[$key]['download'] = '<a href="/service/contentDetail/' . $item['_id'] . '.html#download" target="_blank">表格下载</a>';
                        } else {
                            $item_list[$key]['download'] = '';
                        }
                        if (!empty($item['policy'])) {
                            $item_list[$key]['policy'] = '<a href="/service/contentDetail/' . $item['_id'] . '.html#policy" target="_blank">相关政策<a/>';
                        } else {
                            $item_list[$key]['policy'] = '';
                        }
                    }
                } elseif ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $item_list = array('page' => $this->getPagination($total_row, $page, $per_count, False));
                } elseif ($action == 'branch') {
                    //服务类型
                    list($type_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    $item_list = $this->itemBranch($type_id, $limit, $offset, $length);
                }elseif ($action == 'friendlink') {
                    list($channel_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);
                    $item_list = $this->friendLinkList($channel_id, $limit, $offset, $length, $date_format);
                }
                $data[$struct_val] = $item_list;
            }
        }
        $View->set(array('folder_prefix' => $this->folder_prefix));
		$data['banjian'] = str_replace('tr','tr  class="itablelist"',file_get_contents('./banjian/banshi.dat'));
		$tongji=$this->tongjiInfo();
		$data['total_brsl']=$tongji[0]['count'];
		$data['total_brbj']=$tongji[1]['count'];
		$data['total_bysl']=$tongji[2]['count'];
		$data['total_bybj']=$tongji[3]['count'];
		$data['total_bnsl']=$tongji[4]['count']+35412;
		$data['total_bnbj']=$tongji[5]['count']+35321;
        $View->display($data);
    }

    public function type() {
        $type_id = (string) $this->input->get('type');
        $_id = (string) $this->input->get('_id');
        $page = (int) $this->input->get('page');
        if ($page == 0) {
            $page = 1;
        }
		
		$has_child=$this->service_type->find(array('parent_id' => $_id, 'removed' => false, 'site_id' => $this->site_id,'status'=>true), 1, 0, array('_id', 'name', 'linkurl'), array('sort' => 'DESC'));
		if(!empty($has_child)){
			header("Location: /gov/type/?type=" . $_id);
			exit();
		}
		
        $parent_type = $this->service_type->find(array('_id' => $type_id, 'removed' => false, 'site_id' => $this->site_id,'status'=>true), 1, 0, array('_id', 'name'));
        if (empty($parent_type)) {
            show_404();
        }
        if (empty($_id)) {
            $current_type = $this->service_type->find(array('parent_id' => $type_id, 'removed' => false, 'site_id' => $this->site_id,'status'=>true), 1, 0, array('_id', 'name', 'linkurl'), array('sort' => 'DESC'));
        } else {
            $current_type = $this->service_type->find(array('_id' => $_id, 'removed' => false, 'site_id' => $this->site_id,'status'=>true), 1, 0, array('_id', 'name', 'linkurl'));
        }

        if (empty($current_type)) {
            show_404($current_type);
        }
        if (!empty($current_type['linkurl'])) {
            header("Location: " . $current_type['linkurl']);
            exit();
        }
        $total_row = $this->service_content->count(array('type' => (string) $current_type['_id'], 'status' => true, 'removed' => false, 'site_id' => $this->site_id), null, 0, array('_id'));

        $View = new Blitz('template/list-gov.html');
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                if ($action == 'type') {
                    //服务类型
                    list($type, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($type == 'all') {
                        $type = null;
                    } else {
                        $type = $type_id;
                    }
                    $item_list = $this->itemServiceType($type, $limit, $offset, $length);
                } elseif ($action == 'content') {
                    // 办事指南
                    list($parent_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    if ($parent_id == 'current') {
                        $type = (string) $current_type['_id'];
                    } else {
                        $type = $parent_id;
                    }
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->itemServiceContent($type, $limit, $offset, $length, $sort_by, $date_format);

                    foreach ($item_list as $key => $item) {
                        $item_list[$key]['content'] = '<a href="/gov/contentDetail/' . $item['_id'] . '.html" target="_blank">办事指南</a>';
                        if (!empty($item['download'])) {
                            $item_list[$key]['download'] = '<a href="/gov/contentDetail/' . $item['_id'] . '.html#download" target="_blank">表格下载</a>';
                        } else {
                            $item_list[$key]['download'] = '';
                        }
                        if (!empty($item['policy'])) {
                            $item_list[$key]['policy'] = '<a href="/gov/contentDetail/' . $item['_id'] . '.html#policy" target="_blank">相关政策<a/>';
                        } else {
                            $item_list[$key]['policy'] = '';
                        }
                    }
                } elseif ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $item_list = array('page' => $this->getPagination($total_row, $page, $per_count, False));
                }
                $data[$struct_val] = $item_list;
            }
        }
        $data['parent_type'] = $parent_type;
        $data['service_id'] = $_id;
        $data['location'] = '<a href="/">首页</a> / <a href="/gov/">政务大厅</a> / <a href="/gov/type/?type=' . $parent_type['_id'] . '">' . $parent_type['name'] . '</a> / <span>' . $current_type['name'] . '</span>';
        $View->set(array('folder_prefix' => $this->folder_prefix, 'jstag' => 'service'));
        $View->display($data);
    }

    public function detail() {
        $_id = (string) $this->input->get('_id');
        $content = $this->service_content->find(array('_id' => $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id));
        if ($content['link_url']) {
            header("Location: " . $content['link_url']);
        }
        if (empty($content)) {
            show_404();
        }
        $View = new Blitz('template/detail-service.html');
        $this->service_content->update(array('_id' => $_id), array("views" => $content['views'] + 1));
        $content['release_date'] = ($content['confirm_date']) ? date('Y-m-d H:i:s', $content['confirm_date']) : '';
        $content['content'] = htmlspecialchars_decode($content['content']);
        $download_list = '';
        if (!empty($content['download'])) {
            $download_list = '<br/><br/><font size=4 color="red">资料下载</font>:<br/><font size=3>';
            foreach ($content['download'] as $_id) {
                $item_list = $this->service_download->find(array('_id' => (string) $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id), null, 0, array('_id', 'title'));
                foreach ($item_list as $item) {
                    $title = $item['title'];
                    //$url = '/gov/downloadDetail/' . $item['_id'] . '.html';
					$url = '/download/?mod=site_attach&_id=' . $item['_id'];
                    $download_list.= '&nbsp;&nbsp;&nbsp;&nbsp;' . $title . "、";
                    $download_list.= "[<a href='" . $url . "' style='color:blue' target='_blank'>下载</a>]<br>";
                }
            }
            $download_list.='</font>';
        }
        $policy_list = '';
        if (!empty($content['policy']) && !empty($content['policy'][0])) {
            $policy_list = '<br/><br/><font size=4 color="red">相关政策:</font><br/><font size=3>';
            foreach ($content['policy'] as $_id) {
                $item_list = $this->service_policy->find(array('_id' => (string) $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id), null, 0, array('title', '_id'));
                foreach ($item_list as $item) {
                    $url = '/gov/policyDetail/' . $item['_id'] . '.html';
                    $title = $item['title'];
                    $policy_list .= "&nbsp;&nbsp;&nbsp;&nbsp;<a href='" . $url . "' target='_blank'>" . $title . "</a><br/>";
                }
            }
            $policy_list.='</font>';
        }
        $data = array(
            'location' => '<a href="/">网站首页</a> / <a href="/gov/">政务大厅</a> / ' . $content['title'],
            'download' => $download_list,
            'policy' => $policy_list,
            'content' => $content
        );
        $View->display($data);
    }

    public function download() {

        $page = (string) $this->input->get('page');
        $total_row = count($this->service_download->find(array('status' => True, 'removed' => false, 'site_id' => $this->site_id), 100, 0, array('_id')));

        $View = new Blitz('template/list.html');
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                if ($action == 'list') {
                    list($service_type, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);
                    if ($service_type != 'current') {
                        $service_type = $explode('-', $service_type);
                    } else {
                        $service_type = null;
                    }
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->serviceDownloadList($service_type, $limit, $offset, $length, $date_format);
                } elseif ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $link = $this->getPagination($total_row, $page, $per_count);
                    $item_list['page'] = $link;
                } elseif ($action == 'menu') {
                    list($parent_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($parent_id != 'current') {
                        $channel_id = $parent_id;
                    } else {
                        $channel_id = $parent_channel['_id'];
                    }

                    $menu_list = $this->getMenu($channel_id, $limit, $offset, $length);
                    $i = 0;
                    foreach ($menu_list as $key => $menu) {
                        $item_list[$i]['_id'] = $key;
                        $item_list[$i]['url'] = $this->folder_prefix . '/channel/' . $key . '/';
                        $item_list[$i]['name'] = $menu;
                        $i++;
                    }
                }
                $data[$struct_val] = $item_list;
            }
        }

        $data['channel_name'] = '表格下载';
        $data['location'] = '<a href="/">网站首页</a> / <a href="/service/download/">表格下载</a> ';

        $View->display($data);
    }

    public function content() {
        $page = (string) $this->input->get('page');
        $total_row = count($this->service_content->find(array('status' => True, 'removed' => false, 'site_id' => $this->site_id), 100, 0, array('_id')));
        $View = new Blitz('template/list.html');
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                if ($action == 'list') {
                    list($service_type, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);
                    if ($service_type != 'current') {
                        $service_type = $explode('-', $service_type);
                    } else {
                        $service_type = null;
                    }
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->serviceContentList($service_type, $limit, $offset, $length, $date_format);
                } elseif ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $link = $this->getPagination($total_row, $page, $per_count);
                    $item_list['page'] = $link;
                } elseif ($action == 'menu') {
                    list($parent_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($parent_id != 'current') {
                        $channel_id = $parent_id;
                    } else {
                        $channel_id = $parent_channel['_id'];
                    }

                    $menu_list = $this->getMenu($channel_id, $limit, $offset, $length);
                    $i = 0;
                    foreach ($menu_list as $key => $menu) {
                        $item_list[$i]['_id'] = $key;
                        $item_list[$i]['url'] = $this->folder_prefix . '/channel/' . $key . '/';
                        $item_list[$i]['name'] = $menu;
                        $i++;
                    }
                }
                $data[$struct_val] = $item_list;
            }
        }

        $data['channel_name'] = '服务指南';
        $data['location'] = '<a href="/">网站首页</a> / <a href="/service/content/">服务指南</a> ';

        $View->display($data);
    }

    public function contentDetail() {
        $_id = (string) $this->input->get('_id');
        $content = $this->service_content->find(array('_id' => $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id));
        if ($content['link_url']) {
            header("Location: " . $content['link_url']);
        }
        if (empty($content)) {
            show_404();
        }
        $View = new Blitz('template/detail-service.html');
        $this->service_content->update(array('_id' => $_id), array("views" => $content['views'] + 1));
        $content['release_date'] = ($content['release_date']) ? date('Y-m-d H:i:s', $content['release_date']) : '';
        $content['content'] = htmlspecialchars_decode($content['content']);
        $download_list = '';
        if (!empty($content['download'])) {
            $download_list = '<br/><br/><font size=4 color="red">资料下载</font>:<br/><font size=3>';
            foreach ($content['download'] as $_id) {
                $item_list = $this->service_download->find(array('_id' => (string) $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id), null, 0, array('_id', 'title'));
                foreach ($item_list as $item) {
                    $title = $item['title'];
                    //$url = '/gov/downloadDetail/' . $item['_id'] . '.html';
					$url = '/download/?mod=site_attach&_id=' . $item['_id'];
                    $download_list.= '&nbsp;&nbsp;&nbsp;&nbsp;' . $title . "、";
                    $download_list.= "[<a href='" . $url . "' style='color:blue' target='_blank'>下载</a>]<br>";
                }
            }
            $download_list.='</font>';
        }
        $policy_list = '';
        if (!empty($content['policy']) && !empty($content['policy'][0])) {
            $policy_list = '<br/><br/><font size=4 color="red">相关政策:</font><br/><font size=3>';
            foreach ($content['policy'] as $_id) {
                $item_list = $this->service_policy->find(array('_id' => (string) $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id), null, 0, array('title', '_id'));
                foreach ($item_list as $item) {
                    $url = '/gov/policyDetail/' . $item['_id'] . '.html';
                    $title = $item['title'];
                    $policy_list .= "&nbsp;&nbsp;&nbsp;&nbsp;<a href='" . $url . "' target='_blank'>" . $title . "</a><br/>";
                }
            }
            $policy_list.='</font>';
        }
        $data = array(
            'location' => '<a href="/">网站首页</a> / <a href="/gov/">政务大厅</a> / ' . $content['title'],
            'download' => $download_list,
            'policy' => $policy_list,
            'content' => $content
        );
        $View->display($data);
    }

    public function policyDetail() {
        $_id = (string) $this->input->get('_id');
        $content = $this->service_policy->find(array('_id' => $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id));
        if (empty($content)) {
            show_404();
        }
        $View = new Blitz('template/detail-service.html');
        $content['content'] = $content['body'];
        $content['date'] = ($content['confirm_date']) ? date('Y-m-d H:i:s', $content['confirm_date']) : '';

        $data['content'] = $content;
        $data['location'] = '<a href="/">网站首页</a> / <a href="/service/">政务大厅</a>';
        $View->display($data);
    }

    public function downloadDetail() {
        $_id = (string) $this->input->get('_id');
        $attachment = $this->service_download->find(array('_id' => $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id));

        if (empty($attachment)) {
            header("Content-type: text/html; charset=utf-8");
            show_error('错误：记录不存在。');
        }
        $subdir = substr($attachment['saved_name'], 0, 8);
        $full_file = $this->upload_url . '/' . $subdir . '/' . $attachment['saved_name'];
        header("Content-Type:" . $attachment['file_type']);
        header('Content-Disposition: attachment; filename="' . mb_convert_encoding($attachment['real_name'], 'GBK', 'UTF-8') . '"');
        header('Content-Length:' . $attachment['file_size']);
        ob_clean();
        flush();
        readfile($full_file);
    }

    //办事指南
    public function govContent() {
        $page = (string) $this->input->get('page');
        $total_row = count($this->service_content->find(array('status' => True, 'removed' => false, 'site_id' => $this->site_id), NULL, 0, array('_id')));
        $View = new Blitz('template/list.html');
        $struct_list = $View->getStruct();
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                if ($action == 'list') {
                    list($service_type, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);
                    if ($service_type != 'current') {
                        $service_type = explode('-', $service_type);
                    } else {
                        $service_type = null;
                    }
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->serviceContentList($service_type, $limit, $offset, $length, $date_format);
                } elseif ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $link = $this->getPagination($total_row, $page, $per_count);
                    $item_list['page'] = $link;
                }
                $data[$struct_val] = $item_list;
            }
        }

        $data['channel_name'] = '办事指南';
        $data['location'] = '<a href="/">网站首页</a> / <a href="/gov/">政务大厅</a> / <a href="/gov/govContent/">办事指南</a> ';

        $View->display($data);
    }

    //表格下载
    public function govDownload() {
        $page = (string) $this->input->get('page');
        $total_row = count($this->service_download->find(array('module' => "serviceDownload",'status' => True, 'removed' => false, 'site_id' => $this->site_id), NULL, 0, array('_id')));

        $View = new Blitz('template/list.html');
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                if ($action == 'list') {
                    list($service_type, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);
                    if ($service_type != 'current') {
                        $service_type = explode('-', $service_type);
                    } else {
                        $service_type = null;
                    }
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->serviceDownloadList($service_type, $limit, $offset, $length, $date_format);
                } elseif ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $link = $this->getPagination($total_row, $page, $per_count);
                    $item_list['page'] = $link;
                }
                $data[$struct_val] = $item_list;
            }
        }

        $data['channel_name'] = '表格下载';
        $data['location'] = '<a href="/">网站首页</a> / <a href="/gov/">政务大厅</a> / <a href="/gov/govDownload/">表格下载</a> ';

        $View->display($data);
    }

}

/* End of file gov.php */
/* Location: ./application/controllers/gov.php */