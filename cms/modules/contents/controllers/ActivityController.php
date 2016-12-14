<?php
/** @see BaseController */
require_once 'BaseController.php';

class Contents_ActivityController extends BaseController
{
	private $channel_db;

	public function init()
	{
	    $channel_db = $this->getChannelDbAdapter();
	    $this->channel_db = $channel_db;
	}
	
	/**
	 * 活动列表
	 */
	public function indexAction()
	{
		// 权限检查
		$this->_checkPermission('activity', 'index');
		
		$select = $this->channel_db->select()->from('activity');
		
		// 搜索条件
		// 标题
		$title = trim($this->_getParam('title', ''));
		$this->view->title = $title;
		if ($title) {
			$select->where("`title` LIKE '%{$title}%'");
		}
		
		// 发布者
		$realname = trim($this->_getParam('realname', ''));
		$this->view->realname = $realname;
		if ($realname) {
			$uids = $this->_getUidByRealname($realname);
			if ($uids) {
				$select->where('`uid` IN (' . implode(',', $uids) . ')');
			} else {
				// 输入的发布者不存在
				$select->where('1 != 1');
			}
		}
		
		// 开始日期
		$start_time = trim($this->_getParam('start_time'));
		$this->view->start_time = $start_time;
		if ($start_time) {
			$select->where("`publish_time` >= '{$start_time}'");
		}
		
		// 结束日期
		$end_time = trim($this->_getParam('end_time'));
		$this->view->end_time = $end_time;
		if ($end_time) {
			$select->where("`publish_time` <= '{$end_time}'");
		}
		
		// 计算总数
		$count_sql = strstr($select->__toString(), 'FROM');
		$total = $this->channel_db->fetchOne("SELECT COUNT(*) {$count_sql}");
		
		// 排序
		$select->order('end_time DESC');
		
		// 分页
		$page = $this->_getParam('page', 1);
		$page = max($page, 1);
		$perpage = 15;
		$select->limitPage($page, $perpage);
		
		// 获取数据并预处理
		$activities = $this->channel_db->fetchAssoc($select);
		foreach ($activities as &$val) {
			$val['username'] = $this->_getUsernameByUid($val['uid']);
		}
		unset($val);
		
		// 注册到模板
		$this->view->activities = $activities;
		$this->view->pagebar = Util::buildPagebar($total, $perpage, $page, '?page=__page__');
	}

	/**
	 * 活动添加
	 */
	public function activityAddAction()
	{
		$this->_checkPermission('activity', 'activity-add');
		if ($this->_request->isPost()) {
			$activity = array(
					'title' => trim($this->_getParam('title')) ? trim($this->_getParam('title')) : $this->_showMsg('标题不能为空！'),
					'desc' => trim($this->_getParam('desc')) ? trim($this->_getParam('desc')) : $this->_showMsg('活动描述不能为空！'),
					'pc_img_url' => trim($this->_getParam('pc_img_url')) ? trim($this->_getParam('pc_img_url')) : $this->_showMsg('奖品图片（PC）不能为空！'),
					'phone_img_url' => trim($this->_getParam('phone_img_url')) ? trim($this->_getParam('phone_img_url')) : $this->_showMsg('奖品图片（Phone）不能为空！'),
					'content' => trim($this->_getParam('content')) ? trim($this->_getParam('content')) : $this->_showMsg('活动内容不能为空！'),
					'game_id' => intval(trim($this->_getParam('game_id'))) ? intval(trim($this->_getParam('game_id'))) : $this->_showMsg('游戏ID填写错误！'),
					'rank_num' => intval(trim($this->_getParam('rank_num'))) ? intval(trim($this->_getParam('rank_num'))) : $this->_showMsg('排行人数填写错误！'),
					'uid' => $this->_user['uid'],
					'start_time' => trim($this->_getParam('start_time')) ? trim($this->_getParam('start_time')) : $this->_showMsg('开始时间不能为空！'),
					'end_time' => trim($this->_getParam('end_time')) ? trim($this->_getParam('end_time')) : $this->_showMsg('结束时间不能为空！'),
					'create_time' => date('Y-m-d H:i:s')
			);

			// 获奖规则
            $reward_type = intval(trim($this->_getParam('reward_type'))) ? intval(trim($this->_getParam('reward_type'))) : $this->_showMsg('奖励类型选择错误！');
            if ($reward_type == 1) {
                // 按尾号判定是否得奖
                $award_tail_num = intval(trim($this->_getParam('award_tail_num'))) ? intval(trim($this->_getParam('award_tail_num'))) : $this->_show('获奖尾号不能为空!');
                $activity['award_rule'] = json_encode(array('name' => 'tail_num', 'num' => $award_tail_num));
                $activity['base_score'] = intval(trim($this->_getParam('base_score'))) ? intval(trim($this->_getParam('base_score'))) : $this->_showMsg('基础分数填写错误！');
            } else {
                // 奖励积分
                $activity['award_rule'] = json_encode(array('name' => 'get_score_by_rank'));
            }
			
			$this->channel_db->insert('activity', $activity);
			$this->flash('添加成功！', '/contents/activity/index', 1);
		}
	}
	
	/**
	 * 活动预览
	 */
	public function activityPreviewAction()
	{
		$this->_checkPermission('activity', 'activity-preview');
		$type = $this->_getParam('type');
		$aid = intval($this->_getParam('aid'));
		if ($type == 0) {
			$url = $this->getChannelConfig()->url->compete_pc_preview;
		} else {
			$url = $this->getChannelConfig()->url->compete_phone_preview;
		}
		header("location:{$url}?activity_id={$aid}");
		//TODO
		exit;
	}
	
	/**
	 * 活动修改
	 */
	public function activityEditAction()
	{
		$this->_checkPermission('activity', 'activity-edit');
		$aid = intval($this->_getParam('aid')) ? intval($this->_getParam('aid')) : $this->_showMsg('没有指定活动ID');
		if ($this->_request->isPost()) {
			$activity = array(
					'title' => trim($this->_getParam('title')) ? trim($this->_getParam('title')) : $this->_showMsg('标题不能为空！'),
					'desc' => trim($this->_getParam('desc')) ? trim($this->_getParam('desc')) : $this->_showMsg('活动描述不能为空！'),
					'pc_img_url' => trim($this->_getParam('pc_img_url')) ? trim($this->_getParam('pc_img_url')) : $this->_showMsg('奖品图片（PC）不能为空！'),
					'phone_img_url' => trim($this->_getParam('phone_img_url')) ? trim($this->_getParam('phone_img_url')) : $this->_showMsg('奖品图片（Phone）不能为空！'),
					'content' => trim($this->_getParam('content')) ? trim($this->_getParam('content')) : $this->_showMsg('活动内容不能为空！'),
					'game_id' => intval(trim($this->_getParam('game_id'))) ? intval(trim($this->_getParam('game_id'))) : $this->_showMsg('游戏ID填写错误！'),
					'rank_num' => intval(trim($this->_getParam('rank_num'))) ? intval(trim($this->_getParam('rank_num'))) : $this->_showMsg('排行人数填写错误！'),
					'start_time' => trim($this->_getParam('start_time')) ? trim($this->_getParam('start_time')) : $this->_showMsg('开始时间不能为空！'),
					'end_time' => trim($this->_getParam('end_time')) ? trim($this->_getParam('end_time')) : $this->_showMsg('结束时间不能为空！')
			);

            // 获奖规则
            $reward_type = intval(trim($this->_getParam('reward_type'))) ? intval(trim($this->_getParam('reward_type'))) : $this->_showMsg('奖励类型选择错误！');
            if ($reward_type == 1) {
                // 按尾号判定是否得奖
                $award_tail_num = intval(trim($this->_getParam('award_tail_num'))) ? intval(trim($this->_getParam('award_tail_num'))) : $this->_show('获奖尾号不能为空!');
                $activity['award_rule'] = json_encode(array('name' => 'tail_num', 'num' => $award_tail_num));
                $activity['base_score'] = intval(trim($this->_getParam('base_score'))) ? intval(trim($this->_getParam('base_score'))) : $this->_showMsg('基础分数填写错误！');
            } else {
                // 奖励积分
                $activity['award_rule'] = json_encode(array('name' => 'get_score_by_rank'));
            }
				
			$this->channel_db->update('activity', $activity, '`aid` = ' . $aid);
			$this->flash('修改成功！', '/contents/activity/index', 1);
		} else {
			$sql = $this->channel_db->select()->from('activity')->where('`aid` = ' . $aid);
			$activity = $this->channel_db->fetchRow($sql);
			
			// 获奖尾号
			$award_rule = json_decode($activity['award_rule'], true);
			$activity['award_tail_num'] = isset($award_rule['name']) && $award_rule['name'] == 'tail_num' ? $award_rule['num'] : '';
			$activity['reward_type'] = isset($award_rule['name']) && $award_rule['name'] == 'tail_num' ? 1 : 2;
			$this->view->activity = $activity;
		}
	}
	
	/**
	 * 活动发布
	 */
	public function activityPublishAction()
	{
		$this->_checkPermission('activity', 'activity-publish');
		$aid = intval($this->_getParam('aid')) ? intval($this->_getParam('aid')) : $this->_showMsg('没有指定活动ID');
		$this->channel_db->update('activity', array('publish_time' => date('Y-m-d H:i:s')), '`aid` = ' . $aid);

        // 同步图片到线上
        if (is_file("/VODONE/www/vodone.cms/publish/h5/uploadimg.sh")) {
            exec("/VODONE/www/vodone.cms/publish/h5/uploadimg.sh");
        }

		$this->flash('发布成功！', '/contents/activity/index', 1);
	}
	
	/**
	 * 活动删除
	 */
	public function activityDelAction()
	{
		$this->_checkPermission('activity', 'activity-del');
		$aid = intval($this->_getParam('aid')) ? intval($this->_getParam('aid')) : $this->_showMsg('没有指定活动ID');
		$this->channel_db->delete('activity', '`aid` = ' . $aid);
		$this->flash('删除成功！', '/contents/activity/index', 2);
	}
	
	/**
	 * 活动预告列表
	 */
	public function foreshowListAction()
	{

		// 权限检查
		$this->_checkPermission('activity', 'foreshow-list');
		
		$select = $this->channel_db->select()->from('activity_foreshow');
		
		// 搜索条件
		// 标题
		$title = trim($this->_getParam('title', ''));
		$this->view->title = $title;
		if ($title) {
			$select->where("`title` LIKE '%{$title}%'");
		}
		
		// 发布者
		$realname = trim($this->_getParam('realname', ''));
		$this->view->realname = $realname;
		if ($realname) {
			$uids = $this->_getUidByRealname($realname);
			if ($uids) {
				$select->where('`uid` IN (' . implode(',', $uids) . ')');
			} else {
				// 输入的发布者不存在
				$select->where('1 != 1');
			}
		}
		
		// 开始日期
		$start_time = trim($this->_getParam('start_time'));
		if ($start_time) {
			$select->where("`publish_time` >= '{$start_time}'");
		}
		
		// 结束日期
		$end_time = trim($this->_getParam('end_time'));
		if ($end_time) {
			$select->where("`publish_time` <= '{$end_time}'");
		}
		
		// 计算总数
		$count_sql = strstr($select->__toString(), 'FROM');
		$total = $this->channel_db->fetchOne("SELECT COUNT(*) {$count_sql}");
		
		// 排序
		$select->order('create_time DESC');
		
		// 分页
		$page = $this->_getParam('page', 1);
		$page = max($page, 1);
		$perpage = 15;
		$select->limitPage($page, $perpage);
		
		// 获取数据并预处理
		$foreshows = $this->channel_db->fetchAssoc($select);
		foreach ($foreshows as &$val) {
			$val['username'] = $this->_getUsernameByUid($val['uid']);
		}
		unset($val);
		
		// 注册到模板
		$this->view->foreshows = $foreshows;
		$this->view->pagebar = Util::buildPagebar($total, $perpage, $page, '?page=__page__');
	}
	
	/**
	 * 活动预告添加
	 */
	public function foreshowAddAction()
	{
		$this->_checkPermission('activity', 'foreshow-add');
		if ($this->_request->isPost()) {
			$activity = array(
					'title' => trim($this->_getParam('title')) ? trim($this->_getParam('title')) : $this->_showMsg('标题不能为空！'),
					'desc' => trim($this->_getParam('desc')) ? trim($this->_getParam('desc')) : $this->_showMsg('活动描述不能为空！'),
					'pc_img_url' => trim($this->_getParam('pc_img_url')) ? trim($this->_getParam('pc_img_url')) : $this->_showMsg('奖品图片（PC）不能为空！'),
					'phone_img_url' => trim($this->_getParam('phone_img_url')) ? trim($this->_getParam('phone_img_url')) : $this->_showMsg('奖品图片（Phone）不能为空！'),
					'game_id' => intval(trim($this->_getParam('game_id'))) ? intval(trim($this->_getParam('game_id'))) : $this->_showMsg('游戏ID填写错误！'),
					'uid' => $this->_user['uid'],
					'create_time' => date('Y-m-d H:i:s'),
			);
				
			$this->channel_db->insert('activity_foreshow', $activity);
			$this->flash('添加成功！', '/contents/activity/foreshow-list', 1);
		}
	}

	/**
	 * 活动预告预览
	 */
	public function foreshowPreviewAction()
	{
		$this->_checkPermission('activity', 'foreshow-preview');
		$type = $this->_getParam('type');
		$aid = intval($this->_getParam('aid'));
		if ($type == 0) {
			$url = $this->getChannelConfig()->url->compete_pc_preview;
		} else {
			$url = $this->getChannelConfig()->url->compete_phone_preview;
		}
		header("location:{$url}?foreshow_id={$aid}");
		//TODO
		exit;
	}
	/**
	 * 活动预告发布
	 */
	public function foreshowPublishAction()
	{
		$this->_checkPermission('activity', 'foreshow-publish');
		$aid = intval($this->_getParam('aid')) ? intval($this->_getParam('aid')) : $this->_showMsg('没有指定活动ID');
		$this->channel_db->update('activity_foreshow', array('publish_time' => date('Y-m-d H:i:s')), '`aid` = ' . $aid);

        // 同步图片到线上
        if (is_file("/VODONE/www/vodone.cms/publish/h5/uploadimg.sh")) {
            exec("/VODONE/www/vodone.cms/publish/h5/uploadimg.sh");
        }

		$this->flash('发布成功！', '/contents/activity/foreshow-list', 1);
	}
	
	/**
	 * 活动预告修改
	 */
	public function foreshowEditAction()
	{
		$this->_checkPermission('activity', 'foreshow-edit');
		$aid = intval($this->_getParam('aid')) ? intval($this->_getParam('aid')) : $this->_showMsg('没有指定活动ID');
		if ($this->_request->isPost()) {
			$activity = array(
					'title' => trim($this->_getParam('title')) ? trim($this->_getParam('title')) : $this->_showMsg('标题不能为空！'),
					'desc' => trim($this->_getParam('desc')) ? trim($this->_getParam('desc')) : $this->_showMsg('活动描述不能为空！'),
					'pc_img_url' => trim($this->_getParam('pc_img_url')) ? trim($this->_getParam('pc_img_url')) : $this->_showMsg('奖品图片（PC）不能为空！'),
					'phone_img_url' => trim($this->_getParam('phone_img_url')) ? trim($this->_getParam('phone_img_url')) : $this->_showMsg('奖品图片（Phone）不能为空！'),
					'game_id' => intval(trim($this->_getParam('game_id'))) ? intval(trim($this->_getParam('game_id'))) : $this->_showMsg('游戏ID填写错误！'),
			);
		
			$this->channel_db->update('activity_foreshow', $activity, '`aid` = ' . $aid);
			$this->flash('修改成功！', '/contents/activity/foreshow-list', 1);
		} else {
			$sql = $this->channel_db->select()->from('activity_foreshow')->where('`aid` = ' . $aid);
			$this->view->foreshow = $this->channel_db->fetchRow($sql);
		}
	}
	
	/**
	 * 活动预告删除
	 */
	public function foreshowDelAction()
	{
		$this->_checkPermission('activity', 'foreshow-del');
		$aid = intval($this->_getParam('aid')) ? intval($this->_getParam('aid')) : $this->_showMsg('没有指定活动ID');
		$this->channel_db->delete('activity_foreshow', '`aid` = ' . $aid);
		$this->flash('删除成功！', '/contents/activity/foreshow-list', 1);
	}
	
	/**
	 * 根据用户ID获取用户名
	 * @param integer $uid
	 * @return string|boolean
	 */
	private function _getUsernameByUid($uid){
		$sql = $this->channel_db->select()->from('users', 'realname')->where('`uid` = ' . $uid);
		$username = $this->channel_db->fetchOne($sql);
		if ($username) {
			return $username;
		} else {
			return false;
		}
	}
	
	/**
	 * 根据用户名获取用户ID
	 */
	private function _getUidByRealname($realname){
		$sql = $this->channel_db->select()->from('users', 'uid')->where("`realname` LIKE '%{$realname}%'");
		$users = $this->channel_db->fetchAll($sql);
		if ($users) {
			$uids = array();
			foreach ($users as $val) {
				$uids[] = $val['uid'];
			}
			return $uids;
		} else {
			return null;
		}
	}
	
	/**
	 * 回退到上一步
	 */
	private function _showMsg($str='', $url='', $flag=1){
		echo "<script>";
		if ($str) {
			echo "alert('{$str}');";
		}
		if ($url) {
			echo "window.location.href='{$url}'";
		} else {
			echo 'window.history.back();';
		}
		echo "</script>"; 
		if($flag)exit;
	}
}