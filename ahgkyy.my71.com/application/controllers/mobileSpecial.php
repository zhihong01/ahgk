<?php

class mobileSpecial extends MY_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model('special_model', 'special');
        $this->load->model('special_content_model', 'special_content');
        $this->load->model('special_column_model', 'special_column');
        $this->load->model('site_channel_tree_model', 'site_channel_tree');
    }

    // 专题栏目
    protected function specialList($_id_list, $limit = 10, $offset = 0, $length = 60, $sort_by = 0, $date_format = 0) {

        $arr_sort = array('sort' => 'DESC');
        $date_format = $this->date_foramt[$date_format];

        $select = array('_id', 'title', 'link_url', 'create_date','content','cover');

        $item_list = $this->special->find(array('status' => true, 'removed' => false, 'site_id' => $this->site_id), $limit, $offset, $select, $arr_sort);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);
            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }
			$item['content'] = str_replace(Chr(32), " ", $item['content']);
            if (mb_strlen($item['content']) > 40) {
                $item_list[$key]['content'] = mb_substr($item['content'], 0, 40) . '...';
            }
            $item_list[$key]['date'] = ($item['create_date']) ? date($date_format, $item['create_date']) : '';
            $item_list[$key]['url'] = $item['link_url'] ? $item['link_url'] : '/special/channel/?_id=' . $item['_id'];
        }
        return $item_list;
    }

    // 取具体专题
    protected function specialContentList($special_id, $column_id, $limit = 10, $offset = 0, $length = 60, $date_format = 0) {


        $date_format = $this->date_foramt[$date_format];

        $select = array('_id', 'title', 'link_url', 'thumb', 'release_date');

        if (!empty($column_id)) {
            $item_list = $this->special_content->find(array('special_id' => $special_id, 'column_id' => $column_id, 'status' => True, 'removed' => false), $limit, $offset, $select);
        } else {
            $item_list = $this->special_content->find(array('special_id' => $special_id, 'status' => True, 'removed' => false), $limit, $offset, $select);
        }


        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);

            if (mb_strlen($item['title']) > $length) {
                $item_list[$key]['short_title'] = mb_substr($item['title'], 0, $length) . '...';
            } else {
                $item_list[$key]['short_title'] = $item['title'];
            }

            $item_list[$key]['url'] = !empty($item['link_url']) ? $item['link_url'] : '/special/detail/' . $item['_id'] . '.html';
            $item_list[$key]['_blank'] = $item['link_url'] ? 'target="_blank"' : '';
            $item_list[$key]['date'] = ($item['release_date']) ? date($date_format, $item['release_date']) : '';
        }
        return $item_list;
    }

    // 取专题栏目
    protected function specialColumnList($_id_list, $limit = 10, $offset = 0, $length = 60) {

        $select = array('_id', 'name');

        $item_list = $this->special_column->find(array('special_id' => $_id_list, 'removed' => false, 'site_id' => $this->site_id), $limit, $offset, $select);

        foreach ($item_list as $key => $item) {
            $item_list[$key]['_id'] = (string) ($item['_id']);

            if (mb_strlen($item['name']) > $length) {
                $item_list[$key]['name'] = mb_substr($item['name'], 0, $length) . '...';
            } else {
                $item_list[$key]['name'] = $item['name'];
            }

            $item_list[$key]['url'] = '/special/channel/?column_id=' . $item['_id'];
        }

        return $item_list;
    }

    protected function getMenu($channel_id, $limit = 50, $offset = 0, $length = 60) {
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

    public function index() {
        $page = (string) $this->input->get('page');
        $total_row = count($this->special->find(array('status' => True, 'removed' => false, 'site_id' => $this->site_id), 100, 0, array('_id')));

        $View = new Blitz('template/mobile/list-nodate.html');
        $struct_list = $View->getStruct();
		$data=array();
        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                if ($action == 'list') {
                    list($channel_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);
                    $_id_list = explode('-', $channel_id);
                    if ($offset == 'page') {
                        $offset = $limit * ($page - 1);
                    }
                    $item_list = $this->specialList($_id_list, $limit, $offset, $length, $date_format);
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

        $data['channel_name'] = '专题报道';
        $data['location'] = '<a href="/">网站首页</a> / <a href="/mobileSpecial/">专题报道</a> ';

        $View->display($data);
    }

    public function channel() {

        $column_id = $this->input->get('column_id') ? (string) $this->input->get('column_id') : null;

        if (!empty($column_id)) {
            $find_special = $this->special_column->find(array('_id' => $column_id), 1, 0, array('special_id'));
            $special_id = (string) $find_special['special_id'];
        } else {
            $special_id = (string) $this->input->get('_id');
        }

        $special = $this->special->find(array('_id' => $special_id), 1, 0, array('template_id', 'title'));

        switch ($special['template_id']) {
            case 'simple':
                header("Location: /specialSimple/?_id=$special_id");
                break;
            case 'red':
                header("Location: /specialRed/?_id=$special_id");
                break;
            case 'blue':
                header("Location: /specialBlue/?_id=$special_id");
                break;
            case '30':
                header("Location: /specialThirty/?_id=$special_id");
                break;
            default:
                break;
        }
        $page = (string) $this->input->get('page');

        if (!empty($column_id)) {
            $total_row = count($this->special_content->find(array('special_id' => $special_id, 'column_id' => $column_id, 'status' => True, 'removed' => false), 100, 0, array('_id')));
        } else {
            $total_row = count($this->special_content->find(array('special_id' => $special_id, 'status' => True, 'removed' => false), 100, 0, array('_id')));
        }


        $View = new Blitz('template/list.html');
        $struct_list = $View->getStruct();

        foreach ($struct_list as $struct) {
            $matches = array();
            if (preg_match('@^/([a-z]+)-([\w\-]+)/$@', $struct, $matches)) {
                $action = $matches[1];
                $struct_val = trim($matches[0], '/');
                $item_list = '';

                if ($action == 'list') {
                    list($channel_id, $limit, $offset, $length, $date_format) = explode('_', $matches[2]);


                    $item_list = $this->specialContentList($special_id, $column_id, $limit, $offset, $length, $date_format);
                } elseif ($action == 'page') {
                    $per_count = (int) $matches[2];
                    if ($per_count == 0) {
                        $per_count = 20;
                    }
                    $link = $this->getPagination($total_row, $page, $per_count,0);
                    $item_list['page'] = $link;
                } elseif ($action == 'menu') {
                    list($parent_id, $limit, $offset, $length) = explode('_', $matches[2]);

                    if ($parent_id != 'current') {
                        $channel_id = $parent_id;
                        $menu_list = $this->getMenu($channel_id, $limit, $offset, $length);
                        $i = 0;
                        foreach ($menu_list as $key => $menu) {
                            $item_list[$i]['_id'] = $key;
                            $item_list[$i]['url'] = $this->folder_prefix . '/channel/' . $key . '/';
                            $item_list[$i]['name'] = $menu;
                            $i++;
                        }
                    } else {
                        $item_list = $this->specialColumnList($special_id, $limit, $offset, $length);
                    }
                }
                $data[$struct_val] = $item_list;
            }
        }

        $data['channel_name'] = '专题报道';
        $data['menu_name'] = $special['title'];
        $data['menu_id'] = $column_id;

        $data['location'] = "<a href='/'>网站首页</a> / <a href='/special/'>专题报道</a> / <a href='/special/channel/?_id=$special_id'>" . $special['title'] . '</a> ';


        $View->display($data);
    }

    public function detail() {

        $service_id = (string) $this->input->get('_id');

        $View = new Blitz('template/detail.html');

        $content = $this->special_content->find(array('_id' => $service_id), 1, 0, array('title', 'body', 'release_date', 'author', 'copy_from', 'target_url'));

        if (!empty($content['target_url'])) {
            header("Location: " . $content['target_url']);
        }

        $content['release_date'] = ($content['release_date']) ? date('Y-m-d H:i', $content['release_date']) : '';
        if (!empty($content['author'])) {
            $content['author'] = '作者： ' . $content['author'];
        }
        if (!empty($content['copy_from'])) {
            $content['copy_from'] = '信息来源： ' . $content['copy_from'];
        }
		$content['body'] = htmlspecialchars_decode($content['body']);
        $content['title_br'] = str_replace("\n", '<br/>', $content['title']);

		$content['table_name']='special_content'; 
        $data = array(
            'content' => $content,
        );
        $data['location'] = '<a href="/">网站首页</a> / <a href="/special/">专题报道</a> ';

        $View->display($data);
    }

}

?>