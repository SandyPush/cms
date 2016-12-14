<?php
require_once 'Zend/Db/Table.php';

class UsersTable extends Zend_Db_Table
{
	const TABLE='users';
	protected $_name = 'users';
	protected $_primary = 'uid';
	public $error = '';
    public function __construct($db=NULL)
    {
    	parent::__construct();
    	if(isset($db))
    	{
    		Zend_Db_Table_Abstract::setDefaultAdapter($db);
    		$this->_db=$db;
    	}
    }
	public function setDb($db)
	{
		$this->_db=$db;
	}
    /**
     * Inserts a new row.
     *
     * @param  array  $data  Column-value pairs.
     * @return mixed         The primary key of the row inserted.
     */	
    public function insert(array $data)
    {
        if (!isset($data['username']) OR !isset($data['realname'])) {
            $this->error = '填写不完整';
            return false;
        }    	
        
        if (!Util::isValidEmail($data['email'])) {
            $this->error = '电子邮件地址不正确';
            return false;
        }
        
        if ($this->findWithName($data['username'])) {
            $this->error = '名为' . $data['username'] . '的用户已存在';
            return false;
        }
        $data['salt'] = substr(md5(uniqid()), 0, 6);
        !isset($data['password']) OR $data['password'] = md5($data['password']);
        return parent::insert($data);
    }
    
    public function edit(array $data, $where)
    {
//        if ($data['realname'] == '') {
//            $this->error = '真实姓名不能为空';
//            return false;
//        }
        
//        if (!Util::isValidEmail($data['email'])) {
//            $this->error = '电子邮件地址不正确';
//            return false;
//        }
        
//        if (isset($data['password'])) {
//            $data['password'] = md5($data['password']);
//        }
        
        return parent::update($data, $where);
    }
    
    public function listAll($start=NULL,$offset=NULL)
    {
        $db = $this->getAdapter();
        $sql='SELECT u.*, g.name AS groupname FROM users u LEFT JOIN user_groups g ON gid = usergroup ORDER BY gid ASC, uid';
        if(isset($start))$sql.=" LIMIT ".$start;
        if(isset($offset))$sql.=",".$offset;
        $users = $db->fetchAll($sql);
        
        return $users;
    }
    public static function usernameList($db)
    {
    	$sql="SELECT * FROM users";
    	$query=$db->query($sql);
    	$users=array();
    	while($row=$query->fetch())
    	{
    		$users[$row['uid']]=$row['realname'];
    	}
    	return $users;
    }
    public function getCount()
    {
        $db = $this->getAdapter();
    	return $db->fetchOne("SELECT count(uid) FROM ".self::TABLE."");
    }
    /**
     * find user by name
     *
     * @param string $username
     * @return Zend_Db_Table_Rowset Object
     */
    public function findWithName($username)
    {
        $db = $this->getAdapter();
        $where = $db->quoteInto("username = ?", $username);
        
        return $this->fetchRow($where);
    }

	public function checkField($db){
		$sql= "DESCRIBE users weibo";
		$weibo= $db->fetchRow($sql);		
		if(empty($weibo)){		
			$db->query("ALTER TABLE `users` ADD `weibo` VARCHAR( 100 ) NOT NULL");
		}
		$sql= "DESCRIBE users nickname";
		$nickname= $db->fetchRow($sql);		
		if(empty($nickname)){		
			$db->query("ALTER TABLE `users` ADD `nickname` VARCHAR( 30 ) NOT NULL");
		}
	}

	static public function getWeibo($db, $uid){
		$sql= "select weibo from `users` where uid='$uid'";
		$weibo= $db->fetchOne($sql);		
		return 	$weibo;
	}

	static public function getNickname($db, $uid){		
		$sql= "select nickname from `users` where uid='$uid'";
		$db->query('set names utf8');	
		$nickname= $db->fetchOne($sql);		
		return 	$nickname;
	}
}