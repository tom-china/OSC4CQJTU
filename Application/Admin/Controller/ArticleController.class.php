<?php
/*
*    Online Service Center for Chongqing Jiaotong University 
*    Copyright (C) 2015 freyhsiao@gmail.com
*
*    This program is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License, or
*    (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License along
*    with this program; if not, write to the Free Software Foundation, Inc.,
*    51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA. 	
*/

namespace Admin\Controller;
use Think\Controller;
class ArticleController extends SimpleController {
	public function __construct(){
		parent::__construct();
		if(!session('?admin')){
			$this->redirect('Main/index');
		}
	}	
	
	//公告列表
    public function index(){
    	$database = M('article');
    	$list = $database->order('acid desc')->page(I('get.p/d').',25')->select();
    	$this->assign('list',$list);
    	$count = $database->count();
    	$this->assign('count',$count);
		$page = pagination($count);
		$this->assign('page',$page);
        $this->display('admin-table');
    }	
	
	//添加公告
    public function add(){
    	if(IS_POST){
            $database = D('article');
            if (!$database->create()){
                $this->error($database->getError());
            }            
            $data['title'] = I('post.title');
            $data['content'] = I('post.content');
            $data['time'] = time();
            $data['view'] = 0;
            $data['author'] = session('admin');
            $add = $database->strict(true)->data($data)->add();
            if($add){
                $this->success('添加成功',U('Article/index'));
            }else{
                $this->error('添加失败');
            }
    	}else{
            $this->display('admin-add');
    	}
    }
	
	//公告编辑
    public function edit(){
		$database = D('article');
    	if(IS_POST){
            if (!$database->create()){
                $this->error($database->getError());
            }             
    		$data['title'] = I('post.title');
    		$data['content'] = I('post.content');
    		$update = $database->where('acid=:acid')->bind(':acid',I('post.acid'))->save($data);
    		if($update){
    			$this->success('更新成功',U('Article/index'));
    		}else{
    			$this->error('更新失败');
    		}
    	}else{
    		$article = $database->where('acid=:acid')->bind(':acid',I('get.acid'))->find();
    		$this->assign('article',$article);
    		$this->display('admin-edit');
    	}
    }
    
	//加载百度编辑器
    public function ueditor(){
        $data = new \Org\Util\Ueditor();
        echo $data->output();
    }

	//公告删除
    public function del(){
    	if(IS_AJAX and IS_POST){
			$acid = I('post.acid/a');
			$total = count($acid);
			$acid = implode(',', $acid);
			$row = M('article')->delete($acid);
			if($row){
				$this->success($row.'/'.$total.'条记录删除成功');
			}else{
				$this->error($row.'/'.$total.'条记录删除成功');
			}				
    	}    	
    }
}