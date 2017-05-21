<?php
/**
 * Page Class
 * @package    Page
 * @author  Adrian·Tian
 * @date    2009-07-12
 */
 
class Page
{
	 protected $style;
	 protected $nums;
	 protected $showPage;
	/**
	 * 功能：构造函数
	 * 参数：配置数组
	 *  $params = array('style','nums')
	 */
	 public function __construct($params = array()){
		$this->nums     = empty($params['nums'])?10:$params['nums'];
		$this->showPage = empty($params['show_page'])?10:$params['show_page'];
	 }
	 
	 /**
	 * 功能：分页函数
	 * 参数：配置数组
	 *  $params = array('url','pages','page','nums','show_page','gets')
	 */
	 public function page_html($params = array())
	 {
		$html = '';
		$prev = '<a href="javascript:void(0);"> < </a>';
		$next = '<a href="javascript:void(0);"> > </a>';
		$first = '<a href="javascript:void(0);">首页</a>';
		$last  = '<a href="javascript:void(0);">尾页</a>';
		if(!empty($params['nums'])){
			$this->nums = $params['nums'];
		}
		if($params['pages']!=0&&$params['pages']!=1){
			if(!isset($params['gets'])){
				$params['gets'] = '';
			}
			//查询传递过来的参数是否有page这个参数
			$index = strpos($params['gets'],'page');
			if($index!==false){
				$index = strpos($params['gets'],'?');
				$params['gets'] = substr($params['gets'],$index+1);
			}
			
			$style = '';
			if($params['page']!=1){
				$prev  = '<a href="'.$params['url'].'?page='.($params['page']-1).'&'.$params['gets'].'"> < </a>';
				$first = '<a href="'.$params['url'].'?page=1&'.$params['gets'].'"> 首页 </a>';
			}
			if($params['page']!=$params['pages']){
				$next = '<a href="'.$params['url'].'?page='.($params['page']+1).'&'.$params['gets'].'"> > </a>';
				$last = '<a href="'.$params['url'].'?page='.$params['pages'].'&'.$params['gets'].'"> 尾页 </a>';
			}
			//判断当前总页数是否小于设定的显示页数
			if($params['pages']<$this->showPage){
				$i = 1;
				$e = $params['pages'];
			}else{
				//开始页分界点
				$m = $params['pages'] - $this->showPage + 1;
				//开始页
				$i = ($params['page']<$m?$params['page']:$m);
				//结束页
				$e = $i + $this->showPage - 1;
			}
			for(;$i<=$e;++$i){
				$gets = "page={$i}&{$params['gets']}";
				if($i==$params['page']){
					$style .= "<span>{$i}</span>";
				}else{
					$style .= "<a href='{$params['url']}?{$gets}'>{$i}</a>";
				}
			}
			$html = "{$first}{$prev}{$style}{$next}{$last}";
		}
		return $html;
	 }
	 
	/**
	 * 功能：分页函数
	 * 参数：配置数组
	 *  $params = array('url','pages','page','nums','show_page','gets')
	 */
	 public function page_html_web($params = array())
	 {
		$html = '';
		$prev = '<a href="javascript:void(0);"> < </a>';
		$next = '<a href="javascript:void(0);"> > </a>';
		$first = '<a href="javascript:void(0);">首页</a>';
		$last  = '<a href="javascript:void(0);">尾页</a>';
		if(!empty($params['nums'])){
			$this->nums = $params['nums'];
		}
		if($params['pages']!=0&&$params['pages']!=1){
			if(!isset($params['gets'])){
				$params['gets'] = '';
			}
			//查询传递过来的参数是否有page这个参数
			$index = strpos($params['gets'],'page');
			if($index!==false){
				$index = strpos($params['gets'],'?');
				$params['gets'] = substr($params['gets'],$index+1);
			}
			
			$style = '';
			if($params['page']!=1){
				$prev  = '<a href="'.$params['url'].'?page='.($params['page']-1).'&'.$params['gets'].'"> < </a>';
				$first = '<a href="'.$params['url'].'?page=1&'.$params['gets'].'"> 首页 </a>';
			}
			if($params['page']!=$params['pages']){
				$next = '<a href="'.$params['url'].'?page='.($params['page']+1).'&'.$params['gets'].'"> > </a>';
				$last = '<a href="'.$params['url'].'?page='.$params['pages'].'&'.$params['gets'].'"> 尾页 </a>';
			}
			//判断当前总页数是否小于设定的显示页数
			if($params['pages']<$this->showPage){
				$i = 1;
				$e = $params['pages'];
			}else{
				//开始页分界点
				$m = $params['pages'] - $this->showPage + 1;
				//开始页
				$i = ($params['page']<$m?$params['page']:$m);
				//结束页
				$e = $i + $this->showPage - 1;
			}
			for(;$i<=$e;++$i){
				$gets = "page={$i}&{$params['gets']}";
				if($i==$params['page']){
					$style .= "<a href='javascript:void(0);' class='page_namber_SHUZ_ON'>{$i}</a>";
				}else{
					$style .= "<a href='{$params['url']}?{$gets}'>{$i}</a>";
				}
			}
			$html = "{$first}{$prev}{$style}{$next}{$last}";
		}
		return $html;
	 }
	 //析构函数
	 public function __destruct(){}		 	
}
?>