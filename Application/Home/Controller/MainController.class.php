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
class MainController extends SimpleController {
	//首页
    public function index(){
    	//公告
    	$notice = M('article')->order('acid desc')->limit(5)->select();
        $this->assign('notice',$notice);
    	//统计
    	$stat['today'] = M('order')->cache(true,5)->where('time>:time')->bind(':time',strtotime(date("Y-m-d")))->count();
		$stat['todo'] = M('order')->cache(true,5)->where('status=0')->count();
    	$stat['doing'] = M('order')->cache(true,5)->where('status=1')->count();
    	$stat['done'] = M('order')->cache(true,5)->where('status=2')->count();
        $this->assign('stat',$stat);
    	//最新报修
        //$map['status'] = array('neq',-1);//不显示已取消工单
    	$list = M('order')->cache(true,5)->where($map)->order('time desc')->limit(5)->select();
        $this->assign('list',$list);
        $this->display('main');
    }

	//自动刷新
    public function refresh(){
    	$list = M('order')->cache(true,5)->order('time desc')->limit(5)->select();
    	$html = '';
    	foreach($list as $k=>$v){
            $html .= ($v['emerg']==1)?'<tr class="am-active">':'<tr>';
            $html .= '<td>'.$v['order'].'</td>';//工单号
            $building = ($v['emerg']==1)?$v['description']:(empty($v['building'])?'-':building($v['area'],$v['building']));
            $html .= '<td class="am-show-md-up">'.$building.'</td>';//报修楼栋
            $html .= '<td class="am-show-md-up">'.$v['location'].'</td>';//报修寝室
            $html .= '<td class="am-show-md-up">'.date("Y年m月d日",$v['time']).'</td>';//报修时间
            $html .= '<td>'.status($v['status']).'</td>';//维修状态
            $html .= '<td><a href="'.U('Report/detail',array('order'=>$v['order'])).'">详细&raquo;</a></td>';
            $html .= '</tr>';
    	}
    	echo $html;
    }

}