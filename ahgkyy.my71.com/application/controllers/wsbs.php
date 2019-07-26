<?php

class wsbs extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('service_content_model', 'service_content');
        $this->load->model('site_attach_model', 'site_attach');
        $this->load->model('service_type_model', 'service_type');
        $this->load->model('service_policy_model', 'service_policy');
    }

    protected function serviceTypeList($parent_id, $limit = 50, $offset = 0, $length = 60) {
        $this->load->model('service_type_model', 'service_type');
        $filter = array('parent_id' => $parent_id, 'removed' => false, 'site_id' => $this->site_id);
        $select = array('_id', 'name');
        $sort = array('sort' => 'DESC');
        $item_list = $this->service_type->find($filter, $limit, $offset, $select, $sort);
        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = '/wsbs/type/?type=' . $parent_id . '&_id=' . $item['_id'];
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['name'], 0, $length);
            } else {
                $item_list[$key]['short_title'] = $item['name'];
            }
        }
        return $item_list;
    }

    protected function serviceDownloadList($service_type, $limit = 10, $offset = 0, $length = 60, $date_format = 0) {

        $select = array('_id', 'title', 'release_date');
        $sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        if ($service_type) {
            $filter = array('service_type' => $service_type, 'removed' => false, 'status' => true, 'site_id' => $this->site_id);
        } else {
            $filter = array('removed' => false, 'status' => true, 'site_id' => $this->site_id);
        }
        $item_list = $this->site_attach->find($filter, $limit, $offset, $select, $sort);
        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = '/download/?mod=service_download&_id=' . $item['_id'];
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
            $filter = array('service_type' => $service_type, 'removed' => false, 'status' => true, 'site_id' => $this->site_id);
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

    // 服务指南
    protected function itemServiceContent($_id, $limit = 50, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {
        $this->load->model('service_content_model', 'service_content');
        $filter = array('status' => true, 'removed' => false, 'site_id' => $this->site_id);
        $select = array('_id', 'title', 'branch_id', 'release_date', 'download', 'policy', 'link_url', 'old_ZXTS', 'is_table');
        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        //$item_list = $this->service_content->find($filter, $limit, $offset, $select, $arr_sort);
		
		
		$item_list = $this->service_content->searchList(array($_id), '', $filter,  null,null, $limit, $offset, $select, $arr_sort );
		
        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = $item['link_url'] ? $item['link_url'] : '/wsbs/contentDetail/' . $item['_id'] . '.html';
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
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }
        return $item_list;
    }

    //部门办事指南
    protected function serviceBranchContent($branch_id, $limit = 50, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {
        $this->load->model('service_content_model', 'service_content');
        $filter = array('branch_id' => $branch_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id);
        $select = array('_id', 'title', 'branch_id', 'release_date', 'download', 'policy', 'link_url', 'is_table');
        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $item_list = $this->service_content->find($filter, $limit, $offset, $select, $arr_sort);
        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = $item['link_url'] ? $item['link_url'] : '/wsbs/contentDetail/' . $item['_id'] . '.html';
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }
        return $item_list;
    }

    // 服务类型
    protected function itemServiceType($_id, $limit = 50, $offset = 0, $length = 60) {
        $this->load->model('service_type_model', 'service_type');
        $filter = array('parent_id' => $_id, 'removed' => false, 'site_id' => $this->site_id);
        $select = array('_id', 'name');
        $sort = array('sort' => 'DESC');
        $item_list = $this->service_type->find($filter, $limit, $offset, $select, $sort);
        $count = count($item_list);
        foreach ($item_list as $key => $item) {
            $item_list[$key]['key'] = $key;
            $item_list[$key]['count'] = $count;
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = '/wsbs/type/?type=' . $_id . '&_id=' . $item['_id'];
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['name'], 0, $length);
            } else {
                $item_list[$key]['short_title'] = $item['name'];
            }
            $child_list = $this->service_type->find(array('parent_id' => (string) $item['_id']), $limit, $offset, $select, $sort);
            if (!empty($child_list)) {
                foreach ($child_list as $key_val => $val) {
                    $child_list[$key_val]['_id'] = (string) $val['_id'];
                    $child_list[$key_val]['url'] = '/wsbs/content/?type=' . $_id . '&_id=' . $item['_id'] . "&child_id=" . $val['_id'];
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
        $filter = array('parent_id' => $_id, 'removed' => false, 'site_id' => $this->site_id);
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
                $item_list[$key]['child_content'] = $this->itemServiceContent((string)$item['_id'], $limit, $offset, $length, $sort_by, $date_format);
            }
        } else {
			$_id=empty($_id)?$type_id:$_id;
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
                $result[$key] = $value;
                $i++;
            }
        }

        return $result;
    }

    protected function contentList($_id_list, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $description_length = 0, $is_pic = false) {
        $this->load->model('content_model', 'content');
        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'description', 'thumb_name', 'release_date', 'thumb_large', 'link_url', 'type', 'body');
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

            $item['description'] = str_replace(Chr(32), " ", $item['description']);
            if (mb_strlen($item['description']) > $description_length) {
                $item_list[$key]['description'] = mb_substr($item['description'], 0, $description_length) . '...';
            }

            $item_list[$key]['url'] = !empty($item['link_url']) ? $item['link_url'] : $this->folder_prefix . '/detail/' . $item['_id'] . '.html';
            $item_list[$key]['thumb'] = $item['type'] == 1 ? $item['thumb_name'] : $item['thumb_large'];
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }

        return $item_list;
    }

    //办事服务（个人办事 企业办事）
    protected function serviceList($parent_id, $limit = 50, $offset = 0, $length = 60) {
        $filter = array('parent_id' => $parent_id, 'removed' => false, 'site_id' => $this->site_id);
        $select = array('_id', 'name');
        $sort = array('sort' => 'DESC');
        $item_list = $this->service_type->find($filter, $limit, $offset, $select, $sort);
        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = '/wsbs/service/?type=' . $parent_id . '&_id=' . $item['_id'];
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['name'], 0, $length);
            } else {
                $item_list[$key]['short_title'] = $item['name'];
            }
        }
        return $item_list;
    }

    //办事部门
    protected function branchList($branch_id, $limit, $offset, $length) {
        $this->load->model("site_branch_model", "site_branch");
        $filter = array('parent_id' => $branch_id, 'service_on' => TRUE, 'removed' => false, 'status' => TRUE, 'site_id' => $this->site_id);
        $select = array('_id', 'name');
        $sort = array('sort' => 'DESC');
        $item_list = $this->site_branch->findList(NULL, NULL, $filter, NULL, NULL, $limit, $offset, $select, $sort);
        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = '/wsbs/branch/?branch_id=' . $item['_id'];
            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['name'], 0, $length);
            } else {
                $item_list[$key]['short_title'] = $item['name'];
            }
        }
        return $item_list;
    }

    public function index() {
        $View = new Blitz('template/wsbs/index.html');
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
                    //重点领域
                    list($parent_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    $item_list = $this->serviceTypeList($parent_id, $limit, $offset, $length);
                } elseif ($action == 'type') {
                    //服务类型
                    list($type, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($type == 'all') {
                        $type = null;
                    }
                    $item_list = $this->itemServiceType($type, $limit, $offset, $length);
                } elseif ($action == 'list') {
                    list($channel_id, $limit, $offset, $length, $date_format, $description_length) = explode('_', $matches[2]);
                    $_id_list = explode('-', $channel_id);
                    $item_list = $this->contentList($_id_list, $limit, $offset, $length, $date_format, $description_length);
                } elseif ($action == 'service') {
                    //办事服务
                    list($parent_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    $item_list = $this->serviceList($parent_id, $limit, $offset, $length);
                } else if ($action == "servicebranch") {
                    //办事部门
                    list($branch_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($branch_id == "all") {
                        $branch_id = NULL;
                    }
                    $item_list = $this->branchList($branch_id, $limit, $offset, $length);
                }
                $data[$struct_val] = $item_list;
            }
        }
        //办事公示抓取
        $data['banshi'] = file_get_contents('./data/banshi_wsbs.dat');
        $banshi_num = explode(chr(10), file_get_contents('./data/banshi_num.dat'));
        $data['num0'] = $banshi_num[0];
        $data['num1'] = $banshi_num[1];
        $data['num2'] = $banshi_num[2];
        $data['num3'] = $banshi_num[3];

        $View->set(array('folder_prefix' => $this->folder_prefix));
        $View->display($data);
    }

    public function type() {
        $type_id = (string) $this->input->get('type');
        $_id = (string) $this->input->get('_id');
        $page = (int) $this->input->get('page');
        if ($page == 0) {
            $page = 1;
        }
        $parent_type = $this->service_type->find(array('_id' => $type_id, 'removed' => false, 'site_id' => $this->site_id), 1, 0, array('_id', 'name'));
        if (empty($parent_type)) {
            show_404("服务类型有误！");
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
        $View = new Blitz('template/service/list-service-zdly.html');
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
		if($parent_type['_id']=='5564180a65c66efdcf398c72'){
			$data['location'] = '<a href="/">网站首页</a> / <a href="/service/">网上办事</a> / <a href="/wsbs/type/?type=' . $parent_type['_id'] . '">' . $parent_type['name'] . '</a> / <span>' . $current_type['name'] . '</span>';
		}else{
			$data['location'] = '<a href="/">网站首页</a> / <a href="/service/">网上办事</a> / <a href="/wsbs/type/?type=5564180a65c66efdcf398c72">重点领域服务</a> / <a href="/wsbs/type/?type=' . $parent_type['_id'] . '">' . $parent_type['name'] . '</a> / <span>' . $current_type['name'] . '</span>';
		}
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
        $data['location'] = "<a href='/'>网站首页</a> > <a href='/service/'>在线办事</a> > <a href='/service/?type_id=$type_id'>" . $current_type['name'] . '</a> ';

        $View = new Blitz('template/detail.html');
        $struct_list = $View->getStruct();

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
        $type_id = (string) $this->input->get('type');
        $_id = (string) $this->input->get('_id');
        $child_id = (string) $this->input->get('child_id');
        $page = (int) $this->input->get('page');
        if ($page == 0) {
            $page = 1;
        }
        $parent_type = $this->service_type->find(array('_id' => $type_id, 'removed' => false, 'site_id' => $this->site_id), 1, 0, array('_id', 'name'));
        if (empty($_id)) {
            $current_type = $this->service_type->find(array('parent_id' => $type_id, 'removed' => false, 'site_id' => $this->site_id), 1, 0, array('_id', 'name', 'linkurl'), array('sort' => 'DESC'));
        } else {
            $current_type = $this->service_type->find(array('_id' => $_id, 'removed' => false, 'site_id' => $this->site_id), 1, 0, array('_id', 'name', 'linkurl'));
        }
        if (empty($child_id)) {
            $child_type = $current_type;
            $total_row = $this->service_content->count(array('type' => (string) $current_type['_id'], 'status' => true, 'removed' => false, 'site_id' => $this->site_id), null, 0, array('_id'));
        } else {
            $child_type = $this->service_type->find(array('_id' => $child_id, 'removed' => false, 'site_id' => $this->site_id), 1, 0, array('_id', 'name', 'linkurl'));
            $total_row = $this->service_content->count(array('type' => $child_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id), null, 0, array('_id'));
        }
        $View = new Blitz('template/service/list-service-zdly-content.html');
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
                    list($limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    if ($child_id) {
                        $item_list = $this->itemServiceContent($child_id, $limit, $offset, $length, $sort_by, $date_format);
                    } else {
                        $item_list = $this->itemServiceContent((string) $current_type['_id'], $limit, $offset, $length, $sort_by, $date_format);
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
        $data['service_name'] = $parent_type['name'];
        $data['service_title'] = $child_type['name'];
        $data['service_id'] = empty($_id) ? $current_type['_id'] : $_id;
        $data['type_id'] = $type_id;
        $data['location'] = '<a href="/">网站首页</a> / <a href="/service/">网上办事</a> / <a href="/wsbs/type/?type=' . $parent_type['_id'] . '">' . $parent_type['name'] . '</a> / <span>' . $current_type['name'] . '</span>';
        
        if ($child_id) {
            $data['location'].=' / <span>' . $child_type['name'] . '</span>';
        }
        $View->set(array('folder_prefix' => $this->folder_prefix, 'jstag' => 'service'));
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
        $View = new Blitz('template/service/detail-service.html');
        $this->service_content->update(array('_id' => $_id), array("views" => $content['views'] + 1));
        $content['views'] = (int) $content['views'] + 1;
        $content['date'] = ($content['confirm_date']) ? date('Y-m-d', $content['confirm_date']) : '';
        $content['content'] = htmlspecialchars_decode($content['content']);
        $download_list = '';
        if (!empty($content['download'])) {
            $download_list = '<br/><br/><font size=4 color="red">资料下载</font>:<br/><font size=3>';
            foreach ($content['download'] as $_id) {
                $item_list = $this->site_attach->find(array('_id' => (string) $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id), null, 0, array('_id', 'title'));
                foreach ($item_list as $item) {
                    $title = $item['title'];
                    $url = '/wsbs/downloadDetail/' . $item['_id'] . '.html';
                    $download_list.= '&nbsp;&nbsp;&nbsp;&nbsp;' . $title . "、";
                    $download_list.= "[<a href='" . $url . "' style='color:blue' target='_blank'>下载</a>]<br>";
                }
            }
            $download_list.='</font>';
        }
        $policy_list = '';
        if (!empty($content['policy']) && !empty($content['policy'][0])) {
            $policy_list = '<br/><br/><font size=4 color="red">政策法规:</font><br/><font size=3>';
            foreach ($content['policy'] as $_id) {
                $item_list = $this->service_policy->find(array('_id' => (string) $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id), null, 0, array('title', '_id'));
                foreach ($item_list as $item) {
                    $url = '/wsbs/policyDetail/' . $item['_id'] . '.html';
                    $title = $item['title'];
                    $policy_list .= "&nbsp;&nbsp;&nbsp;&nbsp;<a href='" . $url . "' target='_blank'>" . $title . "</a><br/>";
                }
            }
            $policy_list.='</font>';
        }
        $data = array(
            'download' => $download_list,
            'policy' => $policy_list,
            'content' => $content
        );
        $serviceTree = $this->serviceTree($content['type'][0]);
        $location = '<a href="/">网站首页</a> / <a href="/service/">网上办事</a>';
        if ($serviceTree['serviceTree']) {
            $array = array_reverse($serviceTree['serviceTree'], TRUE);
            foreach ($array as $item) {
                $location.=' / <span>' . $item['name'] . '</span>';
            }
        }
        $data['location'] = $location . ' / ' . $content['title'];
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

        $subdir = substr($attachment['saved_name'], 0, 8);
        $full_file = $this->upload_url . $subdir . '/' . $attachment['saved_name'];

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

    public function service() {
        $type_id = (string) $this->input->get('type');
        $_id = (string) $this->input->get('_id');
        $page = (int) $this->input->get('page');
        if ($page == 0) {
            $page = 1;
        }
        $parent_type = $this->service_type->find(array('_id' => $type_id, 'removed' => false, 'site_id' => $this->site_id), 1, 0, array('_id', 'name'));
        if (empty($parent_type)) {
            show_404("服务类型有误！");
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
        $View = new Blitz('template/wsbs/service.html');
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';
                if ($action == 'service') {
                    //服务类型
                    list($type, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($type == 'current') {
                        $type = $type_id;
                    }
                    $item_list = $this->serviceList($type, $limit, $offset, $length);
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
                        if ($item['is_table']) {
                            $item_list[$key]['content'] = '<a href="/wsbs/guideDetail/' . $item['_id'] . '.html" target="_blank">办事指南</a>';
                            $item_list[$key]['url'] = '/wsbs/guideDetail/' . $item['_id'] . '.html"';
                        } else {
                            $item_list[$key]['content'] = '<a href="/wsbs/contentDetail/' . $item['_id'] . '.html" target="_blank">办事指南</a>';
                        }
                        //$item_list[$key]['supervision'] = '<a href="/supervision/branch/?_id=' . $item['branch_id'] . '" target="_blank">咨询投诉</a>';
                        if (!empty($item['old_ZXTS'])) {
                            $item_list[$key]['supervision'] = '<a href="' . $item['old_ZXTS'] . '" target="_blank">咨询投诉</a>';
                        }
                        if (!empty($item['download'])) {
                            $item_list[$key]['download'] = '<a href="/wsbs/contentDetail/' . $item['_id'] . '.html#download" target="_blank">表格下载</a>';
                        } else {
                            $item_list[$key]['download'] = '';
                        }
                        if (!empty($item['policy'])) {
                            $item_list[$key]['policy'] = '<a href="/wsbs/contentDetail/' . $item['_id'] . '.html#policy" target="_blank">政策法规</a>';
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
        $banshi_num = explode(chr(10), file_get_contents('./data/banshi_num.dat'));
        $data['num0'] = $banshi_num[0];
        $data['num1'] = $banshi_num[1];
        $data['num2'] = $banshi_num[2];
        $data['num3'] = $banshi_num[3];
        $data['service_name'] = $parent_type['name'];
        $data['service_id'] = empty($_id) ? $current_type['_id'] : $_id;
        $data['location'] = '<a href="/">网站首页</a> / <a href="/wsbs/">网上办事</a> / <a href="/wsbs/service/?type=' . $parent_type['_id'] . '"/' . $parent_type['name'] . '</a> / <span>' . $current_type['name'] . '</span>';
        $View->set(array('folder_prefix' => $this->folder_prefix, 'jstag' => 'service'));
        $View->display($data);
    }

    public function branch() {
        $branch_id = (string) $this->input->get('branch_id');
        if (empty($branch_id)) {
            show_404("部门ID有误！");
        }
        $page = (int) $this->input->get('page');
        if ($page == 0) {
            $page = 1;
        }
        $this->load->model("site_branch_model", "site_branch");
        $branch = $this->site_branch->find(array("_id" => $branch_id), 1);
        if (empty($branch)) {
            show_404("部门ID有误！");
        }
        $total_row = $this->service_content->count(array('branch_id' => $branch_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id), null);
        $View = new Blitz('template/wsbs/service-branch.html');
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';
                if ($action == 'service') {
                    //服务类型
                    list($type, $limit, $offset, $length) = explode('_', $matches[2]);
                    $item_list = $this->serviceList($type, $limit, $offset, $length);
                } elseif ($action == 'content') {
                    // 办事指南
                    list($parent_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->serviceBranchContent($branch_id, $limit, $offset, $length, $sort_by, $date_format);
                    foreach ($item_list as $key => $item) {
                        if ($item['is_table']) {
                            $item_list[$key]['content'] = '<a href="/wsbs/guideDetail/' . $item['_id'] . '.html" target="_blank">办事指南</a>';
                            $item_list[$key]['url'] = '/wsbs/guideDetail/' . $item['_id'] . '.html"';
                        } else {
                            $item_list[$key]['content'] = '<a href="/wsbs/contentDetail/' . $item['_id'] . '.html" target="_blank">办事指南</a>';
                        }
                        //$item_list[$key]['supervision'] = '<a href="/supervision/branch/?_id=' . $item['branch_id'] . '" target="_blank">咨询投诉</a>';
                        if (!empty($item['old_ZXTS'])) {
                            $item_list[$key]['supervision'] = '<a href="' . $item['old_ZXTS'] . '" target="_blank">咨询投诉</a>';
                        }
                        if (!empty($item['download'])) {
                            $item_list[$key]['download'] = '<a href="/wsbs/contentDetail/' . $item['_id'] . '.html#download" target="_blank">表格下载</a>';
                        } else {
                            $item_list[$key]['download'] = '';
                        }
                        if (!empty($item['policy'])) {
                            $item_list[$key]['policy'] = '<a href="/wsbs/contentDetail/' . $item['_id'] . '.html#policy" target="_blank">政策法规</a>';
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
                } else if ($action == "servicebranch") {
                    //办事部门
                    list($parent_id, $limit, $offset, $length) = explode('_', $matches[2]);
                    if ($parent_id == "all") {
                        $parent_id = NULL;
                    }
                    $item_list = $this->branchList($parent_id, $limit, $offset, $length);
                }
                $data[$struct_val] = $item_list;
            }
        }
        $banshi_num = explode(chr(10), file_get_contents('./data/banshi_num.dat'));
        $data['num0'] = $banshi_num[0];
        $data['num1'] = $banshi_num[1];
        $data['num2'] = $banshi_num[2];
        $data['num3'] = $banshi_num[3];
        $data['branch'] = $branch['name'];
        $data['branch_id'] = $branch_id;
        $data['location'] = '<a href="/">网站首页</a> > <a href="/wsbs/">网上办事</a> > <a href="/wsbs/branch/?branch_id=' . $branch_id . '">' . $branch['name'] . '</a>';
        $View->display($data);
    }

    protected function searchServiceContent($titlekey, $limit = 50, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {
        $this->load->model('service_content_model', 'service_content');
        $filter = array('status' => true, 'removed' => false, 'site_id' => $this->site_id);
        $select = array('_id', 'title', 'branch_id', 'confirm_date', 'download', 'policy', 'link_url', 'old_ZXTS', 'is_table');
        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $item_list = $this->service_content->findList(NULL, $titlekey, $filter, $limit, $offset, $select, $arr_sort);
        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            $item_list[$key]['url'] = $item['link_url'] ? $item['link_url'] : '/wsbs/contentDetail/' . $item['_id'] . '.html';
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
            $item_list[$key]['short_title'] = str_replace($titlekey, '<font color="red">' . $titlekey . '</font>', $item_list[$key]['short_title']);
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

    //搜索关键词
    public function search() {
        $titlekey = $this->input->get("key");
        if (empty($titlekey)) {
            $titlekey = NULL;
        }
        $page = (int) $this->input->get('page');
        if ($page == 0) {
            $page = 1;
        }
        $total_row = $this->service_content->listCount($titlekey, array('status' => true, 'removed' => false, 'site_id' => $this->site_id));
        $View = new Blitz('template/wsbs/service-search.html');
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';
                if ($action == 'service') {
                    //服务类型
                    list($type, $limit, $offset, $length) = explode('_', $matches[2]);
                    $item_list = $this->serviceList($type, $limit, $offset, $length);
                } elseif ($action == 'content') {
                    // 办事指南
                    list($parent_id, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    if ($parent_id == 'current') {
                        if ($offset == 'page') {
                            $offset = $limit * ($page - 1);
                        }
                        $item_list = $this->searchServiceContent($titlekey, $limit, $offset, $length, $sort_by, $date_format);
                    } else {
                        $type = $parent_id;
                        if ($offset == 'page') {
                            $offset = $limit * ($page - 1);
                        }
                        $item_list = $this->itemServiceContent($type, $limit, $offset, $length, $sort_by, $date_format);
                    }
                    foreach ($item_list as $key => $item) {
                        if ($item['is_table']) {
                            $item_list[$key]['content'] = '<a href="/wsbs/guideDetail/' . $item['_id'] . '.html" target="_blank">办事指南</a>';
                            $item_list[$key]['url'] = '/wsbs/guideDetail/' . $item['_id'] . '.html"';
                        } else {
                            $item_list[$key]['content'] = '<a href="/wsbs/contentDetail/' . $item['_id'] . '.html" target="_blank">办事指南</a>';
                        }
                        //$item_list[$key]['supervision'] = '<a href="/supervision/branch/?_id=' . $item['branch_id'] . '" target="_blank">咨询投诉</a>';
                        if (!empty($item['old_ZXTS'])) {
                            $item_list[$key]['supervision'] = '<a href="' . $item['old_ZXTS'] . '" target="_blank">咨询投诉</a>';
                        }
                        if (!empty($item['download'])) {
                            $item_list[$key]['download'] = '<a href="/wsbs/contentDetail/' . $item['_id'] . '.html#download" target="_blank">表格下载</a>';
                        } else {
                            $item_list[$key]['download'] = '';
                        }
                        if (!empty($item['policy'])) {
                            $item_list[$key]['policy'] = '<a href="/wsbs/contentDetail/' . $item['_id'] . '.html#policy" target="_blank">政策法规</a>';
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
        $banshi_num = explode(chr(10), file_get_contents('./data/banshi_num.dat'));
        $data['num0'] = $banshi_num[0];
        $data['num1'] = $banshi_num[1];
        $data['num2'] = $banshi_num[2];
        $data['num3'] = $banshi_num[3];
        $data['location'] = '<a href="/">网站首页</a> > <a href="/wsbs/">网上办事</a> > 办件搜索 ' . $titlekey;
        $View->display($data);
    }

    //办事指南自定义表格
    public function guideDetail() {
        $_id = (string) $this->input->get('_id');
        $content = $this->service_content->find(array('_id' => $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id));
        if ($content['link_url']) {
            header("Location: " . $content['link_url']);
        }
        if (empty($content)) {
            show_404();
        }
        $View = new Blitz('template/wsbs/detail-guide.html');
        $this->service_content->update(array('_id' => $_id), array("views" => $content['views'] + 1));
        $content['views'] = (int) $content['views'] + 1;
        $content['date'] = ($content['confirm_date']) ? date('Y-m-d', $content['confirm_date']) : '';
        $content['content'] = htmlspecialchars_decode($content['content']);
        $table_data = array();
        foreach ($content['table_data'] as $item) {
            $table_data[$item['id']]['name'] = $item['name'];
            $table_data[$item['id']]['value'] = $item['value'];
        }
        $data['table_data'] = $table_data;
        $download_list = '';
        if (!empty($content['download'])) {
            $download_list = '';
            foreach ($content['download'] as $_id) {
                $item_list = $this->service_download->find(array('_id' => (string) $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id), null, 0, array('_id', 'title'));
                foreach ($item_list as $item) {
                    $url = '/wsbs/downloadDetail/' . $item['_id'] . '.html';
                    $download_list.='<li><a href="' . $url . '" target="_blank" title="' . $item['title'] . '">' . $item['title'] . '</a></li>';
                }
            }
        }
        $policy_list = '';
        if (!empty($content['policy']) && !empty($content['policy'][0])) {
            $policy_list = '';
            foreach ($content['policy'] as $_id) {
                $item_list = $this->service_policy->find(array('_id' => (string) $_id, 'status' => true, 'removed' => false, 'site_id' => $this->site_id), null, 0, array('title', '_id'));
                foreach ($item_list as $item) {
                    $url = '/wsbs/policyDetail/' . $item['_id'] . '.html';
                    $policy_list.='<li><a href="' . $url . '" target="_blank" title="' . $item['title'] . '">' . $item['title'] . '</a></li>';
                }
            }
        }
        $data['download'] = $download_list;
        $data['policy'] = $policy_list;
        $data['content'] = $content;
        $serviceTree = $this->serviceTree($content['type'][0]);
        $location = '<a href="/">网站首页</a> > <a href="/wsbs/">网上办事</a>';
        if ($serviceTree['serviceTree']) {
            $array = array_reverse($serviceTree['serviceTree'], TRUE);
            foreach ($array as $item) {
                $location.=' > <span>' . $item['name'] . '</span>';
            }
        }
        $data['location'] = $location . ' > ' . $content['title'];
        $View->display($data);
    }

    //获取办事服务树关系返回深度和栏目信息
    protected function serviceTree($_id = NULL, $tree = -1, $serviceTree = array()) {
        $tree++;
        if (empty($_id)) {
            return array("tree" => $tree, "serviceTree" => $serviceTree);
        }
        $service = $this->service_type->find(array('_id' => $_id), 1, 0, array('name', 'parent_id'));

        if (empty($service)) {
            return array("tree" => $tree, "serviceTree" => $serviceTree);
        }
        if ($service['parent_id'] == "/") {
            return array("tree" => $tree, "serviceTree" => $serviceTree);
        }
        $serviceTree[] = array("_id" => (string) $service['_id'], "name" => $service['name']);
        return $this->serviceTree($service['parent_id'], $tree, $serviceTree);
    }

}

/* End of file wsbs.php */
/* Location: ./application/controllers/wsbs.php */