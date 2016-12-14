<?php
require_once 'Zend/Db/Table.php';

class FocusTable extends Zend_Db_Table
{
    protected $_name = 'focus';
    protected $_primary = 'fid';
    public $error = '';
    private $_fields = array(
        'name' => '名称',
        'title' => '标题',
        'description' => '简介',
		'keywords' => '关键词',		
        'url' => '地址',
        'starttime' => '开始时间',
        'endtime' => '结束时间'
    );
    
    public function insert(array $data)
    {
        // starttime, endtime
        foreach (array('name', 'title','keywords','description','starttime', 'endtime') as $field) {
            if ('' === $data[$field]) {
                $this->error = $this->_fields[$field] . ' 不能为空';
                return false;
            }
        }
           
        if ($this->fetchRow("status=1 AND name = '$data[name]'")) {
            $this->error = '已存在以' . $data['name'] . '命名的专题';
            return false;
        }

        if (strtotime($data['starttime'] > strtotime($data['endtime']))) {
            $this->error = '开始时间不能大于结束时间';
            return false;
        }
        
        if (!Util::checkUrl($data['url'])) {
            $this->error = 'url地址不合法，请不要包含styles,scripts,images,img等关键字';
            return false;
        }
                
        /*
        if (!preg_match('/^https?:\/\//', $data['url'])) {
            $data['url'] = 'http://' . $data['url'];
        }
        */
                
        
        return parent::insert($data);
    }
    
    public function edit(array $data, $where)
    {
         // starttime, endtime
         foreach (array('name', 'title','keywords','description','starttime', 'endtime') as $field) {
            if ('' === $data[$field]) {
                $this->error = $this->_fields[$field] . ' 不能为空';
                return false;
            }
        }        
       
        if ($this->fetchRow("name = '$data[name]' AND status=1 AND fid != " . $data['fid'])) {
            $this->error = '已存在以' . $data['name'] . '命名的专题';
            return false;
        }

        if (strtotime($data['starttime'] > strtotime($data['endtime']))) {
            $this->error = '开始时间不能大于结束时间';
            return false;
        }
        
        if (!Util::checkUrl($data['url'])) {
            $this->error = 'url地址不合法，请不要包含styles,scripts,images,img等关键字';
            return false;
        }
                
        /*
        if (!preg_match('/^https?:\/\//', $data['url'])) {
            $data['url'] = 'http://' . $data['url'];
        }
        */
        
        unset($data['fid']);
        
        return parent::update($data, $where);
    }
    
    public function count($where = '')
    {
        $sql="SELECT COUNT(fid) FROM focus f LEFT JOIN users u ON u.uid = f.uid WHERE ".$where;
        return $this->_db->fetchOne($sql);
    }
    
    public function fetch($count = 20, $offset = 0, $where='status>=1')
    {
        $rows = $this->_db->fetchAll(
            $this->_db->limit(
                'select * from (SELECT f.*, (select realname from users where uid = f.uid) realname FROM focus f ) a  WHERE '.$where.' ORDER BY fid DESC',
                $count,
                $offset
            )
        );
        
        return $rows;
    }
    public function delete($fid)
    {
        return parent::update(array('status'=>0), 'fid='.$fid);
    }
    public function checkUrlExist($url,$id=NULL)
    {
        $select = $this->select()->from($this->_name, 'COUNT(*)')->where('status=1 and url=?',$url);
        if(isset($id))$select->where('fid!='.$id);
        return $this->_db->fetchOne($select);
    }
}