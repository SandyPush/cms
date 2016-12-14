<?php

/** @see BaseController */
require_once 'BaseController.php';
require_once MODULES_PATH . 'object/models/Objects.php';

class Object_ObjectsController extends BaseController
{
    protected $_db;
    protected $_table;
    protected $_types;
	protected $_type;
    
    public function init()
    {
	    $channel_db = $this->getChannelDbAdapter();
	    $this->_db=$channel_db;
        Zend_Db_Table::setDefaultAdapter($channel_db);
        $this->_table = new ObjectsTable();
        $this->_types = array(
            'code' => '代码',
            'manual' => '手动区',
            'list' => '列表'
        );
    }
    
    public function indexAction()
    {
        $this->_checkPermission('objects', 'index');
        $perpage = 20;
        $page = $this->_getParam('page', 1);
		$this->_type = $this->_getParam('type','');
        $page = max($page, 1);
        
        $type = $this->_getParam('type', 'all');
        $key = $this->_getParam('key', '');
        
        $params = array();
        if ($type != 'all') {
            array_push($params, 'type=' . $this->_db->quote($type));
        }
        
        if ($key !== '') {
            array_push($params, "name LIKE '%" . trim($this->_db->quote($key), "'") . "%'");
        }
        
        $where = $params ? join(' AND ', $params) : null;
        
        $total = $this->_table->count($where);		
        $objects = $this->_table->fetchAll($where, 'oid DESC', $perpage, ($page - 1) * $perpage);
        
        $pagebar = Util::buildPagebar($total, $perpage, $page, "?page=__page__&type=$type&key=$key");
        
        $options = array('all' => '全部') + $this->_types;
        $this->view->options = $options;
        $this->view->objects = $objects;
        $this->view->pagebar = $pagebar;
    }
    
    public function createAction()
    {
        $this->_checkPermission('objects', 'add');
        if ($this->isPost()) {
            $obj = array(
                'name' => $this->_getParam('name'),
                'description' => $this->_getParam('desc'),
                'type' => $this->_getParam('type'),
            );
            
            if (false === $this->_table->insert($obj)) {
                $this->error($this->_table->error);
                return false;
            }
            
            $this->flash('模块创建成功', '/object/objects/');
        }
        
        $this->view->types = $this->_types;
    }
    
    public function editAction()
    {
        $this->_checkPermission('objects', 'edit');
        $oid = (int) $this->_getParam('oid', 0);
        if (!$oid || false === $obj = $this->_table->find($oid)->current()) {
            $this->error('请指定模块', true);
        }
        
        $this->view->obj = $obj;
        $this->view->types = $this->_types;
        
        if ($this->isPost()) {
            $obj = array(
                'oid' => $oid,
                'name' => $this->_getParam('name'),
                'description' => $this->_getParam('desc'),
                'type' => $this->_getParam('type'),
            );

            if (false === $this->_table->edit($obj, "oid = $oid")) {
                $this->error($this->_table->error);
                return false;
            }

            $this->flash('模块修改成功', '/object/objects/');
        }               
    }
    
    public function delAction()
    {
        $this->_checkPermission('objects', 'delete');
        $oid = (int) $this->_getParam('oid', 0);
        if (!$oid || false === $obj = $this->_table->find($oid)->current()) {
            $this->error('请指定模块', true);
        }
        
        $this->_table->delete('oid = ' . $oid);
        
        // TODO: 其他处理
        
        $this->flash('模块删除成功', '/object/objects/');
    }    
}