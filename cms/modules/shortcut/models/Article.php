<?php
require_once 'Zend/Db/Table.php';
class ArticleTable extends Zend_Db_Table{
	protected $_name = 'article';
    protected $_primary = 'aid';

}