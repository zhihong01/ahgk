<?php
class zcjd extends MY_Controller {

    public function __construct() {
        parent::__construct();
		$this->load->model('site_channel_model', 'site_channel');
        $this->load->model('site_channel_tree_model', 'site_channel_tree');
        $this->load->model('content_model', 'content');
    }
	
	protected function contentList($_id_list, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $description_length = 0, $is_pic = false) {

        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $select = array('_id', 'title', 'description', 'thumb_name', 'release_date', 'thumb_large', 'link_url', 'type', 'title_color','source_table');
        if ($is_pic) {
            $filter = array('status' => true, 'thumb_name' => array("\$nin" => array('',null)), 'removed' => false, 'site_id' => $this->site_id);
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


			$item['description'] = strip_tags($item['description']);
            if (mb_strlen($item['description']) > $description_length) {
                $item_list[$key]['description'] = mb_substr($item['description'], 0, $description_length) . '...';
            }

            $item_list[$key]['url'] = !empty($item['link_url']) ? $item['link_url'] : $this->folder_prefix . '/detail/' . $item['_id'] . '.html';
			
			
			if(strstr($item['thumb_name'],'/jcms/')){
				$item_list[$key]['thumb'] =  $item['type'] == 1 ? 'http://old.huaibei.gov.cn/'.$item['thumb_name']:$item['thumb_large'];
			}else{
				$item_list[$key]['thumb'] = $item['type'] == 1 ? $item['thumb_name'] : $item['thumb_large'];
			}
           
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }

        return $item_list;
    }
	
	protected function opennessList($branch_id, $where_array, $limit = 10, $offset = 0, $length = 60, $date_format = 0, $code = null) {
        $this->load->model('openness_content_model', 'openness_content');
        $this->load->model('site_branch_model', 'site_branch');
        $this->load->model('openness_column_model', 'openness_column');

        $arr_sort = array('sort' => 'DESC', 'openness_date' => 'DESC', 'create_date' => 'DESC');
        $date_format = $this->date_foramt[$date_format];
        $where_array['status'] = True;
        $where_array['removed'] = False;

        $select = array('_id', 'title', 'serial_number', 'create_date', 'tag', 'document_number', 'branch_id', 'column_id', 'column_code', 'openness_date', 'id');

        $item_list = $this->openness_content->findList($branch_id, $where_array, $limit, $offset, $select, $arr_sort, $code);


        foreach ($item_list as $key => $item) {
            if ($item['branch_id']) {
                $this_branch = $this->site_branch->find(array('_id' => $item['branch_id']));
                $item_list[$key]['branch'] = $this_branch['name'];
            }
            if ($item['column_code'] && $item['branch_id']) {
                $this_column = $this->openness_column->find(array('code' => (string) $item['column_code'], 'branch_id' => $item['branch_id']));
                $item_list[$key]['column'] = $this_column['name'];
            }
            $item_list[$key]['_id'] = (string) ($item['_id']);
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['title'] = mb_substr($item['title'], 0, $length) . '...';
            }

            $item_list[$key]['date'] = $item['openness_date'];
            $item_list[$key]['url'] = "http://hbxxgk.huaibei.gov.cn/openness/detail/content/" . $item['_id'] . '.html';
        }

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
	
    public function index() {
        $View = new Blitz('template/zcjd.html');
		$struct_list = $View->getStruct();
		$data = array();
        foreach ($struct_list as $struct) {
            $matches = array();
			if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';
				if ($action == 'list') {
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
				}elseif ($action == 'slider') {
                    list($channel_id, $limit, $offset, $length, $date_format, $description_length) = explode('_', $matches[2]);
                    $this_channel = $this->site_channel_tree->find(array('_id' => $channel_id));
                    if (!empty($this_channel['child'])) {
                        unset($_id_list);
                        foreach ($this_channel['child'] as $key => $val)
                            $_id_list[] = $key;
                    } else {
                        $_id_list = explode('-', $channel_id);
                    }
                    $item_list = $this->contentList($_id_list, $limit, $offset, $length, $date_format, $description_length, true);
					
                }elseif ($action == 'openness') {
                    list($branch_id, $column_code, $limit, $offset, $length, $sort_by, $date_format) = explode('_', $matches[2]);
                    $where_array = null;
                    if (strlen($branch_id) == 1) {
                        $where_array = array('branch_type' => (int) $branch_id);
                        $branch_id = null;
                    } elseif ($branch_id == 'all') {
                        $branch_id = null;
                    } elseif ($column_code == 'all') {
                        $column_code = null;
                    }
                    $item_list = $this->opennessList($branch_id, $where_array, $limit, $offset, $length, $date_format, (int) $column_code);
                }elseif ($action == 'friendlink') {
                    list($channel_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);

                    $item_list = $this->friendLinkList($channel_id, $limit, $offset, $length, $date_format);
                } 
				$data[$struct_val] = $item_list;
			}
			
        }
		$data['sheng_jiedu'] = file_get_contents('./data/zcjd.dat');
        $View->display($data);
    }
}
