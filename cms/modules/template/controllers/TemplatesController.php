<?php

/** @see BaseController */
require_once 'BaseController.php';
require_once MODULES_PATH . 'template/models/Templates.php';

class Template_TemplatesController extends BaseController
{
    protected $_db;
    protected $_table;
    
    public function init()
    {
	    $channel_db = $this->getChannelDbAdapter();
        $this->_db = $channel_db;
        
        Zend_Db_Table::setDefaultAdapter($channel_db);
        
        $this->_table = new TemplatesTable($channel_db);
    }
    
    public function indexAction()
    {
        $this->_checkPermission('templates', 'index');
        $perpage = 20;
        $page = $this->_getParam('page', 1);
        $page = max($page, 1);
        
        $type_array=$this->_table->typeArray();
        $this->view->type_array = $type_array;
        $total = $this->_table->count();
        $templates = $this->_table->fetchAll(null, 'id DESC', $perpage, ($page - 1) * $perpage);
        $pagebar = Util::buildPagebar($total, $perpage, $page, '?page=__page__');
        $this->view->templates = $templates;
        $this->view->pagebar = $pagebar;
    }
    
    public function createAction()
    {
        $this->_checkPermission('templates', 'add');
        $type_array=$this->_table->typeArray();
        $this->view->type_array = $type_array;
        if ($this->isPost()) {
            $tpl = array(
                'name' => $this->_getParam('name'),
                'type' => $this->_getParam('type'),
                'description' => $this->_getParam('desc'),
                'content' => $this->_getParam('content')
            );
			/* Because the issue of ZendFramework,there is a bug,it return null */
            $t= $this->_table->insert($tpl);
            if (false === $t) {				
                if($this->_table->error)$this->error($this->_table->error, false, '');
				else $this->error('模板创建成功');
                return false;
            }			
            $this->flash('模板创建成功', '/template/templates/');			
        }
    }
    
    public function editAction()
    {
        $this->_checkPermission('templates', 'edit');
        $type_array=$this->_table->typeArray();
        $this->view->type_array = $type_array;
        $id = (int) $this->_getParam('id', 0);
        if (!$id || false === $tpl = $this->_table->find($id)->current()) {
            $this->error('请指定模板', true);
        }
        
        $this->view->tpl = $tpl;
		$this->view->id = $id;
        
        if ($this->isPost()) {
            $tpl = array(
                'id' => $id,
                'name' => $this->_getParam('name'),
                'type' => $this->_getParam('type'),
                'description' => $this->_getParam('desc'),
                'content' => $this->_getParam('content')
            );

            if (false === $this->_table->edit($tpl, "id = $id")) {
                $this->error($this->_table->error);
                return false;
            }else{
				$user= Zend_Session::namespaceGet('user');		
				$channel= $user['channel'];
				$dir= DATA_PATH.'templates/'.$channel.'/';
				$file= $id;
				$array= array();				
				try {
					makedir($dir);
					if(file_exists($dir.$file)){
						$array= unserialize(file_get_contents($dir.$file));
						if(count($array)>= 20)array_shift($array);		
						clearstatcache();
					}				
					$array[]= $this->_getParam('content');
					file_put_contents($dir.$file, serialize($array));
				}catch(Exception $e){}
			}
           $this->flash('模板修改成功', '/template/templates/');
        }
    }

    public function logAction()
    {     
        $id = (int) $this->_getParam('id', 0);
        if (!$id || false === $tpl = $this->_table->find($id)->current()) {
            $this->error('请指定模板', true);
        }      
   
		$user= Zend_Session::namespaceGet('user');		
		$channel= $user['channel'];
		$dir= DATA_PATH.'templates/'.$channel.'/';
		$file= $id;
		$array= array();				
		//try {	
			if(file_exists($dir.$file)){
				$array= array_reverse(unserialize(file_get_contents($dir.$file)));
				clearstatcache();
			}
		//}catch(Exception $e){}
		$this->view->data = $array;
		$this->view->tpl = $tpl;
		$this->view->id = $id;
    }

    public function delAction()
    {
        $this->_checkPermission('templates', 'delete');
        $id = (int) $this->_getParam('id', 0);
        if (!$id || false === $tpl = $this->_table->find($id)->current()) {
            $this->error('请指定模板', true);
        }
        
        $this->_table->delete('id = ' . $id);
        
        $this->flash('模板删除成功', '/template/templates/');
    } 

	public function error($str='', $flag= true, $url='/template/templates/'){
		 echo "<script>";
		 if($str)echo "alert('{$str}');";
		 if($url)echo "window.location.href='{$url}'";
		 echo "</script>"; 
		 if($flag)exit;
	}
}