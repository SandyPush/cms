<?php
require_once 'Zend/Db/Table.php';

class SetTable extends Zend_Db_Table
{
	const TABLE = 'video_set';//主数据表
    protected $_name = 'video_set';
    protected $_primary = 'sid';
	protected $_identity = 0;
    public $error = '';
    
    public function __construct($db=NULL)
    {
    	if(isset($db))
    	{
    		Zend_Db_Table_Abstract::setDefaultAdapter($db);
    		$this->_db = $db;
    	}
    }


    public function insert(array $data)
    {
		if ($data['title'] === '') {
			$this->error = '标题不能为空';
			return false;
		}

        return parent::insert($data);
    }
    
    public function edit(array $data, $where)
    {
		if ($data['title'] === '') {
			$this->error = '标题不能为空';
		}
                
        return parent::update($data, $where);
    }    
    
	public function disable($sid)
	{
		return $this->_db->query('UPDATE video_set SET status = 0 WHERE sid = ?', $sid);
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
    
    public function fetch($keyName,$count = 20, $offset = 0)
    {
        $rows = $this->_db->fetchAll(
            $this->_db->limit(
                'SELECT * FROM video_set WHERE status >= 1 '.$keyName.' ORDER BY sid DESC',
                $count,
                $offset
            )
        );

        return $rows;
    }
	

	public function ls($params, &$count)
	{
        // default params
        $limit		= empty($params['limit']) ? 20 : (int) $params['limit'];
        $offset		= empty($params['offset']) ? 0 : (int) $params['offset'];
        $orderby	= empty($params['orderby']) ? 'p.pid ASC' : $params['orderby'] . ' DESC';
        $count		= empty($params['count']) ? 0 : 1;
        $params['status'] = isset($params['status']) ? $params['status'] : 1;
        
        unset($params['orderby'], $params['limit'], $params['offset'], $params['count']);
        
        // build sql
        $sql  = 'SELECT {FIELDS} FROM video_set a';
        $fields = 'DISTINCT a.*';

	if (isset($params['cid']) && $params['cid'] == 0) {
	    unset($params['cid']);
	}

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
        $sql_count = str_replace('{FIELDS}', 'COUNT(DISTINCT a.vid)', $sql);        
        $sql .= " ORDER BY $orderby LIMIT $offset, $limit";
        $sql  = str_replace('{FIELDS}', $fields, $sql);

        $rows = array();
		$ids = array();
        $query = $this->_db->query($sql);
        while ($row = $query->fetch()) {
			$vid = $row['vid'];
			$row['cover'] = Util::getPhotoPath($row['cover'], 's', Zend_Registry::get('channel_config')->url->photos);
            $rows[$vid] = $row;
			$ids[] = $vid;
        }        

        // return
        $count = $count ? $this->_db->fetchOne($sql_count) : 0;
        
        return $rows;
	}

	public function setCover($aid, $pid = 0)
	{
		if (!$pid) {
			$pid = $this->_db->fetchOne('SELECT pid FROM video_index WHERE vid = ? AND status = 1 ORDER BY pid ASC', $aid);
		}

		if (!$pid) {
			return false;
		}

		$this->_db->query('UPDATE video_set SET cover = ? WHERE vid = ?', array($pid, $aid));
	}

	public function updateTags($vid, $tags)
	{
		if (!$vid) {
			return false;
		}

		$tags = is_array($tags) ? $tags : preg_split('/[\s,;]+/i', $tags);
		$tags_quoted = array();
		foreach ($tags as $tag) {
			$tag = trim($tag);
			if ($tag === '') {
				continue;
			}

			$this->_db->query('INSERT IGNORE INTO tags (name) VALUES (' . $this->_db->quote($tag) . ')');
			$tags_quoted[] = $this->_db->quote($tag);
		}
		
		if ($tags_quoted) {
			$tids = $this->_db->fetchCol('SELECT tagid FROM tags WHERE name IN (' . join(', ', $tags_quoted) . ')');
			$this->_db->query('DELETE FROM video_tags WHERE vid = ?', $vid);
			foreach ($tids as $tid) {
				$this->_db->insert('video_tags', array('vid' => $vid, 'tid' => $tid));
			}
		}

		return true;
	}

	public function getTags($vid)
	{
		if (!$vid) {
			return false;
		}

		return $this->_db->fetchCol('SELECT t.name FROM tags t, video_tags at WHERE at.vid = ? AND t.tagid = at.tid', $vid);
	}

	public function getUrl($timestamp, $vid) {
		return sprintf('/video/%s/%d.html', date("Y-m-d", $timestamp), $vid);
	}
}
