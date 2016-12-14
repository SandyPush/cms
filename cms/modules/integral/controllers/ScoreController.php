<?php
/**
 * 该控制器暂时无用
 */

/** @see BaseController */
require_once 'BaseController.php';
require_once MODULES_PATH . 'integral/models/score.php';
//require_once MODULES_PATH . 'system/models/UserGroups.php';
require_once 'Zend/Db/Table.php';

class Integral_ScoreController extends BaseController
{

    protected $_Score_table;    
    protected $_db;
    
    public function init()
    {
	    
        $config= new Zend_Config_Ini(ROOT_PATH . 'config.ini');   
        $db = Zend_Db::factory('PDO_MYSQL', $config->integraldb->toArray());
        $db->query("SET NAMES 'utf8'");
        $this->_db = $db;
        //$channel_db = $this->getChannelDbAdapter();
	    //$this->_db=$channel_db;
        //Zend_Db_Table::setDefaultAdapter($channel_db);

        $this->_Score_table = new ScoreTable($db);
    }

    /**
     * groups list action
     *
     */
    public function indexAction()
    {
        //$this->_checkPermission('level', 'index');
        //$groups = $this->_groups_table->fetchAll();
        //$this->view->groups = $groups;
        $select=$this->_db->select();
        $select->from('userScore','*');
        if(!empty($username))$select->where("username LIKE '%".$username."%'");
        if(!empty($realname))$select->where("realname LIKE '%".$realname."%'");
        $count_sql=strstr($select->__toString(),'FROM');
        //$select->order('usergroup');
        $select->order('id');
        $select->limitPage($page,$perpage);
        $scores = $this->_db->fetchAll($select);
        $total = $this->_db->fetchOne("SELECT COUNT(*) ".$count_sql);
        $pagebar = Util::buildPagebar($total, $perpage, $page, '?page=__page__');
        $this->view->scores = $scores;//print_r($levels);
        $this->view->pagebar = $pagebar;
    }

    public function createAction()
    {
        $this->_checkPermission('usergroup', 'add');
        $groups = array(0 => '请选择');
        foreach ($this->_groups_table->fetchAll() as $group) {
            $groups[$group->gid] = $group->name;
        }

        $this->view->groups = $groups;

        if ($this->isPost()) {
            $group = array (
                'name' => $this->_getParam('name'),
                'inherit_from' => $this->_getParam('inherit'),
                'intro' => $this->_getParam('intro')
            );

            if (false === $this->_groups_table->insert($group)) {
                $this->error($this->_groups_table->error);
                return false;
            }

            $this->flash('用户组创建成功', '/system/group/');
        }
    }

    public function editAction()
    {
        $id = (int) $this->_getParam('id', 0);
        if (!$id || false === $level = $this->_level_table->find($id)) {
            $this->error('请指定等级', true);
        }
        $level = $level->current();
        $this->view->level = $level;
        //$this->view->taskTypes = array(1=>'新手任务',2=>'每日任务',3=>'节日任务',4=>'特殊任务');
        if ($this->isPost()) {
            //$lv=$this->_getParam('lv');           
            $lvName=$this->_getParam('lvName');
            $new = array(
                //'lv' => $lv,
                'lvName' => $lvName,
                'minScore' => $this->_getParam('minScore'),
                'maxScore' => $this->_getParam('maxScore'),
                  
            );
           
            if (false == $this->_level_table->edit($new, 'id = ' . $id)) {
                $this->error($this->_level_table->error);
                return false;
            }
            
            // redirect
            $this->flash('等级编辑成功', '/integral/level/');
        }
    }
    
    public function delAction()
    {
        $this->_checkPermission('usergroup', 'delete');
        $gid = $this->_getParam('gid');
        $group = $this->_groups_table->find($gid)->current();
        if (!$group) {
            $this->error('请指定用户组', true);
        }

        if ($group->type == 'system') {
            $this->error('不能删除系统用户组', true);
        }
        
        $this->_groups_table->update(array('inherit_from' => 0), 'inherit_from = ' . $gid);        
        $this->_groups_table->delete('gid = ' . $gid);
        
        $this->flash('用户组删除成功', '/system/group/');
    }
    
    
    
}