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
class ArticleController extends SimpleController {
	//公告列表
    public function index(){
    	$database = M('article');
    	$list = $database->order('time desc')->page(I('get.p/d').',5')->select();
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