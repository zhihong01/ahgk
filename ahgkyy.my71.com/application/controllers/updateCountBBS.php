<?php

class updateCountBBS extends MY_Controller {

    private $group_id = '5223fdf4682e091f201bda94';
    protected $site_id = '';

    public function __construct() {
        parent::__construct();
        set_time_limit(0);
        
        $this->site_id = $this->config->item('default_site_id');
        $this->vals['setting'] = $this->getSetting( $this->site_id );
        $this->vals['setting'] = array_merge($this->vals['setting'], $this->getMyExtSetting('forum_setting', $this->site_id));
        
    }

    public function index() {

        $this->load->model('forum_group_model', 'forum_group');
        $this->load->model('forum_thread_model', 'forum_thread');
        $this->load->model('forum_post_model', 'forum_post');
        $this->load->model('site_account_model', 'site_account');
        $this->load->model('site_branch_model', 'site_branch');
        $this->load->model('forum_branch_counter_model', 'forum_branch_counter');
        $this->load->model('site_login_session_model', 'login_session');
        $this->load->model('forum_counter_model', 'forum_counter');

        $group_list = $this->forum_group->find(array('site_id' => $this->site_id, 'status' => TRUE), null, 0, array('_id', 'name'));
        $create_date = (int) date('Ymd');

        $filter_list = array('site_id' => $this->site_id);

        $this->load->model('forum_visit_log_model', 'forum_visit_log');
        $total_visit = $this->forum_visit_log->count($filter_list);

        //统计共多少个会员
        $filter_list['type'] = 2;
        $this->load->model('site_account_model', 'site_account');
        $member_count = $this->site_account->count($filter_list);

        //$filter_list['last_time'] = array('\$gt' => $online_time_min);
        $total_online_logged = $this->login_session->getOnline($this->site_id, TRUE, 20);
        $total_online_visitor = $this->login_session->getOnline($this->site_id, FALSE, 20);
        $total_online = $total_online_logged + $total_online_visitor;

        $mobile_info = array();
        foreach ($group_list as $group) {

            $data = array();
            $data['group_id'] = (string) $group['_id'];

            $filter_list = array('group_id' => $data['group_id'], 'status' => true, 'removed' => false, 'closed' => false);

            //获取 状态的信息： 今日:  | 昨日:  | 帖子:  | 会员:
            $data['total_thread'] = $this->forum_thread->count($filter_list);
            $data['total_post'] = $this->forum_post->count($filter_list);

            $one_date = date('Y-m-d');
            $two_date = date('Y-m-d', strtotime('-1 day'));

            $data['today_thread'] = $this->forum_thread->listCount(NULL, $filter_list, $one_date);
            $data['yesterday_thread'] = $this->forum_thread->listCount(NULL, $filter_list, $two_date, $one_date);

            $data['today_post'] = $this->forum_post->listCount(NULL, $filter_list, $one_date);
            $data['yesterday_post'] = $this->forum_post->listCount(NULL, $filter_list, $two_date, $one_date);

            $data['member_count'] = $member_count;
            $data['total_visit'] = $total_visit;

            $data['total_online_logged'] = $total_online_logged;
            $data['total_online_visitor'] = $total_online_visitor;
            $data['total_online'] = $total_online;


            $this->forum_counter->update(array('site_id' => $this->site_id, 'create_date' => $create_date, 'group_id' => $data['group_id']), $data, array('upsert' => TRUE));
            $this->forum_group->update(array('_id' => $data['group_id']), array('total_thread' => $data['total_thread'], 'today_thread' => $data['today_thread']));

            $mobile_info[] = array(
                'name' => $group['name'],
                'id' => $data['group_id'],
                'thread' => $data['total_thread'],
            );
        }


        //更新 总统计
        $filter_list = array('site_id' => $this->site_id, 'status' => true, 'removed' => false, 'closed' => false);
        $data = array();

        $data['group_id'] = '/';

        //获取 状态的信息： 今日:  | 昨日:  | 帖子:  | 会员:
        $data['total_thread'] = $this->forum_thread->count($filter_list);
        $data['total_post'] = $this->forum_post->count($filter_list);

        $one_date = date('Y-m-d');
        $two_date = date('Y-m-d', strtotime('-1 day'));

        $data['today_thread'] = $this->forum_thread->listCount(NULL, $filter_list, $one_date);
        $data['yesterday_thread'] = $this->forum_thread->listCount(NULL, $filter_list, $two_date, $one_date);

        $data['today_post'] = $this->forum_post->listCount(NULL, $filter_list, $one_date);
        $data['yesterday_post'] = $this->forum_post->listCount(NULL, $filter_list, $two_date, $one_date);

        $data['member_count'] = $member_count;
        $data['total_visit'] = $total_visit;

        $data['total_online_logged'] = $total_online_logged;
        $data['total_online_visitor'] = $total_online_visitor;
        $data['total_online'] = $total_online;

        $this->load->model('forum_counter_model', 'forum_counter');
        $this->forum_counter->update(array('site_id' => $this->site_id, 'create_date' => $create_date, 'group_id' => $data['group_id']), $data, array('upsert' => TRUE));

        //更新 总统计
        $filter_list = array('site_id' => $this->site_id);
        $this->load->model('forum_setting_model', 'forum_setting');

        $total_thread = $this->forum_thread->count($filter_list);
        $total_post = $this->forum_post->count($filter_list);
        $this->forum_setting->update($filter_list, array('total_thread' => $total_thread, 'total_post' => $total_post));

        $this->login_session->clear(false, 120);

        $this->resultJson('更新成功！', '+OK');
    }

    public function branch() {
        $group_id = $this->group_id;
        $create_date = (int) $this->input->post('date');
        $branch_id = (string) $this->input->post('branch_id');

        if ($create_date < 20131010) {
            $create_date = date('Ymd');
        }

        $create_date_int = (int) date('Ymd');

        $filter = array('forum_on' => true, 'removed' => false);

        if (strlen($branch_id) == 24) {
            $filter['_id'] = $branch_id;
        }

        $this->load->model('forum_thread_model', 'forum_thread');
        $this->load->model('site_branch_model', 'site_branch');
        $this->load->model('forum_branch_counter_model', 'forum_branch_counter');

        //site branch
        $branch_list = $this->site_branch->find($filter, NULL, 0, array('_id', 'parent_id', 'name', 'site_id', 'sort'));

        foreach ($branch_list as $branch) {
            $_id = (string) $branch['_id'];

            $total = $this->forum_thread->count(array('branch.id' => $_id, 'group_id' => $group_id, 'status' => true, 'removed' => false, 'closed' => false));
            $expired = $this->forum_thread->countExpired(array('branch.id' => $_id, 'group_id' => $group_id, 'status' => true, 'removed' => false, 'closed' => false), $create_date);
            $processed = $this->forum_thread->countProcessed(array('branch.id' => $_id, 'group_id' => $group_id, 'status' => true, 'removed' => false, 'closed' => false));

            $today_thread = $this->forum_thread->count(array('branch.id' => $_id, 'group_id' => $group_id, 'status' => true, 'removed' => false, 'closed' => false), $create_date, $create_date);
            $today_processed = $this->forum_thread->countProcessed(array('branch.id' => $_id, 'group_id' => $group_id, 'status' => true, 'removed' => false, 'closed' => false), $create_date, $create_date);
            $today_expired = $this->forum_thread->countExpired(array('branch.id' => $_id, 'group_id' => $group_id, 'status' => true, 'removed' => false, 'closed' => false), $create_date, $create_date);

            $data = array(
                'branch_id' => $_id,
                'create_date' => $create_date_int,
                'branch_name' => $branch['name'],
                'parent_id' => $branch['parent_id'],
                'today_thread' => $today_thread,
                'today_processed' => $today_processed,
                'today_expired' => $today_expired,
                'total' => $total,
                'processed' => $processed,
                'expired' => $expired,
                'site_id' => $branch['site_id'],
                'sort' => $branch['sort'],
            );

            $this->forum_branch_counter->update(array('create_date' => $create_date_int, 'branch_id' => $_id), $data, array('upsert' => TRUE));
        }


        $this->resultJson('更新成功！', '+OK');
    }

    //设置红黄牌
    public function priority() {
        $filter = array('group_id' => $this->group_id, 'status' => TRUE, 'removed' => false, 'closed' => false);

        $this->load->model('forum_thread_model', 'forum_thread');
        $now = time();
        
        //黄牌
        $yellow_result_day = $this->exceptHoliday($now, $this->vals['setting']['yellow_card_day']);
        $yellow_end_date = $yellow_result_day[0];
        $yellow_begin_day = $yellow_end_date - $this->vals['setting']['yellow_card_day'] * 24 * 3600;
        $yellow_car = $this->forum_thread->updatePriority($filter, array(1, 2), 2, $yellow_begin_day, $yellow_end_date);

        //红牌
        $red_result_day = $this->exceptHoliday($now, $this->vals['setting']['red_card_day']);
        $red_end_date = $red_result_day[0];
        $red_begin_day = $red_end_date - 60 * 24 * 3600;  //60天

        $red_car = $this->forum_thread->updatePriority($filter, array(1, 2), 3, $red_begin_day, $red_end_date);

        $this->resultJson('红黄牌设置成功！', '+OK', array('yellow_car' => $yellow_car, 'red_car' => $red_car));
    }

    //自动关闭
    public function close() {
        $filter = array('group_id' => $this->group_id, 'process_status' => 3);

        $this->load->model('forum_thread_model', 'forum_thread');

        $result_day = $this->exceptHoliday(time(), $this->vals['setting']['auto_close']);
        $close_day = $result_day[0];

        $this->forum_thread->autoClose($filter, $close_day);

        $this->resultJson('自动关闭设置成功！', '+OK');
    }

    //发送督办短信
    public function urge() {
	
	
	
        if (!$this->vals['setting']['sms_on']) {
            $this->resultJson('短信通知功能已经关闭！');
        }
		

        // 百姓热线，
        $filter = array('group_id' => $this->group_id, 'status' => true, 'removed' => false, 'closed' => false, 'process_status' => 2);
        $this->load->model('forum_thread_model', 'forum_thread');
        $this->load->model('site_branch_model', 'site_branch');

        $this->load->model('sms_template_model', 'sms_template');
        $template = $this->sms_template->find(array('site_id' => $this->site_id, 'key_word' => "need_reply"), 1);

        if (empty($template)) {
            return FALSE;
        }

        $branch_list = array();
        $result = $this->site_branch->find(array('site_id' => $this->site_id), NULL, 0, array('_id', 'mobile', 'name'));
		
		
        foreach ($result as $value) {
            if (!empty($value['mobile']) && $this->valid_mobile_number($value['mobile'])) {
                $branch_list[(string) $value['_id']] = $value;
            }
        }

        //发送黄牌
        $filter['priority'] = 2;
        $thread_list = $this->forum_thread->find($filter, 20, 0, array('_id', 'title', 'branch'), array('create_date' => 'DESC'));
        $yellow_car = count($thread_list);
        foreach ($thread_list as $thread) {
            if (!isset($branch_list[$thread['branch']['id']])) {
                continue;
            }
            $branch = $branch_list[$thread['branch']['id']];
            $content = str_replace(array("%MEMBERNAME%", "%TITLE%"), array($branch['name'], $thread['title']), $template["content"]);
            $this->sendSms($branch['mobile'], $content, $this->site_id, '', false, $this->vals['setting']['notice_sms_hour']);
        }
        //发送红牌
        $filter['priority'] = 3;
        $thread_list = $this->forum_thread->find($filter, 20, 0, array('_id', 'title', 'branch'), array('create_date' => 'DESC'));
        $red_car = count($thread_list);
        foreach ($thread_list as $thread) {
            if (!isset($branch_list[$thread['branch']['id']])) {
                continue;
            }
            $branch = $branch_list[$thread['branch']['id']];
            $content = str_replace(array("%MEMBERNAME%", "%TITLE%"), array($branch['name'], $thread['title']), $template["content"]);
            $this->sendSms($branch['mobile'], $content, $this->site_id, '', false, $this->vals['setting']['notice_sms_hour']);
        }
        $this->resultJson('催办短信发送成功！', '+OK', array('yellow_car' => $yellow_car, 'red_car' => $red_car));
    }

}

?>