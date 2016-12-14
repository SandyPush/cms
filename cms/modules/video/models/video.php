<?php
require_once 'Zend/Db/Table.php';

class VideoTable extends Zend_Db_Table
{
    protected $_name = 'video';
    protected $_primary = 'pid';
	protected $_identity = 0;
	protected $_db;
    public $error = '';
    
    public function __construct($db=NULL)
    {
    	if(isset($db)) {
    		Zend_Db_Table_Abstract::setDefaultAdapter($db);
    		$this->_db=$db;
    	}
    }


    public function insert(array $data)
    {
		//

        return parent::insert($data);
    }
    
    public function edit(array $data, $where)
    {
		if (isset($data['status'])) {
			$this->_db->query('UPDATE video SET status = ? WHERE ' . $where, $data['status']);
			unset($data['status']);
		}

		if (!$data) {
			return true;
		}

        return parent::update($data, $where);
    }
    
    public function findWithName($name)   
    {
        $db = $this->getChannelDbAdapter();
        $where = $db->quoteInto("name = ?", $name);
        
        return $this->fetchRow($where);
    }

    public function count($where = 'status >= 1')
    {
        $select = $this->select()->from($this->_name, 'COUNT(*)');
        if ($where) {
            $select->where($where);
        }
        
        return $this->_db->fetchOne($select);
    }
    
	public function fetch($keyName,$count = 20, $offset = 0) {
        $rows = $this->_db->fetchAll(
            $this->_db->limit(
                'SELECT * FROM video WHERE status >= 1 '.$keyName.' ORDER BY uploadtime DESC',
                $count,
                $offset
            )
        );
        return $rows;
	}

    /**
     * 新图片入库
     *
     * @param array $data 图片信息数组
     * @return integer
     */
    public function create($data)
    { 
        list($title,$extName)=explode(".",$data['name']);
        $video = array (
            'sid' => $data['sid'] ? $data['sid'] : 0,
            'filename' => $data['name'],
            'title' => $title,
            'uid' => $data['uid'],
            
        );
      
        $this->_db->insert('video', $video);        
        $vid = $this->_db->lastInsertId();
        $this->_db->query("UPDATE video_set SET videos = videos + 1 WHERE sid = ?", $video['sid']);
         
        // photo file
		$config = Zend_Registry::get('channel_config');
        $original = Util::getVideoPath($vid, 'o', $config->path->videos.$_POST['coverLogo']."/",$data['ext']);
        $dir = dirname($original);
        @mkdir($dir, 0775, 1);
        
        if (false == move_uploaded_file($data['tmp_name'], $original)) {
            return false;
        }
        
        return $vid;
    }

	public function getList($sid)
	{
        $rows = array();
		$ids = array();
        $query = $this->_db->query('SELECT * FROM video_set WHERE sid = ? AND status = 1', $sid);
        while ($row = $query->fetch()) {
			$pid = $row['pid'];
            $rows[$pid] = $row;
			$ids[] = $pid;
        }
        
		// fill photo info
		if ($ids) {
			$query = $this->_db->query('SELECT * FROM video WHERE pid IN (' . join(',', $ids). ')');
			while ($row = $query->fetch()) {
				$pid = $row['pid'];
				$rows[$pid] = array_merge($rows[$pid], $row);
			}
		}

		return $rows;
	}
}
