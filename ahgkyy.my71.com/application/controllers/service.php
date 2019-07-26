<?php

class service extends MY_Controller {

    private $channel_tree = array("name" => "网上办事", "link_url" => "/service/");

    public function __construct() {
        parent::__construct();
        $this->load->model('service_content_model', 'service_content');
        $this->load->model('site_attach_model', 'site_attach');
        $this->load->model('service_type_model', 'service_type');
        $this->load->model('service_policy_model', 'service_policy');
		$this->load->model('site_channel_model', 'site_channel');
        $this->load->model('site_channel_tree_model', 'site_channel_tree');
        $this->load->model('content_model', 'content');
    }
	
	
	
	
	  protected function attachList($content_id) {
        $this->load->model('site_attach_model', 'site_attach');
        $item_list = $this->site_attach->find(array('module_id' => $content_id), NULL);

        return $item_list;
    }
	
	 // 友情链接
    protected function friendLinkList($_id_list, $limit = 10, $offset = 0, $length = 60, $date_format = 0) {

        $this->load->model('friend_link_model', 'friend_link');

        $filter = array("type_id" => $_id_list, 'status' => true, 'removed' => False, 'site_id' => $this->site_id);
        $arr_sort = array('sort' => 'ASC');

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
	

    protected function serviceTypeList($parent_id, $limit = 50, $offset = 0, $length = 60) {
        $this->load->model('service_type_model', 'service_type');
        $filter = array('parent_id' => $parent_id,'removed' => false,  'status' => true, 'site_id' => $this->site_id);
        $select = array('_id', 'name');
        $sort = array('sort' => 'DESC');
        $item_list = $this->service_type->find($filter, $limit, $offset, $select, $sort);
        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = '/service/type/?type=' . $parent_id . '&_id=' . $item['_id'];
            //$item_list[$key]['url'] = '/serviceSubject/' . $item['_id'].'/';
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['name'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['name'];
            }
        }
        return $item_list;
    }

    protected function serviceDownloadList($service_type, $limit = 10, $offset = 0, $length = 60, $date_format = 0) {
        $this->load->model('site_attach_model', 'site_attach');
        $select = array('_id', 'title', 'release_date');
        $sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        if ($service_type) {
            $filter = array('attach_type' => $service_type, 'removed' => false, 'status' => true, 'site_id' => $this->site_id, "module"=>"serviceDownload");
        } else {
            $filter = array('removed' => false, 'status' => true, 'site_id' => $this->site_id,"module"=>"serviceDownload");
        }

        $item_list = $this->site_attach->find($filter, $limit, $offset, $select, $sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
             $item_list[$key]['url'] = '/download/?mod=site_attach&_id=' . $item['_id'];
			 // $item_list[$key]['url'] = 'http://file.maoji.gov.cn/mserver/download/?_id=' . $item['_id'].'&SiteId=575688fdceab06373c526171';
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }
        return $item_list;
    }

    protected function serviceContentList($service_type, $limit = 10, $offset = 0, $length = 60, $date_format = 0) {

        $this->load->model('service_content_model', 'service_content');

        $select = array('_id', 'title', 'release_date');
        $sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];

        if ($service_type) {
            $filter = array('type' => $service_type, 'removed' => false, 'status' => true, 'site_id' => $this->site_id);
        } else {
            $filter = array('removed' => false, 'status' => true, 'site_id' => $this->site_id);
        }
        $item_list = $this->service_content->find($filter, $limit, $offset, $select, $sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = '/service/detail/' . $item['_id'] . '.html';
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }
        return $item_list;
    }

 // 相关政策
    protected function policyList($type, $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {
		if ($type) {
            $filter = array('service_type' => $type, 'site_id' => $this->site_id, 'status' => true, 'removed' => false);
        } else {
            $filter = array('removed' => false, 'status' => true, 'site_id' => $this->site_id);
        }
        $select = array('_id', 'title', 'create_date');
        $arr_sort = array('create_date' => 'DESC');
        $date_format = $this->date_foramt['1'];
		
        $item_list = $this->service_policy->find($filter, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['url'] = '/service/policyDetail/?_id=' . $item['_id'];
            $item['title'] = strip_tags(html_entity_decode($item['title']));
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            $item_list[$key]['date'] = ($item['create_date']) ? date($date_format, $item['create_date']) : '';
        }
        return $item_list;
    }
    
    // 服务指南
    protected function itemServiceContent($_id, $limit = 50, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {
        $this->load->model('service_content_model', 'service_content');
		if ($_id) {
            $filter = array('type' => (string) $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id);
        } else {
            $filter = array('removed' => false, 'status' => true, 'site_id' => $this->site_id);
        }

        $select = array('_id', 'title', 'branch_id', 'release_date', 'download', 'policy', 'link_url');
        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];

        $item_list = $this->service_content->find($filter, $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = $item['link_url'] ? $item['link_url'] : '/service/contentDetail/?_id=' . $item['_id'];
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            // 取部门
            if (isset($this->branch_list[$item['branch_id']])) {
                $item_list[$key]['branch'] = $this->branch_list[$item['branch_id']];
                $item_list[$key]['branch_url'] = '/supervision/branch/' . $item['branch_id'] . '/';
            } else {
                $item_list[$key]['branch'] = '';
            }
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }
        return $item_list;
    }

 // 服务类型
    protected function itemServiceType($_id, $limit = 50, $offset = 0, $length = 60) {
        $this->load->model('service_type_model', 'service_type');
        $filter = array('parent_id' => $_id,  'status' => true, 'removed' => false, 'site_id' => $this->site_id);
        $select = array('_id', 'name');
        $sort = array('sort' => 'DESC');
        $item_list = $this->service_type->find($filter, $limit, $offset, $select, $sort);
        $count = count($item_list);
        foreach ($item_list as $key => $item) {
            $item_list[$key]['key'] = $key;
            $item_list[$key]['count'] = $count;
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = '/service/type/?type=' . $_id . '&_id=' . $item['_id'];
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['name'], 0, $length);
            } else {
                $item_list[$key]['short_title'] = $item['name'];
            }
            $child_list = $this->service_type->find(array('parent_id' => (string) $item['_id']), $limit, $offset, $select, $sort);
            if (!empty($child_list)) {
                foreach ($child_list as $key_val => $val) {
                    $child_list[$key_val]['_id'] = (string) $val['_id'];
                    $child_list[$key_val]['url'] = '/service/content/?type=' . $_id . '&_id=' . $item['_id'] . "&child_id=" . $val['_id'];
                    if (mb_strlen($val['name']) > $length) {
                        $child_list[$key_val]['short_title'] = mb_substr($val['name'], 0, $length);
                    } else {
                        $child_list[$key_val]['short_title'] = $val['name'];
                    }
                }
            }
            $item_list[$key]['child_list'] = $child_list;
        }
        return $item_list;
    }

    //调取当前服务下子栏目信息
    protected function childContentList($type_id, $_id, $limit = 50, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {
        $this->load->model('service_type_model', 'service_type');
        $filter = array('parent_id' => $_id, 'status' => true,  'removed' => false, 'site_id' => $this->site_id);
        $select = array('_id', 'name');
        $sort = array('sort' => 'DESC');
        $item_list = $this->service_type->find($filter, NULL, NULL, $select, $sort);
        if (!empty($item_list)) {
            foreach ($item_list as $key => $item) {
                $item_list[$key]['_id'] = (string) ($item['_id']);
                $item_list[$key]['url'] = '/wsbs/content/?type=' . $type_id . "&_id=" . $_id . '&child_id=' . $item['_id'];
                if (mb_strlen($item['name']) > $length) {
                    $item_list[$key]['short_title'] = mb_substr($item['name'], 0, $length);
                } else {
                    $item_list[$key]['short_title'] = $item['name'];
                }
                $item_list[$key]['child_content'] = $this->itemServiceContent($item['_id'], $limit, $offset, $length, $sort_by, $date_format);
            }
        } else {
            $filter = array('_id' => $_id, 'removed' => false, 'site_id' => $this->site_id);
            $item_list = $this->service_type->find($filter, NULL, NULL, $select, $sort);
            $item_list[0]['short_title'] = $item_list[0]['name'];
            $item_list[0]['url'] = '/wsbs/content/?type=' . $type_id . "&_id=" . $_id;
            $item_list[0]['child_content'] = $this->itemServiceContent($_id, $limit, $offset, $length, $sort_by, $date_format);
        }

        return $item_list;
    }
    protected function getMenu($channel_id, $limit = 50, $offset = 0, $length = 60) {
        $this->load->model('site_channel_tree_model', 'site_channel_tree');
        $result = array();
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
	
	
	 protected function contentList($_id_list, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $description_length = 0, $is_pic = false) {

        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'description', 'thumb_name', 'release_date', 'thumb_large', 'link_url', 'type', 'title_color');
        if ($is_pic) {
            $filter = array('status' => true, 'thumb_name' => array("\$ne" => ''), 'removed' => false, 'site_id' => $this->site_id);
        } else {
            $filter = array('status' => true, 'removed' => false, 'site_id' => $this->site_id);
        }
        $item_list = $this->content->findList($_id_list, NULL, $filter, NULL, NULL, $limit, $offset, $select, $arr_sort);
        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item['title'] = strip_tags(html_entity_decode($item['title']));
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }

           /* if ($item['title_color']) {
                $item_list[$key]['short_title'] = "<font style='color:" . $item['title_color'] . "'>" . $item_list[$key]['short_title'] . "</font>";
            }*/

            $item['description'] = str_replace(Chr(32), " ", $item['description']);
            if (mb_strlen($item['description']) > $description_length) {
                $item_list[$key]['description'] = mb_substr($item['description'], 0, $description_length) . '...';
            }
            $item_list[$key]['description'] = nl2br($item_list[$key]['description']);

            $item_list[$key]['url'] = !empty($item['link_url']) ? $item['link_url'] : $this->folder_prefix . '/detail/' . $item['_id'] . '.html';
            $item_list[$key]['thumb'] = $item['type'] == 1 ? $item['thumb_name'] : $item['thumb_large'];
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }

        return $item_list;
    }
	

    public function index() {
		
        $View = new Blitz('template/service/index.html');
        $struct_list = $View->getStruct();
        $data = array();
		
		
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
                } elseif ($action == 'menu') {
                    list($parent_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($parent_id != 'current') {
                        $channel_id = $parent_id;
                    }
                    $menu_list = $this->getMenu($channel_id, $limit, $offset, $length);
                    $i = 0;
                    foreach ($menu_list as $key => $menu) {
                        $item_list[$i]['_id'] = $key;
                        $item_list[$i]['url'] = $this->folder_prefix . '/channel/' . $key . '/';
                        $item_list[$i]['name'] = $menu;
                        $i++;
                    }
                } elseif ($action == 'friendlink') {
                    list($channel_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);

                    $item_list = $this->friendLinkList($channel_id, $limit, $offset, $length, $date_format);
                }
				elseif ($action == 'bjgk') {
                    list($limit, $offset, $length) = explode('_', $matches[2]);
                    $item_list = $this->bjgkList($limit, $offset, $length);
				}				
				elseif ($action == 'list') {
                    list($channel_id, $limit, $offset, $length, $date_format, $description_length) = explode('_', $matches[2]);
                    $this_channel = $this->site_channel_tree->find(array('_id' => $channel_id));
                    if (!empty($this_channel['child'])) {
                        unset($_id_list);
                        foreach ($this_channel['child'] as $key => $val)
                            $_id_list[] = $key;
                    } else {
                        $_id_list = explode('-', $channel_id);
                    }
                    $item_list = $this->contentList($_id_list, $limit, $offset, $length, $date_format, $description_length);
                }
                $data[$struct_val] = $item_list;
            }
        }
        //$data['banshi_service'] = file_get_contents('./data/banshi_service.dat');
        //$data['tongji'] = file_get_contents('./data/tongji.dat');
		$data['bjgs'] = file_get_contents('./data/bjgs.dat');
		$data['bstj'] = file_get_contents('./data/bstj.dat');
		
        $View->set(array('folder_prefix' => $this->folder_prefix));
	    $data['channel_name'] = $this->channel_tree['name'];
		 
		
		
       $View->display($data);
       
    }
	
    public function type() {
        $type_id = (string) $this->input->get('type');
        $_id = (string) $this->input->get('_id');
        $page = (int) $this->input->get('page');
		$channel_tree = $this->site_channel_tree->find(array('_id' => $channel_id), 1);
        if ($page == 0) {
            $page = 1;
        }
		
		$has_child=$this->service_type->find(array('parent_id' => $_id, 'removed' => false, 'site_id' => $this->site_id), 1, 0, array('_id', 'name'));
        if (!empty($has_child)) {
            header("Location: /service/type/?type=" . $_id);
            exit();
        }
		
        $parent_type = $this->service_type->find(array('_id' => $type_id, 'removed' => false, 'site_id' => $this->site_id), 1, 0, array('_id', 'name'));
        if (empty($parent_type)) {
            show_404();
        }
        if (empty($_id)) {
            $current_type = $this->service_type->find(array('parent_id' => $type_id, 'removed' => false, 'site_id' => $this->site_id), 1, 0, array('_id', 'name', 'linkurl'), array('sort' => 'DESC'));
        } else {
            $current_type = $this->service_type->find(array('_id' => $_id, 'removed' => false, 'site_id' => $this->site_id), 1, 0, array('_id', 'name', 'linkurl'));
        }

        if (empty($current_type)) {
            show_404($current_type);
        }
        if (!empty($current_type['linkurl'])) {
            header("Location: " . $current_type['linkurl']);
            exit();
        }
        $total_row = $this->service_content->count(array('type' => (string) $current_type['_id'], 'status' => true, 'removed' => false, 'site_id' => $this->site_id), null, 0, array('_id'));
		$View = new Blitz('template/service/list-service.html');
		
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
                        $item_list[$key]['content'] = '<a href="/service/contentDetail/?_id=' . $item['_id'] . ' target="_blank">办事指南</a>';
                       /* $item_list[$key]['supervision'] = '<a href="/supervision/branch/' . $item['branch_id'] . '/" target="_blank">网上咨询</a>';*/
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
						if(!empty($item_list[$key]['policy'])||!empty($item_list[$key]['download'])){
							$item_list[$key]['has_line']=true;
						}
                    }
                } elseif ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $item_list = array('page' => $this->getPagination($total_row, $page, $per_count, False));
                }elseif ($action == 'list') {//办事指南
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);

                    if ($channel_id != 'current') {
                        $_id_array = explode('-', $channel_id);
                    } else {
                        $_id_array = (string) $current_type['_id'];
                    }
                    $item_list = $this->itemServiceContent($_id_array, $limit, $offset, $length, $sort_by, $date_format);
                }else if ($action == 'download') {//表格下载
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    if ($channel_id != 'current') {
                        $_id_array = explode('-', $channel_id);
                    } else {
                        $_id_array =(string) $current_type['_id'];
                    }
                    $item_list = $this->serviceDownloadList($_id_array, $limit, $offset, $length,$date_format);
                }else if ($action == 'policy') {//相关政策
                    list($channel_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($channel_id != 'current') {
                        $_id_array = explode('-', $channel_id);
                    } else {
                        $_id_array = (string) $current_type['_id'];
                    }
                    $item_list = $this->policyList($_id_array, $limit, $offset, $length);
                }
				elseif ($action == 'friendlink') {
                    list($channel_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);

                    $item_list = $this->friendLinkList($channel_id, $limit, $offset, $length, $date_format);
                }
                $data[$struct_val] = $item_list;
            }
        }
        $data['parent_type'] = $parent_type;
		$data['channel_name'] = $current_type['name'];
        $data['type']=(string) $current_type['_id'];
        $data['type_name']=$current_type['name'];
        $data['service_id'] = $_id;
        $data['location'] = '<a href="/">网站首页</a> / <a href="/service/">网上办事</a> / <a href="/service/type/?type=' . $parent_type['_id'] . '">' . $parent_type['name'] . '</a> / <span>' . $current_type['name'] . '</span>';
        $View->set(array('folder_prefix' => $this->folder_prefix, 'jstag' => 'service'));
        $View->display($data);
    }
    
    
    
	public function priority() {
        $type_id = (string) $this->input->get('type');
        $_id = (string) $this->input->get('_id');
        $page = (int) $this->input->get('page');
        if ($page == 0) {
            $page = 1;
        }
        $parent_type = $this->service_type->find(array('_id' => $type_id, 'removed' => false, 'site_id' => $this->site_id), 1, 0, array('_id', 'name'));
        
        if (empty($parent_type)) {
            show_404();
        }

        if (empty($_id)) {
            $current_type = $this->service_type->find(array('parent_id' => $type_id, 'removed' => false, 'site_id' => $this->site_id), 1, 0, array('_id', 'name', 'linkurl'), array('sort' => 'DESC'));
        } else {
            $current_type = $this->service_type->find(array('_id' => $_id, 'removed' => false, 'site_id' => $this->site_id), 1, 0, array('_id', 'name', 'linkurl'));
        }

        if (!empty($current_type['linkurl'])) {
            header("Location: " . $current_type['linkurl']);
            exit();
        }
        $total_row = $this->service_content->count(array('type' => (string) $current_type['_id'], 'status' => true, 'removed' => false, 'site_id' => $this->site_id), null, 0, array('_id'));
  
		
		$View = new Blitz('template/list-service-zdly.html');
		
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
                } elseif ($action == 'childlist') {
                    list($limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    $item_list = $this->childContentList($type_id, (string) $current_type['_id'], $limit, $offset, $length, $sort_by, $date_format);
                }
                $data[$struct_val] = $item_list;
            }
        }
        $data['service_name'] = $parent_type['name'];
        $data['service_id'] = empty($_id) ? $current_type['_id'] : $_id;
        $data['type_id'] = $type_id;
        $data['location'] = '<a href="/">网站首页</a> / <a href="/service/">网上办事</a> / <a href="/wsbs/type/?type=' . $parent_type['_id'] . '">' . $parent_type['name'] . '</a> / <span>' . $current_type['name'] . '</span>';
        $View->set(array('folder_prefix' => $this->folder_prefix, 'jstag' => 'service'));
        $View->display($data);
    }
    

    public function detail() {
        $_id = $this->input->get('_id');

        $content = $this->service_content->find(array('_id' => $_id, 'status' => true, 'removed' => false));

        if (!empty($content['link_url'])) {
            header("Location: " . $content['link_url']);
        }

        $content['body'] = htmlspecialchars_decode($content['content']);
        $content['release_date'] = ($content['release_date']) ? date('Y-m-d', $content['release_date']) : '';

        $content['table_name'] = 'service_content';
        $data = array(
            'content' => $content,
            'folder_prefix' => $this->folder_prefix,
        );
        $current_type = $this->service_type->find(array('_id' => (string) $content['type'][0]), 1, 0, array('name'));
        $data['location'] = "<a href='/'>网站首页</a> / <a href='/service/'>网上办事</a> / <a href='/service/type/?type=".$current_type['_id']."'>" . $current_type['name'] . '</a> ';

        $View = new Blitz('template/detail.html');
        $struct_list = $View->getStruct();

        $View->display($data);
    }

    public function download() {
    	$_id = (string) $this->input->get('_id');
    	if (empty($_id)) {
            $current_type = $this->service_type->find(array('parent_id' => $type_id, 'removed' => false, 'site_id' => $this->site_id), 1, 0, array('_id', 'name', 'linkurl'), array('sort' => 'DESC'));
        } else {
            $current_type = $this->service_type->find(array('_id' => $_id, 'removed' => false, 'site_id' => $this->site_id), 1, 0, array('_id', 'name', 'linkurl','parent_id'));
        }
        $page = (string) $this->input->get('page');
		 if ($page == 0) {
            $page = 1;
		 }
        $total_row = $this->site_attach->count(array('module' => 'serviceDownload','status' => True, 'removed' => false, 'site_id' => $this->site_id));
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
                        $service_type =(string) $current_type['_id'];
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
                    $link = $this->getPagination($total_row, $page, $per_count,true);
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
                }else if ($action == 'type') {
                    //服务类型
                    list($type, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($type == 'all') {
                        $type = null;
                    } else {
                        $type = (string) $current_type['parent_id'];;
                    }
                    $item_list = $this->itemServiceType($type, $limit, $offset, $length);
                } 
                $data[$struct_val] = $item_list;
            }
        }
         $data['menu_name'] = '表格下载';
        $data['channel_name'] = '表格下载';
        $data['location'] = '<a href="/">网站首页</a> / <a href="/service/">网上办事</a> / <a href="/service/download/">表格下载</a> ';

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
         $data['menu_name'] = '办事指南';
        $data['channel_name'] = '办事指南';
        $data['location'] = '<a href="/">网站首页</a> / <a href="/service/">网上办事</a> / <a href="/service/content/">办事指南</a> ';

        $View->display($data);
    }

    public function contentDetail() {
	
        $_id = (string) $this->input->get('_id');
        $content = $this->service_content->find(array('_id' => $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id));
		  if (!empty($content['author'])) {
            $content['author'] = '作者： ' . $content['author'];
        }

        if ($content['link_url']) {
            header("Location: " . $content['link_url']);
        }

        
        $View = new Blitz('template/service/detail-service.html');
        
        $this->service_content->update(array('_id' => $_id), array("views" => $content['views'] + 1));
        $content['release_date'] = ($content['release_date']) ? date('Y-m-d H:i:s', $content['release_date']) : '';
        $content['content'] = htmlspecialchars_decode($content['content']);
		
		$content['content'] = str_replace(array('"/picture','"http://218.22.238.60/picture'),'"/jcms/jcms_files/jcms1/web1/site/picture',$content['content']);
        $download_list = '';
        if (!empty($content['download'])) {
            $download_list = '<br/><br/><font size=4 color="#0d870d">表格下载</font>:<br/><font size=3>';
            foreach ($content['download'] as $_id) {
                $item_list = $this->site_attach->find(array('_id' => (string) $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id), null, 0, array('_id', 'title'));
                foreach ($item_list as $item) {
                    $title = $item['title'];
                    // $url = '/service/downloadDetail/' . $item['_id'] . '.html';
					 $url = '/download/?mod=site_attach&_id=' . $item['_id'];
			      // $url = 'http://hexian.u.my71.com/download/?_id=' . $item['_id'].'&SiteId=59313631ceab063f2f611981';
					
                    $download_list.= '&nbsp;&nbsp;&nbsp;&nbsp;' . $title;
                    $download_list.= "[<a href='" . $url . "' style='color:#1C73BB' >下载</a>]<br/>";
                }
            }
            $download_list.='</font>';
        }
        $policy_list = '';
        if (!empty($content['policy']) && !empty($content['policy'][0])) {
            $policy_list = '<br/><br/><font size=4 color="#0d870d">相关政策:</font><br/><font size=3>';
            foreach ($content['policy'] as $_id) {
                $item_list = $this->service_policy->find(array('_id' => (string) $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id), null, 0, array('title', '_id'));
                foreach ($item_list as $item) {
                    $url = '/service/policyDetail/' . $item['_id'] . '.html';
                    $title = $item['title'];
                    $policy_list .= "&nbsp;&nbsp;&nbsp;&nbsp;<a href='" . $url . "' target='_blank'>" . $title . "</a><br/>";
                }
            }
            $policy_list.='</font>';
        }
        array_multisort($content['table_data'],SORT_ASC);
       
        $data = array(
            'download' => $download_list,
            'policy' => $policy_list,
            'content' => $content,
        	'table_data'=>$content['table_data']
        );
        
        $serviceTree = $this->serviceTree($content['type'][count($content['type'])-1]);
        $location = '<a href="/">网站首页</a> / <a href="/service/">网上办事</a>';
        if ($serviceTree['serviceTree']) {
            $array = array_reverse($serviceTree['serviceTree'], TRUE);
            foreach ($array as $item) {
                $location.=' / <span>' . $item['name'] . '</span>';
            }
        }
        $data['location'] = $location;
      
        $View->display($data);
    }
    
     //获取办事服务树关系返回深度和栏目信息
    protected function serviceTree($_id = NULL, $tree = -1, $serviceTree = array()) {
        $tree++;
        if (empty($_id)) {
            return array("tree" => $tree, "serviceTree" => $serviceTree);
        }
        $service = $this->service_type->find(array('_id' => $_id), 1, 0, array('name', 'parent_id'));//print_r($service);die();

        if (empty($service)) {
            return array("tree" => $tree, "serviceTree" => $serviceTree);
        }
        if ($service['parent_id'] == "/") {
            return array("tree" => $tree, "serviceTree" => $serviceTree);
        }
        $serviceTree[] = array("_id" => (string) $service['_id'], "name" => $service['name']);
        return $this->serviceTree($service['parent_id'], $tree, $serviceTree);
    }
    

    public function policyDetail() {
        $_id = (string) $this->input->get('_id');
        $content = $this->service_policy->find(array('_id' => $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id));
        if (empty($content)) {
            show_404();
        }
		 if (!empty($content['author'])) {
            $content['author'] = '作者： ' . $content['author'];
        }
		
	
		
		
        $View = new Blitz('template/service/detail-service.html');
		 $this->service_policy->update(array('_id' => $_id), array("views" => $content['views'] + 1));
        $content['content'] = $content['body'];
        $content['release_date'] = ($content['confirm_date']) ? date('Y-m-d H:i:s', $content['confirm_date']) : '';
        $data['content'] = $content;
			 if ($View->hasContext('attach')) {
            $item_list = $this->attachList($_id);

            $this->load->helper('number');
            foreach ($item_list as $item) {
                $View->block('/attach', array('_id' => $item['_id'],
                    'downloads' => $item['downloads'],
                    'file_size' => byte_format($item['file_size']),
                    'name' => "附件：" . $item['real_name'],
                     // 'url' => 'http://file.tianchang.gov.cn/mserver/download/?_id=' . $item['_id'].'&SiteId='.$item['site_id'],
					'url' => '/index.php?c=content&m=download&_id=' . $item['_id'],
                    'file_type' => $item['file_type'],
                        )
                );
            }
        }
        $data['location'] = '<a href="/">网站首页</a> / <a href="/service/">网上办事</a>';
        $View->display($data);
    }

     public function downloadDetail() {
        $_id = (string) $this->input->get('_id');
        $attachment = $this->site_attach->find(array('_id' => $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id));

        if (empty($attachment)) {
            header("Content-type: text/html; charset=utf-8");
            show_error('错误：记录不存在。');
        }
		if(!empty($attachment['src_table'])){
			header("Location: " . $attachment['old_url']);
		}
		
        $subdir = substr($attachment['saved_name'], 0, 8);
        // $full_file = $this->upload_url . $subdir . '/' . $attachment['saved_name'];
$full_file = $attachment['media_path'] . $attachment['saved_name'];
        $filename = mb_convert_encoding($attachment['real_name'], 'GBK', 'UTF-8');
        if (strrpos($filename, '.')) {
            $filename = mb_convert_encoding($attachment['real_name'], 'GBK', 'UTF-8');
        } else {
            $filename = $filename . '.' . $attachment['file_type'];
        }

        $filesize = $attachment['file_size'];
        if (!$filesize) {
            $header_array = get_headers($full_file, true);
            $filesize = $header_array['Content-Length'];
        }

        header("Content-Type:" . $data['type']);

        header('Content-Disposition: attachment; filename="' . $filename . '"');

        header('Content-Length:' . $filesize);

        ob_clean();
        //flush();

        readfile($full_file);
    }
 
    
 public function guide() {

        $service_id = (string) $this->input->get('_id');
        if (empty($service_id)) {
            $service_id = null;
        }
        $page = (int) $this->input->get('page');
        if ($page == 0) {
            $page = 1;
        }

        $current_service = $this->service_type->find(array('_id' => $service_id), 1, 0, array('_id', 'name', 'parent_id'));
		
		if($service_id){
        $total_row = $this->service_content->count(array('type' => $service_id, 'site_id' => $this->site_id, 'status' => true, 'removed' => false));
		}else{
			 $total_row = $this->service_content->count(array('site_id' => $this->site_id, 'status' => true, 'removed' => false));
			
			}
        $data = array();
        $View = new Blitz('template/list-service-xz.html');
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {

                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                //菜单(二级栏目)
                 if ($action == 'type') {
                    //服务类型
                    list($type, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($type == 'all') {
                        $type = null;
                    } else {
                        $type = (string) $current_service['parent_id'];;
                    }
                    $item_list = $this->itemServiceType($type, $limit, $offset, $length);
                } 

                //服务指南
                if ($action == 'list') {
                    list($channel_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);

                    if ($channel_id != 'current') {
                        $_id_array = explode('-', $channel_id);
                    } else {
                        $_id_array = $service_id;
                    }
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->itemServiceContent($_id_array, $limit, $offset, $length, 0,$date_format);
                }

                //分页
                if ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $item_list['page'] = $this->getPagination($total_row, $page, $per_count, true);
                }

                $data[$struct_val] = $item_list;
            }
        }
        $data['channel_name'] = '办事指南';
        $data['location'] = '<a href="/">网站首页</a> / <a>办事指南</a>';
        $View->display($data);
    }
     
     public function policy() {

        $service_id = (string) $this->input->get('_id');
        if (empty($service_id)) {
            $service_id = null;
        }
        $page = (int) $this->input->get('page');
        if (empty($page)) {
            $page = 1;
        }

        $current_service = $this->service_type->find(array('_id' => $service_id), 1, 0, array('_id', 'name', 'parent_id'));
		
		if($service_id){
        $total_row = $this->service_policy->count(array('service_type' => $service_id, 'site_id' => $this->site_id, 'status' => true, 'removed' => false));
		}else{
			 $total_row = $this->service_policy->count(array('site_id' => $this->site_id, 'status' => true, 'removed' => false));
			}
        $data = array();
          $View = new Blitz('template/list.html');
        // $View = new Blitz('template/list-service-xz.html');
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {

                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                //菜单(二级栏目)
                 if ($action == 'type') {
                    //服务类型
                    list($type, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($type == 'all') {
                        $type = null;
                    } else {
                        $type = (string) $current_service['parent_id'];;
                    }
                    $item_list = $this->itemServiceType($type, $limit, $offset, $length);
                } 

                //服务指南
                if ($action == 'list') {
                    list($channel_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);

                    if ($channel_id != 'current') {
                        $_id_array = explode('-', $channel_id);
                    } else {
                        $_id_array = $service_id;
                    }
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->policyList($_id_array, $limit, $offset, $length, $sort_by, $date_format);
                }

                //分页
                if ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $item_list['page'] = $this->getPagination($total_row, $page, $per_count, true);
                }

                $data[$struct_val] = $item_list;
            }
        }
		$data['menu_name'] = '相关政策';
        $data['channel_name'] = '相关政策';
        $data['location'] = '<a href="/">首页</a> / <a>相关政策</a>';
        $View->display($data);
    }
     
}

?>