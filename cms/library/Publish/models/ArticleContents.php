<?php
require_once 'Zend/Db/Table.php';

class ArticleContentsTable extends Zend_Db_Table
{
    protected $_name = 'article_contents';
    protected $_primary = 'aid';

}