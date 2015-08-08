<?php
namespace Home\Controller;
use Think\Controller;
class ArticleController extends SimpleController {
	//公告列表
    public function index(){
    	$database = M('article');
    	$list = $database->order('time desc')->page(I('get.p').',5')->select();
    	$count = $database->count();
    	$page = pagination($count);
    	$this->assign('page',$page);
    	$this->assign('list',$list);
        $this->display('blog');
    }
	
	//公告详情
    public function show(){
     	$database = M('article');
    	$article = $database->where('acid=:acid')->bind(':acid',I('get.acid/d'))->find();
    	$this->assign('article',$article);
    	$view = $database->where('acid=:acid')->bind(':acid',I('get.acid/d'))->setInc('view',1,60);
        $this->display('show');   	
    }
}