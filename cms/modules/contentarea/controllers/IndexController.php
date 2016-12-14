<?php

/** @see BaseController */
require_once 'BaseController.php';
require_once MODULES_PATH . 'contentarea/models/Contentarea.php';
require_once MODULES_PATH . 'contentarea/models/Objects.php';

class Contentarea_IndexController extends BaseController{
    protected $_db;
    protected $_table;
	private $base_path;
	private $base_url;
	protected $channel;

    
    public function init(){
        $db = $this->getChannelDbAdapter();
        $this->_db = $db;        
        Zend_Db_Table::setDefaultAdapter($db);   
        $this->_table = new Contentarea($db);
		$user= Zend_Session::namespaceGet('user');		
		$this->channel= $user['channel'];	 	
		$this->base_path= $this->getChannelConfig($this->channel)->path->images.'/contentarea';
    	$this->base_url= $this->getChannelConfig($this->channel)->url->images.'contentarea';
		
    }

	public function indexAction(){	
		$id = $this->_getParam('oid','');
		if(!$id){
			$this->flash('参数错误', '/object/objects/index/type/manual');	
			return false;
		}

		$pagenum = 10;
        $page = $this->_getParam('page', 1);
        $page = max($page, 1);
        $start =($page - 1) * $pagenum;     
		    	
		$total = $this->_table->count($id);
        $pagebar = Util::buildPagebar($total, $pagenum, $page, '?page=__page__');
		$this->view->pagebar = $pagebar;		
		$this->view->data=$this->_table->getList($id,$start,$pagenum);	
		
		$db=$this->_table->getAdapter();
		$where = $db->quoteInto('oid = ?', $id );
		$order = 'oid ASC';
		$this->_table = new Objects();
		$info = $this->_table->fetchRow($where, $order)->toArray();		
		$this->view->oid = $id;
		$this->view->info = $info;		
	}

	public function addAction(){
		$id = $this->_getParam('oid','');	
		
		$this->_table = new Objects();

		$db=$this->_table->getAdapter();
		$where = $db->quoteInto('oid = ?', $id );
		$order = 'oid';
		$data = $this->_table->fetchRow($where, $order)->toArray();		
		
		$this->view->oid = $id;
		$this->view->data = $data;

	}

	public function insertAction(){		
		if($this->isPost()) {
			$ahtml= trim($this->_getParam('ahtml'));
			if($this->_getParam('type')==2 && $_FILES['ahtml3']['size']){
				$ext=explode(".", $_FILES['ahtml3']['name']);	
				$fileext=strtolower(end($ext));
		
				if($fileext!='jpg' && $fileext!='gif' && $fileext!='png'){
					$this->flash('请上传一张合法的图片,谢谢合作!', '/contentarea/index/add/oid/'.intval($this->_getParam('oid')));
					return false;
				}
				if($_FILES['ahtml3']['size']==0){
					$this->flash('请上传一张合法的图片,谢谢合作!', '/contentarea/index/add/oid/'.intval($this->_getParam('oid')));
					return false;
				}
		
				$time=date('Y/m/d',time());
				$filename=substr(md5($_FILES['ahtml3']['name']),0,10).'_'.time().'.'.$fileext;
				$filedir=$this->base_path.'/'.$time.'/';
				$filedir=str_replace(array("//","\\","\\\\"),'/',$filedir);

				$fileurl=$this->base_url.'/'.$time.'/'.$filename;
				$filename=$filedir.$filename;	
				$tmp_name=$_FILES['ahtml3']['tmp_name']; 

				$this->_table->makedir(dirname($filename));
				$ahtml= $this->_table->uploadfile($tmp_name, $filename)?$fileurl:$ahtml;
			}		

		
            $data = array(
         		'caption' =>  trim($this->_getParam('caption')),
				'pid' =>  trim($this->_getParam('pid')),
				'value' =>  $ahtml,
				'name'=> htmlspecialchars($this->_getParam('name')),
				'oid'=> intval($this->_getParam('oid')),
				'type'=> intval($this->_getParam('type')),
				'max_length'=> intval($this->_getParam('max_length')),
				'info'=>  htmlspecialchars($this->_getParam('info'))
            );         
			
            if (false === $this->_table->insert($data)) {
                $this->error($this->_table->error);
            }else{            
				$this->flash('添加成功', '/object/objects/index/type/manual');
			}
		}
	}

	public function	uploadAction(){
		$aid= trim($this->_getParam('aid'));
		$html='';
		$html.='<div id="popdiv" style="padding:15px 20px 0 20px;">';
		$html.="<form id=\"form2\" name=\"form2\" method=\"post\" action=\"/contentarea/index/upfile/aid/$aid\"  enctype=\"multipart/form-data\">";
		$html.='<input name="image" id="uploadimg" type="file"  size="30" />';
		$html.='<input name="uploadaid" type="hidden"  value="'.$aid.'"/>&nbsp;&nbsp;';
		$html.='<input type="submit" value="上传"/>';
		$html.='<br /><br /><span style="font-size:12px;margin-top:15px;color:#333">限制jpg或gif格式,上传完毕后不要忘了在主页面点击"修改".</span>';
		$html.='</form>';
		$html.='</div>';
		die($html);
	}

	public function upfileAction(){
		$aid= trim($this->_getParam('aid'));
		if($this->isPost()) {	
			if($_FILES){
				$ext=explode(".", $_FILES['image']['name']);	
				$fileext=strtolower(end($ext));
				
				if($fileext!='jpg' && $fileext!='gif' && $fileext!='png'){					
					$this->flash('请上传一张合法的图片,谢谢合作!', '/contentarea/index/upload/aid/'.intval($this->_getParam('aid')));
					return false;
				}
				if($_FILES['image']['size']==0){
					$this->flash('请上传一张合法的图片,谢谢合作!', '/contentarea/index/upload/aid/'.intval($this->_getParam('aid')));
					return false;
				}
			
				$time=date('Y/m/d',time());
				$filename=substr(md5($_FILES['image']['name']),0,10).'_'.time().'.'.$fileext;
				$filedir=$this->base_path.'/'.$time.'/';
				$filedir=str_replace(array("//","\\","\\\\"),'/',$filedir);

				$fileurl=$this->base_url.'/'.$time.'/'.$filename;
				$filename=$filedir.$filename;	
				$tmp_name=$_FILES['image']['tmp_name']; 

				$this->_table->makedir(dirname($filename));
				if($this->_table->uploadfile($tmp_name, $filename)){
					echo '<script>alert("上传成功");parent.$("#value'.$aid.'").val("'.$fileurl.'");parent.$("#floatBox .title span").click();</script>';
				}else{
					//echo '<script>alert("上传失败'.$tmp_name.$filename.'");window.location.href="/contentarea/index/upload/aid/$aid";</script>';
					echo '<script>alert("上传失败");window.location.href="/contentarea/index/upload/aid/$aid";</script>';
				}
				exit;
			}
		}
	}

	public function deleteAction(){
		$aid= trim($this->_getParam('aid'));
		$this->_table->delete('aid = ' . $aid);
		exit("OK!");
	}

	public function deleteallAction(){
		$aid= trim($_POST['ids']);
		echo $this->_getParam('ids');		
		$this->_table->delete('aid IN('.$aid.')');
		exit("OK!");
	}

	public function updateAction(){			
		$aid= $this->_getParam('aid');		
		$oid=intval($_POST['oid']);
		$pid=trim($_POST['pid']);	
		$name=trim($_POST['name']);	
		$caption = trim($_POST['caption']);
		$info = $_POST['info'];		
		$max_length = intval($_POST['max_length']);		
		$type=intval($_POST['type']);
		$value = $_POST['value'];

		(!$aid or !$oid or !$name or !$caption) && die('Error!');	
	
		$where = $this->_db->quoteInto('aid = ?', $aid);
	
		$data = array(	
			'caption' => $caption,
			'info' => $info,
			'pid' => $pid,
			'max_length' => $max_length,
			'type' => $type,
			'value' =>$value
		);
		$this->_table->update($data, $where);		
		die('OK!');
	}

	public function updateallAction(){	
		//channel
		$channel= $this->_getParam('channel', '');
		if($channel && ($channel!= $this->channel)){		
			if($this->_getParam('mode')=='show'){				
				$this->flash('所编辑的模块已不属于当前频道,可能的原因是你在编辑的过程中切换了频道!', '/contentarea/index/show/oid/'.trim($_POST['ids']).'/pid/'.$_POST['pid'][0],3);
			}elseif($this->_getParam('mode')=='ajax'){
				die('The editing module is no longer belong to the current channel');				
			}			
		}
	
		// Post by xmlhttp, encoding		
		if($this->_getParam('mode')=='ajax'){		
			foreach($_POST as $key => $var){
				if(!is_array($var))$_POST[$key]=array($var);
			}
		}

		//checked
		if(empty($_POST['check'])){
			if($_POST['mode']=='show'){
				die('<script>alert("请勾选要修改的项！");window.location.href="/contentarea/index/show/oid/'.intval($_POST['oid'][0]).'/pid/'.$_POST['pid'][0].'";</script>');
			}else{
				die('<script>alert("请勾选要修改的项！");window.location.href="/contentarea/index/index/oid/'.intval($_POST['oid'][0]).'";</script>');
			}
		}
		
		foreach($_POST['check'] as $key => $aid){
			$array=explode(',',$aid);
			$aid=$array[0];
			$array[1]=$array[1]?$array[1]:0;
			$order=$this->_getParam('mode')!='ajax'?$array[1]:0;
			
			$row = $this->_table->fetchRow("aid = '$aid'");
			
			if($_POST['caption']){
				if(!$_POST['caption'][$order]){
					die('<script>alert("属性名不能为空值，请检查！");window.location.href="/contentarea/index/index/oid/'.intval($_POST['oid'][0]).'";</script>');
				}
				$row->caption = trim($_POST['caption'][$order]);
			}
			
			if($_POST['pid'][$order]!= $row->pid &&  !$row->pid){
				if(!$_POST['value'][$order])continue;
				$data= array(
					'oid' => $row->oid,
					'caption' => $row->caption,
					'name' => $row->name,
					'info' =>  $row->info,
					'pid' => $_POST['pid'][$order],
					'max_length' => $row->max_length,
					'type' => $row->type,
					'value' =>$_POST['value'][$order]
					);	
				if (false === $this->_table->insert($data)) {
					die('<script>alert("'.$this->_table->error.'");window.location.href="/contentarea/index/show/oid/'.intval($_POST['oid'][0]).'/pid/'.$_POST['pid'][0].'";</script>');					
				}				
			}else{
				if($_POST['pid'])$row->pid = $_POST['pid'][$order];
				if($_POST['info'])$row->info = $_POST['info'][$order];
				if($_POST['max_length'])$row->max_length = intval($_POST['max_length'][$order]);
				if($_POST['type'])$row->type = intval($_POST['type'][$order]);
				if($_POST['value'])$row->value = $_POST['value'][$order];
				$row->save();
			}
		}
		if($this->_getParam('mode')=='ajax')
			die('OK!');
		elseif($_POST['mode']=='show')
			$this->flash('修改成功!', '/contentarea/index/show/oid/'.trim($_POST['ids']).'/pid/'.$_POST['pid'][0]);
		else
			$this->flash('修改成功!', '/contentarea/index/index/oid/'.intval($_POST['oid'][0]));
	}

	/**
	 * 前台编辑模板时的操作
	 */
	public function showAction(){		
		$id = trim($this->_getParam('oid',''));
		$pid = trim($this->_getParam('pid',''));
		if(!$id){
			$this->flash('参数错误', '/object/objects/index/type/manual');		
			return false;
		}
		if($pid){
			$add=" AND (pid='')";	
			$add1=" AND (pid='$pid')";	
			$data = $this->_table->fetchAll("oid = '$id'".$add, 'aid')->toArray();
			$data1 = $this->_table->fetchAll("oid = '$id'".$add1, 'aid')->toArray();
			if(!$data){	$data= $data1;}
			if(!$data1){
				foreach($data as $key =>$var){
					$data[$key]['value']='';
					$data[$key]['pid']=$pid;
				}
			}
			if($data && $data1){
				foreach($data as $key =>$var){
					$data[$key]['value']='';
					$data[$key]['pid']=$pid;
					foreach($data1 as $k =>$v){
						if($v['name']== $var['name'])unset($data[$key]);					
					}					
				}
				$data= array_merge($data, $data1);
			}
		}else{
			$add=" AND (pid='')";
			$data = $this->_table->fetchAll("oid = '$id'".$add, 'aid')->toArray();	
		}
		foreach($data as $key =>$var)$data[$key]['value']= htmlspecialchars($data[$key]['value']);
		$this->view->headLink()->appendStylesheet('/styles/module_edit.css');
		$this->view->layout()->disableLayout();
		$this->getResponse()->clearBody();
		$this->view->data = $data;
		$this->view->ids=$id;		

		$db=$this->_table->getAdapter();		
		$where = $db->quoteInto('oid = ?', $id );
		$order = 'oid ASC';
		$this->_table = new Objects();
		$info = $this->_table->fetchRow($where, $order);
		if ($info) {	
		    $this->view->info = $info->toArray();
		} else {
            $this->view->info = array();
		}
		$this->view->channel= $this->channel;
	}

	public function __call($method, $args){       
		exit;
	}

}
?>