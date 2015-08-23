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
class SimpleController extends Controller {
    /**
     * 空操作处理
     * @return custom or 404 page
     */
    public function _empty() {
        $this->redirect('Main/index');
    }

    //站点信息
    public function __construct(){
        parent::__construct();

		//站点信息		
		if(empty(F('settings'))){
			foreach (M('setting')->select() as $key=>$value){
				$settings[$value['key']] = json_decode($value['value'],true);
			}	
			F('settings',$settings);			
		}
			
		//是否开启站点
        if(F('settings')['global']['isopen']=='false')$this->error('站点已经关闭，请稍后访问~');
		 
		//加载版权信息
        $this->assign('copyright',F('settings')['copyright']);   
    }        
}