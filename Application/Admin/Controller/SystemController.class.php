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
class SystemController extends SimpleController {
	public function __construct(){
		parent::__construct();
		if(!session('?admin') or session('right')!=1){
			$this->redirect('Main/index');
		}
	}	
	
    //系统设置
    public function setting(){
        $database = D('setting');
        if(IS_POST){         
            if (!$database->autoCheckToken($_POST)){
                $this->error('令牌验证错误，请刷新后重试');
            } 
            $menu = I('post.config');
            $menu = $menu['menu']['button'];
            foreach($menu as $k=>$v){
                $area[] = $v['name'];
                $building[] = $v['sub_button'];
            }
            $data['key'] = 'area';
            $data['value'] = json_encode($area);
			if(!$database->add($data,array(),true)){
				$this->error('设置（一级地点）保存失败');
			}
            $data['key'] = 'building';
            $data['value'] = json_encode($building);
			if(!$database->add($data,array(),true)){
				$this->error('设置（二级地点）保存失败');
			}
			if(!empty(F('settings')))F('settings',NULL);
			$this->success('设置保存成功');
        }else{
			$global = $database->where("`key`='global'")->find();
			$global = json_decode($global['value'],true);
			$this->assign('global',$global);

			$tips = $database->where("`key`='tips'")->find();
			$tips = json_decode($tips['value'],true);
			$this->assign('tips',$tips);

			$copyright = $database->where("`key`='copyright'")->find();
			$copyright = json_decode($copyright['value'],true);
			$this->assign('copyright',$copyright);       

			$area = $database->where("`key`='area'")->find();
			$area = json_decode($area['value'],true);
			$this->assign('area',$area);

			$building = $database->where("`key`='building'")->find();
			$building = json_decode($building['value'],true);
			$this->assign('building',$building);

			$this->display('admin-setting');			
		}
    }
	
	//全局设置
    public function setGlobal(){
        if(IS_POST){
        	$database = M('setting');
            if (!$database->autoCheckToken($_POST)){
                $this->error('令牌验证错误，请刷新后重试');
            } 
	    	$global = I('post.global');
	    	if(!in_array($global['isopen'],array(true,false))){
				$this->error('参数非法');
			}
	    	if(!in_array($global['allowregister'],array(true,false))){
				$this->error('参数非法');
			}   
            if(!in_array($global['quickreport'],array(true,false))){
				$this->error('参数非法');
			}	
			$data['key'] = 'global';
	    	$data['value'] = json_encode($global);
	    	$add = $database->add($data,array(),true);
	    	if($add){
				if(!empty(F('settings')))F('settings',NULL);
	    		$this->success('设置保存成功');
	    	}else{
	    		$this->error('设置保存失败');
	    	}
        }else{
        	$this->redirect('System/setting');
        }
    }    

	//提示设置
    public function setTips(){
    	if(IS_POST){
    		$database = M('setting');
            if (!$database->autoCheckToken($_POST)){
                $this->error('令牌验证错误，请刷新后重试');
            }     		
    		$tips = I('post.tips');
    		$data['key'] = 'tips';
    		$data['value'] = json_encode($tips);
    		$add = $database->data($data)->filter('strip_tags')->add($data,array(),true);
	    	if($add){
				if(!empty(F('settings')))F('settings',NULL);
	    		$this->success('设置保存成功');
	    	}else{
	    		$this->error('设置保存失败');
	    	}    		
    	}else{
    		$this->redirect('System/setting');
    	}
    }

	//版权设置
    public function setCopyright(){
    	if(IS_POST){
    		$database = M('setting');
            if (!$database->autoCheckToken($_POST)){
                $this->error('令牌验证错误，请刷新后重试');
            }     		
    		$copyright = I('post.copyright');
    		$data['key'] = 'copyright';
    		$data['value'] = json_encode($copyright);
    		$add = $database->data($data)->filter('strip_tags')->add($data,array(),true);
	    	if($add){
				if(!empty(F('settings')))F('settings',NULL);
	    		$this->success('设置保存成功');
	    	}else{
	    		$this->error('设置保存失败');
	    	}    		
    	}else{
    		$this->redirect('System/setting');
    	}
    }
	
	public function log(){
		$list = $this->listFiles(LOG_PATH);
		$log = '';
		if(!empty($list)){
			foreach($list as $v){
				$log .= file_get_contents($v);
			}			
		}
		$this->assign('log',$log);
		$this->display('admin-log');
	}

	public function clean(){
		if(IS_POST){
			if(!empty(I('post.type'))){
				foreach(I('post.type') as $k=>$v){
					switch($v){
						case 'cache':
						$list = $this->listFiles(CACHE_PATH); //项目模板缓存目录
						break;
						case 'log':
						$list = $this->listFiles(LOG_PATH); //应用日志目录
						break;
						case 'temp':
						$list = $this->listFiles(TEMP_PATH); //应用缓存目录
						break;						
						case 'data':
						$list = $this->listFiles(DATA_PATH); //应用数据目录
						break;						
						case 'html':
						$list = $this->listFiles(HTML_PATH); //应用静态缓存目录
						break;
						default:
						$this->error('非法参数');													
					}
					$this->cleanCache($list);
				}
				$this->success('缓存清理完成');
			}			
		}else{
			$this->display('admin-clean');
		}
	}	
	
	private function cleanCache($list){
		foreach($list as $k=>$v){
			if(file_exists($v)){
				unlink($v);
			}			
		}
	}
	
	//列出目录下的所有文件
	private function listFiles( $from = '.'){
		if(! is_dir($from))
			return false;
		
		$files = array();
		$dirs = array( $from);
		while( NULL !== ($dir = array_pop( $dirs)))
		{
			if( $dh = opendir($dir))
			{
				while( false !== ($file = readdir($dh)))
				{
					if( $file == '.' || $file == '..')
						continue;
					$path = $dir . '/' . $file;
					if( is_dir($path))
						$dirs[] = $path;
					else
						$files[] = $path;
				}
				closedir($dh);
			}
		}
		return $files;
	}

}