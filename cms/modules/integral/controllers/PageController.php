<?php
/** @see BaseController */
require_once 'BaseController.php';

class Integral_PageController extends BaseController
{
    private $_db;
    private $_channel_db;
    private $_page_table = 'integral_page';

    public function init()
    {
        $config = new Zend_Config_Ini(ROOT_PATH . 'config.ini');
        $this->_db = Zend_Db::factory('PDO_MYSQL', $config->integraldb->toArray());
        $this->_db->query("SET NAMES 'utf8'");

        $this->_channel_db = $this->getChannelDbAdapter();
    }

    /**
     * 更新列表
     */
    public function indexAction()
    {
        // 权限检查
        $this->_checkPermission('integral-page', 'list');

        $select = $this->_db->select()->from($this->_page_table);

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
        $total = $this->_db->fetchOne("SELECT COUNT(*) {$count_sql}");

        // 排序
        $select->order('create_time DESC');

        // 分页
        $page = $this->_getParam('page', 1);
        $page = max($page, 1);
        $perpage = 15;
        $select->limitPage($page, $perpage);

        // 获取数据并预处理
        $items = $this->_db->fetchAssoc($select);
        foreach ($items as &$val) {
            $val['username'] = $this->_getUsernameByUid($val['uid']);
            $val['content1_desc'] = mb_substr(strip_tags($val['content1']), 0, 40);
            $val['content2_desc'] = mb_substr(strip_tags($val['content2']), 0, 40);
        }
        unset($val);

        // 注册到模板
        $this->view->items = $items;
        $this->view->pagebar = Util::buildPagebar($total, $perpage, $page, '?page=__page__');
    }

    /**
     * 活动添加
     */
    public function addAction()
    {
        $this->_checkPermission('integral-page', 'add');
        if ($this->_request->isPost()) {
            $item = array(
                'title' => trim($this->_getParam('title')) ? trim($this->_getParam('title')) : $this->_showMsg('标题不能为空！'),
                'content1' => trim($this->_getParam('content1')) ? trim($this->_getParam('content1')) : $this->_showMsg('第一美女内容不能为空！'),
                'content2' => trim($this->_getParam('content2')) ? trim($this->_getParam('content2')) : $this->_showMsg('每日一笑内容不能为空！'),
                'uid' => $this->_user['uid'],
                'create_time' => date('Y-m-d H:i:s'),
            );

            $this->_db->insert($this->_page_table, $item);
            $this->flash('添加成功！', '/integral/page/index', 1);
        }
    }

    /**
     * 页面预览
     */
    public function previewAction()
    {
        $this->_checkPermission('integral-page', 'preview');
        $id = intval($this->_getParam('id'));
        $url = $this->getChannelConfig()->url->integral_page_preview;
        header("location:{$url}?page_id={$id}");
        exit;
    }


    /**
     * 页面修改
     */
    public function editAction()
    {
        $this->_checkPermission('integral-page', 'edit');
        $id = intval($this->_getParam('id')) ? intval($this->_getParam('id')) : $this->_showMsg('没有指定页面ID');
        if ($this->_request->isPost()) {
            $item = array(
                'title' => trim($this->_getParam('title')) ? trim($this->_getParam('title')) : $this->_showMsg('标题不能为空！'),
                'content1' => trim($this->_getParam('content1')) ? trim($this->_getParam('content1')) : $this->_showMsg('第一美女内容不能为空！'),
                'content2' => trim($this->_getParam('content2')) ? trim($this->_getParam('content2')) : $this->_showMsg('每日一笑内容不能为空！'),
            );

            $this->_db->update($this->_page_table, $item, '`id` = ' . $id);
            $this->flash('修改成功！', '/integral/page/index', 1);
        } else {
            $sql = $this->_db->select()->from($this->_page_table)->where('`id` = ' . $id);
            $item = $this->_db->fetchRow($sql);
            $this->view->item = $item;
        }
    }

    /**
     * 页面发布
     */
    public function publishAction()
    {
        $this->_checkPermission('integral-page', 'publish');
        $id = intval($this->_getParam('id')) ? intval($this->_getParam('id')) : $this->_showMsg('没有指定页面ID');
        $this->_db->update($this->_page_table, array('publish_time' => date('Y-m-d H:i:s')), '`id` = ' . $id);

        // 同步图片到线上
        if (is_file("/VODONE/www/vodone.cms/publish/h5/uploadimg.sh")) {
            exec("/VODONE/www/vodone.cms/publish/h5/uploadimg.sh");
        }
        $this->flash('发布成功！', '/integral/page/index', 1);
    }

    /**
     * 活动删除
     */
    public function delAction()
    {
        $this->_checkPermission('integral-page', 'del');
        $id = intval($this->_getParam('id')) ? intval($this->_getParam('id')) : $this->_showMsg('没有指定页面ID');
        $this->_db->delete($this->_page_table, '`id` = ' . $id);
        $this->flash('删除成功！', '/integral/page/index', 2);
    }

    /**
     * 根据用户ID获取用户名
     * @param integer $uid
     * @return string|boolean
     */
    private function _getUsernameByUid($uid){
        $sql = $this->_channel_db->select()->from('users', 'realname')->where('`uid` = ' . $uid);
        $username = $this->_channel_db->fetchOne($sql);
        if ($username) {
            return $username;
        } else {
            return false;
        }
    }

    /**
     * 根据用户名获取用户ID
     */
    private function _getUidByRealname($realname)
    {
        $sql = $this->_channel_db->select()->from('users', 'uid')->where("`realname` LIKE '%{$realname}%'");
        $users = $this->_channel_db->fetchAll($sql);
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
     *
     * @param string $str 提示文字
     * @param string $url 跳转地址
     * @param int $flag 是否直接退出
     * @return boolean
     */
    private function _showMsg($str = '', $url = '', $flag = 1)
    {
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
        if ($flag) {
            exit;
        } else {
            return true;
        }
    }
}