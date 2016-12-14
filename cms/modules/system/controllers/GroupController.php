<?php

/** @see BaseController */
require_once 'BaseController.php';
//require_once MODULES_PATH . 'system/models/Users.php';
require_once MODULES_PATH . 'system/models/UserGroups.php';
require_once 'Zend/Db/Table.php';

class GroupController extends BaseController
{
    private $_db;
    private $_groups_table;

    public function init()
    {
	    $channel_db = $this->getChannelDbAdapter();
	    $this->_db=$channel_db;
        Zend_Db_Table::setDefaultAdapter($channel_db);

        $this->_groups_table = new UserGroupsTable($channel_db);
    }

    /**
     * groups list action
     *
     */
    public function indexAction()
    {
        $this->_checkPermission('usergroup', 'index');
        $groups = $this->_groups_table->fetchAll();
        $this->view->groups = $groups;
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
        $this->_checkPermission('usergroup', 'edit');
        $gid = $this->_getParam('gid');
        $group = $this->_groups_table->find($gid)->current();
        if (!$group) {
            $this->error('请指定用户组', true);
        }

        if ($group->type == 'system') {
            $this->error('不能编辑系统用户组', true);
        }

        $groups = array(0 => '请选择');
        foreach ($this->_groups_table->fetchAll() as $g) {
            if ($g->gid == $gid) {
                continue;
            }

            $groups[$g->gid] = $g->name;
        }

        $this->view->group = $group;
        $this->view->groups = $groups;

        if ($this->isPost()) {
            $group = array(
                'gid' => $gid,
                'name' => $this->_getParam('name'),
                'inherit_from' => $this->_getParam('inherit'),
                'intro' => $this->_getParam('intro')
            );

            if (false === $this->_groups_table->edit($group, "gid = $gid")) {
                $this->error($this->_groups_table->error);
                return false;
            }

            $this->flash('修改成功', '/system/group/');
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
    
    public function aclAction()
    {
        $this->_checkPermission('usergroup', 'acl');
        $gid = $this->_getParam('gid');
        $group = $this->_groups_table->find($gid)->current();
        if (!$group) {
            $this->error('请指定用户组', true);
        }
        $resources = Zend_Registry::get('settings')->resources->toArray();

        $this->view->group = $group;
        $this->view->role = $gid;
        $this->view->acl = $this->_acl;
        $this->view->resources = $resources;

        
        if ($this->isPost()) {
            
            $resources_selected = $this->_getParam('res');
            
            /*
            // 跟此用户组有关的用户组
            $roles = $this->_db->fetchPairs($this->_db->select()->from('user_groups', array('gid', 'inherit_from')));

            $depended_roles = array();
            $current_role = $gid;
            while ($roles[$current_role]) {
                array_push($depended_roles, $current_role);
                $current_role = $roles[$current_role];
            }*/
            
            $this->_db->query("DELETE FROM acl WHERE role = $gid");
            
            $parent = $group->inherit_from;
            foreach ($resources as $res => $perms) {
                $allowed = array();
                $denied = array();
                
                foreach ($perms as $perm => $name) {
                    $isset = isset($resources_selected[$res][$perm]);
                    $parent_allowed = $parent ? $this->_acl->isAllowed($parent, $res, $perm) : false;
                    
                    if (!$parent_allowed && $isset) {
                        array_push($allowed, $perm);
                    } elseif ($parent_allowed && !$isset) {
                        array_push($denied, $perm);
                    }
                }
                
                if (!$allowed && !$denied) {
                    continue;
                }
                
                $data = array (
                    'role' => $gid,
                    'resource' => $res,
                );
                
                if ($allowed){
                    $allowed = $allowed == array_keys($perms) ? '' : join(', ', $allowed);
                    $data['permissions'] = $allowed;
                    $data['aord'] = 'allow';
                    
                    $this->_db->insert('acl', $data);
                }
                
                if ($denied){
                    $denied = $denied == array_keys($perms) ? '' : join(', ', $denied);
                    $data['permissions'] = $denied;
                    $data['aord'] = 'deny';
                    
                    $this->_db->insert('acl', $data);
                }
            }
            
            $this->flash('权限修改成功', '/system/group/acl/gid/' . $gid);
        }
    }
    
}