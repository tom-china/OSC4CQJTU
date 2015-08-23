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

namespace Home\Controller;
use Think\Controller;
class ListController extends SimpleController {
	//报修列表
    public function index(){
    	$database = M('order');
        $status = I('get.status');
        $emerg = I('get.emerg');
    	if(isset($status)&&$status!='')$map['status'] = I('get.status');
        if(isset($emerg)&&$emerg!='')$map['emerg'] = I('get.emerg');
    	$list = $database->where($map)->order('time desc')->page(I('get.p').',25')->select();
    	$this->assign('list',$list);
    	$count = $database->where($map)->count();
        $show = pagination($count);
    	$this->assign('page',$show);
        $this->display('list');
    }	

	//工单搜索
    public function search(){
    	$database = M('order');
        $status = I('get.status');
        $emerg = I('get.emerg');
        if(isset($status)&&$status!='')$map['status'] = I('get.status');
        if(isset($emerg)&&$emerg!='')$map['emerg'] = I('get.emerg');  
        $where['order'] = array('like','%'.I('param.order').'%');
        $where['user'] = I('param.order');
        $where['doctor'] = I('param.order');
        $where['repairer'] = I('param.order');    
        $where['_logic'] = 'or';
        $map['_complex'] = $where;         
    	$list = $database->where($map)->order('time desc')->page(I('get.p').',25')->select();
    	$this->assign('list',$list);
    	$count = $database->where($map)->count();
        $show = pagination($count);
    	$this->assign('page',$show);    	
    	$this->display('search-list');
    }
}