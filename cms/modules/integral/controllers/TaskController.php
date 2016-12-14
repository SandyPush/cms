<?php

/** @see BaseController */
require_once 'BaseController.php';
require_once MODULES_PATH . 'integral/models/Task.php';

class Integral_TaskController extends BaseController
{
    protected $_task_table;    
    protected $_db;
	
    public function init()
    {		
        $config= new Zend_Config_Ini(ROOT_PATH . 'config.ini');   
        $db = Zend_Db::factory('PDO_MYSQL', $config->integraldb->toArray());
        $db->query("SET NAMES 'utf8'");
        $this->_db = $db;
        $this->_task_table = new TaskTable($db);
    }
    
    public function indexAction()
    {
        //$this->_checkPermission('user', 'index');
		//$username=$this->_getParam('username', '');
		//$realname=$this->_getParam('realname', '');
        //$this->view->username = $username;
        //$this->view->realname = $realname;
        $select=$this->_db->select();
        $select->from('task','*');
        //if(!empty($username))$select->where("username LIKE '%".$username."%'");
        //if(!empty($realname))$select->where("realname LIKE '%".$realname."%'");
        $count_sql=strstr($select->__toString(),'FROM');
        //$select->order('usergroup');
        $select->order('id');
        $tasks = $this->_db->fetchAll($select);
        $this->view->taskTypes = array(1=>'新手任务',2=>'每日任务',3=>'节日任务',4=>'特殊任务');
    	$this->view->tasks = $tasks;
        
        
    }


    public function createAction()
    {
        //$this->_checkPermission('usergroup', 'add');
        $this->view->taskTypes = array(1=>'新手任务',2=>'每日任务',3=>'节日任务',4=>'特殊任务');
        if ($this->isPost()) {
            $task = array (
                'taskName' => $this->_getParam('taskName'),
                'taskDesc' => $this->_getParam('taskDesc'),
                'taskType' => $this->_getParam('taskType'),
                'jumpUrl' => $this->_getParam('jumpUrl'),
                'score' => $this->_getParam('score'),
                'step' => $this->_getParam('step'),
                'startTime' => strtotime($this->_getParam('startTime')),
                'endTime' => strtotime($this->_getParam('endTime'))
            );

            if (false === $this->_task_table->insert($task)) {
                $this->error($this->_task_table->error);
                return false;
            }

            $this->flash('任务创建成功', '/integral/task/');
        }
    }



    public function editAction()
    {
        //$this->_checkPermission('task', 'edit');
        $id = (int) $this->_getParam('id', 0);
        if (!$id || false === $task = $this->_task_table->find($id)) {
            $this->error('请指定任务', true);
        }
        $task = $task->current();
        $this->view->task = $task;
        $this->view->taskTypes = array(1=>'新手任务',2=>'每日任务',3=>'节日任务',4=>'特殊任务');
        if ($this->isPost()) {
            $taskName=$this->_getParam('taskName');           
            $taskDesc=$this->_getParam('taskDesc');
            $new = array(
                'taskName' => $taskName,
                'taskDesc' => $taskDesc,
                'taskType' => $this->_getParam('taskType'),
                'jumpUrl' => $this->_getParam('jumpUrl'),
                'score' => $this->_getParam('score'),
                'step' => $this->_getParam('step'),
                'startTime' => strtotime($this->_getParam('startTime')),
                'endTime' => strtotime($this->_getParam('endTime')),
                'isShow' => $this->_getParam('isShow'),     
            );
           
            if (false == $this->_task_table->edit($new, 'id = ' . $id)) {
                $this->error($this->_task_table->error);
                return false;
            }
            
            // redirect
            $this->flash('任务编辑成功', '/integral/task/');
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
