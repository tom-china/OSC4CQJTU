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
    public function index(){
    	if(!session('?admin'))$this->redirect('Main/index');
    	$database = M('article');
    	$list = $database->order('acid desc')->page(I('get.p/d').',25')->select();
    	$this->assign('list',$list);
    	$count = $database->count();
    	$this->assign('count',$count);
		$page = pagination($count);
		$this->assign('page',$page);
        $this->display('admin-table');
    }	

    public function add(){
    	if(!session('?admin'))$this->redirect('Main/index');
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

    public function edit(){
    	if(!session('?admin'))$this->redirect('Main/index');
    	if(IS_POST){
            $database = D('article');
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
    		$article = M('article')->where('acid=:acid')->bind(':acid',I('get.acid'))->find();
    		$this->assign('article',$article);
    		$this->display('admin-edit');
    	}
    }
    

	//ueditor

    public function ueditor(){
    	if(!session('?admin'))$this->redirect('Main/index');
        $data = new \Org\Util\Ueditor();
        echo $data->output();
    }

    public function del(){
        if(!session('?admin'))$this->redirect('Main/index');
    	$database = M('article');
    	if(IS_AJAX){
    		if(is_array(I('post.id'))){
				$count = count(I('post.uid'));
    			$order = implode(',', I('post.id'));
    			$res = $database->delete($order);
    			if($res){
					$this->success(intval($res).'/'.$count.'条记录删除成功');
				}else{
					$this->error(intval($res).'/'.$count.'条记录删除成功');
				}				
    		}else{
    			$res = $database->delete(I('post.id'));
    			if($res){
					$this->success(I('post.id').'删除成功');
				}else{
					$this->error(I('post.id').'删除失败');
				}				
    		}

    	}    	
    }
}
