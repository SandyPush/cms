<?php
require_once 'Zend/Db/Table.php';

class ObjectsRevision extends Zend_Db_Table
{
	protected $_db;
    protected $_name = 'obj_revision';
    protected $_primary = 'oid';
    public $error = '';

	// object types
    const OBJ_TYPE_TEMPLATE = 0;
    const OBJ_TYPE_ARTICLE = 1;
    const OBJ_TYPE_FOCUS   = 2;
    const OBJ_TYPE_PAGE    = 3;

    // object OWNEDs
    const OBJ_OWNED_CHANNEL = 0;
    const OBJ_OWNED_TEMPLATE = 1;
    const OBJ_OWNED_PAGE    = 2;

    public function __construct($db) 
	{
		$this->_db= $db;	
	}

    public function insert(array $data)
    {
        if (empty($data['oid'])) {
            $this->error = 'error!';
            return false;
        }                
        return parent::insert($data);
    }  
   
    public function getContent($oid,$uid,$date)   
    {
 		$sql="SELECT content FROM  obj_revision  WHERE oid='$oid' AND uid='$uid' AND updatetime='$date' ORDER BY updatetime DESC";
		
		$result= $this->_db->fetchOne($sql);
		return $result;
    }

	public function getList($pub, $oid, $pageid = 0, $type = self::OBJ_TYPE_PAGE, $owned = self::OBJ_OWNED_PAGE, $limit=10)
	{	
		if(!$oid)return;			
		$flag= 0;
		
        if($owned == self::OBJ_OWNED_PAGE) {
            $where = "oid = $oid AND type = $type AND pageid = $pageid AND owned = " . self::OBJ_OWNED_PAGE;	
			$sql="SELECT *,(SELECT realname FROM users WHERE uid=a.uid) username FROM  obj_revision a WHERE $where ORDER BY a.updatetime DESC LIMIT $limit";
			if(!$result= $this->_db->fetchAll($sql))$flag++;			
        }
		
		if ($owned == self::OBJ_OWNED_TEMPLATE  || $flag) {
			//require_once MODULES_PATH . 'page/models/Page.php';
			//$page_table = new PageTable($this->_db);			
			//$page = $page_table->find($pageid)->current();
			//$template_id= $page->template;
			$template = $pub->getTemplate();            
            $template_id = $template->id;				       
            $where ="oid = $oid AND pageid = '$template_id' AND owned = " . self::OBJ_OWNED_TEMPLATE;		
			$sql="SELECT *,(SELECT realname FROM users WHERE uid=a.uid) username FROM  obj_revision a WHERE $where ORDER BY a.updatetime DESC LIMIT $limit";		
			if(!$result= $this->_db->fetchAll($sql))$flag++;			
		}
		
        if ($owned == self::OBJ_OWNED_CHANNEL || $flag) {
            $where = "oid = $oid AND pageid= 0 AND owned = " . self::OBJ_OWNED_CHANNEL;	
			$sql="SELECT *,(SELECT realname FROM users WHERE uid=a.uid) username FROM  obj_revision a WHERE $where ORDER BY a.updatetime DESC LIMIT $limit";
			if(!$result= $this->_db->fetchAll($sql))$flag++;			
        }		
		if(!$result)return;
		if(count($result)== $limit){
			$arr= end($result);			
			$sql="DELETE FROM obj_revision WHERE updatetime< '$arr[updatetime]'";			
			$this->_db->query($sql);
		}
		return json_encode($result);
	}
}