<?php
require_once 'Zend/Db/Table.php';

class PhotosTable extends Zend_Db_Table
{
    protected $_name = 'photos';
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
			$this->_db->query('UPDATE photos_index SET status = ? WHERE ' . $where, $data['status']);
			$this->_db->query("UPDATE albums SET photos = photos - 1 WHERE aid = ?", $data['aid']);
            unset($data['status'],$data['aid']);
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
    
	public function fetch($params, &$count) {
        // default params
        $limit = empty($params['limit']) ? 20 : (int) $params['limit'];
        $offset = empty($params['offset']) ? 0 : (int) $params['offset'];
        $orderby = empty($params['orderby']) ? 'p.pid ASC' : $params['orderby'];
        $count = empty($params['count']) ? 0 : 1;
        $params['status'] = isset($params['status']) ? $params['status'] : 1;
        
        unset($params['orderby'], $params['limit'], $params['offset'], $params['count']);
        
        // build sql
        $sql  = 'SELECT {FIELDS} FROM photos_index p';
        $fields = 'DISTINCT p.*';

        foreach ($params as $k => $v) {
            $param  = $k;
            if (is_array($v)) {
                $param .= " IN ('" . join("', '", $v) . "')";
            } elseif (preg_match('/^\s*?(!=|in)/i', $v)) {
                $param .= ' ' . $v;
            } else {
                $param .= ' = ' . $this->_db->quote($v);
            }
            
            $query_params[] = $param;
        }
        
        // ...
        $sql .= $query_params ? ' WHERE ' . join(' AND ', $query_params) : '';
        $sql_count = str_replace('{FIELDS}', 'COUNT(DISTINCT p.pid)', $sql);        
        $sql .= " ORDER BY $orderby LIMIT $offset, $limit";
        $sql  = str_replace('{FIELDS}', $fields, $sql);

        $rows = array();
		$ids = array();
        $query = $this->_db->query($sql);
        while ($row = $query->fetch()) {
			$pid = $row['pid'];
            $row['square_image'] = Util::getPhotoPath($pid, 's', Zend_Registry::get('channel_config')->url->photos);
            $rows[$pid] = $row;
			$ids[] = $pid;
        }
        
		// fill photo info
		if ($ids) {
			$query = $this->_db->query('SELECT * FROM photos WHERE pid IN (' . join(',', $ids). ')');
			while ($row = $query->fetch()) {
				$pid = $row['pid'];
				$rows[$pid] = array_merge($rows[$pid], $row);
                $sequence[$pid]=$row['sequence'];
			}
            array_multisort($sequence, SORT_ASC, $rows);
		}
        
        // return
        $count = $count ? $this->_db->fetchOne($sql_count) : 0;
        
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
		$size = getimagesize($data['tmp_name']);

        // insert
		$index = array (
			'aid' => $data['aid'],
			'width' => $size[0],
			'height' => $size[1],
			'status' => 1,
		);

        $this->_db->insert('photos_index', $index);
        $pid = $this->_db->lastInsertId();

        $photo = array (
			'pid' => $pid,
            'title' => $data['title'],
            'intro' => $data['intro'],
        );
        
        $this->_db->insert('photos', $photo);
        $this->_db->query("UPDATE albums SET photos = photos + 1 WHERE aid = ?", $data['aid']);

        // photo file
		$config = Zend_Registry::get('channel_config');

        $original = Util::getPhotoPath($pid, 'o', $config->path->photos);
        
        $dir = dirname($original);
        @mkdir($dir, 0775, 1);
        
        if (false == move_uploaded_file($data['tmp_name'], $original)) {
            return false;
        }
        
        $settings = Zend_Registry::get('settings');
		$size = $settings->albums->photo->size;
        Util::resizeImage($original, Util::getPhotoPath($pid, 's', $config->path->photos), 
            $size->s, $size->s, 'fit', 88);
        
        Util::resizeImage($original, Util::getPhotoPath($pid, 't', $config->path->photos), 
            $size->t, $size->t, 'resize', 88);

        Util::resizeImage($original, Util::getPhotoPath($pid, 'm', $config->path->photos), 
            $size->m, $size->m, 'resize', 88);

        
        return $pid;
    }

	public function getList($aid)
	{
        $rows = array();
		$ids = array();
        $query = $this->_db->query('SELECT * FROM photos_index WHERE aid = ? AND status = 1', $aid);
        while ($row = $query->fetch()) {
			$pid = $row['pid'];
            $row['square_image'] = Util::getPhotoPath($pid, 's', Zend_Registry::get('channel_config')->url->photos);
	    $row['thumbnail_image'] = Util::getPhotoPath($pid, 't', Zend_Registry::get('channel_config')->url->photos);
	    $row['middle_image'] = Util::getPhotoPath($pid, 'm', Zend_Registry::get('channel_config')->url->photos);

            $rows[$pid] = $row;
			$ids[] = $pid;
        }
        
		// fill photo info
		if ($ids) {
			$query = $this->_db->query('SELECT * FROM photos WHERE pid IN (' . join(',', $ids). ')');
			while ($row = $query->fetch()) {
				$pid = $row['pid'];
				$rows[$pid] = array_merge($rows[$pid], $row);
                $sequence[$pid]=$row['sequence'];
			}
            array_multisort($sequence, SORT_ASC, $rows);
		}

		return $rows;
	}
}
