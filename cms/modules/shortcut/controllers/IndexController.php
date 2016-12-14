<?php
/** @see BaseController */
require_once 'BaseController.php';
require_once MODULES_PATH . 'shortcut/models/Article.php';
require_once MODULES_PATH . 'focus/models/Focus.php';
require_once MODULES_PATH . 'page/models/Page.php';

class Shortcut_IndexController extends BaseController
{
	
	private $channel;
	private $user;	
	private $preview_url;

	protected $_db; 	
	protected $_article_table;
	protected $_focus_table;
	protected $_page_table;

	private $pattern= array(
			'news'=> '/^http:\/\/([a-z]*)\.titan24.com\/([a-z]*\/)*([0-9]*\-[0-9]*\-[0-9]*\/)([0-9]*)(_[0-9]*)?(.html)$/i',
			'page'=> '/^http:\/\/([a-z]*)\.titan24.com([\S]*)/i',			
		);

	public function init(){
		$user= Zend_Session::namespaceGet('user');		
		$this->channel= $user['channel'];
		$this->user= $user['username'];
		$this->_db = $this->getChannelDbAdapter();	 
		$this->_article_table = new ArticleTable();
		$this->_focus_table = new FocusTable();
		$this->_page_table = new PageTable($this->_db);
		$this->preview_url= $this->getChannelConfig($this->channel)->url->workplace; 		
	}

	public function indexAction(){
		$url= trim($_GET['surl']);
		if(!$url)$this->error();
		if(trim($url,'/')=='http://titan24.com')$url='http://news.titan24.com';		
		if(preg_match($this->pattern['news'], $url, $buffer)){			
			if($buffer[1] && $buffer[4] && $buffer[6]='.html'){
				if($buffer[1]!= $this->channel)$this->error('Sorry, you do not have permission of the channel or have already changed the channel. ');	
				if (!$article = $this->_article_table->find($buffer[4])->current())$this->error('Sorry, the article does not exist!');
				$this->location('','/contents/article/edit/id/'.$buffer[4]);
			}else{
				$this->error();
			}
		}elseif(preg_match($this->pattern['page'], $url, $buffer)){	
			if($buffer[1] && $buffer[2]){
				if($buffer[1]!= $this->channel)$this->error('Sorry, you do not have permission of the channel or have already changed the channel. ');	
				if ($this->_focus_table->checkUrlExist($buffer[2]) || $this->_focus_table->checkUrlExist($buffer[2].'index.html') || $this->_focus_table->checkUrlExist($buffer[2].'/index.html'))
				{
					//$PreviewUrl= str_replace('.com','.net.cn',$buffer[0]);
					$PreviewUrl= trim($this->preview_url,'/').$buffer[2];
					$this->location('', $PreviewUrl);
				}
				elseif($this->_page_table->checkUrlExist($buffer[2]) || $this->_page_table->checkUrlExist($buffer[2].'index.html') || $this->_page_table->checkUrlExist($buffer[2].'/index.html'))
				{
					//$PreviewUrl= str_replace('.com','.net.cn',$buffer[0]);	
					//if($this->channel=='www')$PreviewUrl= str_replace($buffer[1],'www1', $PreviewUrl);
					$PreviewUrl= trim($this->preview_url,'/').$buffer[2];
					$this->location('', $PreviewUrl);
				}else{
					$this->error();
				}
			}elseif($buffer[1] && !$buffer[2]){
				if($buffer[1]=='news'){					
					if($this->channel=='www'){
						$this->location('', 'http://www1.titan24.net.cn/news/index.html');	
					}else{
						$this->error('Sorry, you do not have permission of the channel or have already changed the channel. ');
					}
				}
				if($buffer[1]!= $this->channel)$this->error('Sorry, you do not have permission of the channel or have already changed the channel. ');
				//$PreviewUrl= str_replace('.com','.net.cn',$buffer[0]);	
				//if($this->channel=='www')$PreviewUrl= str_replace($buffer[1],'www1', $PreviewUrl);
				$PreviewUrl= trim($this->preview_url,'/').$buffer[2];
				$this->location('', $PreviewUrl);				
			}else{
				$this->error();
			}
		}else{
			$this->error();
		}
		exit;
	}

	public function location($str='', $url='', $flag=1){
		 echo "<script>";
		 if($str)echo "alert('{$str}');";
		 if($url)echo "window.location.href='{$url}'";
		 echo "</script>"; 
		 if($flag)exit;
	}

	public function error($str='', $flag=1){
		 echo "<script>";
		 if($str)echo "alert('{$str}');";
		 echo "window.opener=null;";
		 echo "window.open('','_self');";	
		 echo "window.close();";
		 echo "</script>"; 
		 if($flag)exit;		
	}
}