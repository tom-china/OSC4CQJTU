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
class SimpleController extends Controller {
    /**
     * 空操作处理
     * @return custom or 404 page
     */
    public function _empty() {
        $this->redirect('Main/index');
    }	

    //统计
    public function __construct(){
        parent::__construct();
        if(session('?admin') && session('?security')){
			
			//安全验证
			if(sha1(get_client_ip().$_SERVER['HTTP_USER_AGENT']) != session('security')){
				session(null);
				$this->redirect('Main/index');
			}

			$database = M('order');

<<<<<<< HEAD
        $map['status'] = 0;
        $count = $database->cache(true,60)->where($map)->count();
        $this->assign('countTodo',$count); 

        $map['status'] = 1;
        $count = $database->cache(true,60)->where($map)->count();
        $this->assign('countDoing',$count); 

        $map['status'] = 2;
        $count = $database->cache(true,60)->where($map)->count();
        $this->assign('countDone',$count);

        $map['time'] = array('gt',strtotime(date('Y-m-d')));
        $map['status'] = array('neq',-1);//隐藏已取消报修的
        $count = $database->cache(true,60)->where($map)->count();
        $this->assign('countToday',$count);                      
=======
			$admin = M('admin')->where('username=:username')->bind(':username',session('admin'))->find();
			$admin = json_decode($admin['location'],true);
			if(!empty($admin['area']) && !empty($admin['building'])){
				$where['area'] = array('in',$admin['area']);
				$where['building'] = array('in',$admin['building']);
				$where['_logic'] = 'or';
				$map['_complex'] = $where;
			}
			elseif(!empty($admin['area']))$map['area'] = array('in',$admin['area']);
			elseif(!empty($admin['building']))$map['building'] = array('in',$admin['building']);

			$map['status'] = 0;//待处理
			$count = $database->where($map)->count();
			$this->assign('countTodo',$count); 

			$map['status'] = 1;//处理中
			$count = $database->where($map)->count();
			$this->assign('countDoing',$count); 

			$map['status'] = 2;//已处理
			$count = $database->where($map)->count();
			$this->assign('countDone',$count);
			
			//今日有效报修
			$map['time'] = array('gt',strtotime(date('Y-m-d')));
			$map['status'] = array('neq',-1);//隐藏已取消报修的
			$count = $database->where($map)->count();
			$this->assign('countToday',$count);                      
>>>>>>> origin/beta
        }
		
    }
}