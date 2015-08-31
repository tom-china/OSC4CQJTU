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
class StatController extends SimpleController {
	public function __construct(){
		parent::__construct();
		if(!session('?admin')){
			$this->redirect('Main/index');
		}
	}	
	
	public function index(){
		$this->redirect('Main/dashboard');
	}

	public function repairer(){
		//EXCEL导出
		if(I('get.action') == 'export'){
			$goods_list = $this->getStatList(ACTION_NAME,true);
			if(empty($goods_list)){
				$this->error('没有搜索结果，无法导出数据');
			}
			$this->goods_export($goods_list);
		}	
		$this->assignAdmin();
		$this->display('admin-table-repairer');
	}
	
	public function doctor(){
		//EXCEL导出
		if(I('get.action') == 'export'){
			$goods_list = $this->getStatList(ACTION_NAME,true);
			if(empty($goods_list)){
				$this->error('没有搜索结果，无法导出数据');
			}
			$this->goods_export($goods_list);
		}		   	
		$this->assignAdmin();
		$this->display('admin-table-doctor');
	}	

	public function getTable(){
		if(IS_POST AND IS_AJAX){
			$character = I('get.character');
			if(!in_array($character,array('repairer','doctor'))){
				$this->error('非法请求');
			}
			
			$database = M('order');
			$map = $this->getMap();

			$columns = I('post.columns');
			$order = I('post.order');
			
			$order[0]['column'] = $order[0]['column']?$order[0]['column']:0;
			$order[0]['dir'] = $order[0]['dir']?$order[0]['dir']:'asc';

			$list = $database->field($character)->where($map)->group($character)->select();			

			$draw = I('post.draw');
			$recordsTotal = count($database->field($character)->group($character)->select());	
			$recordsFiltered = count($list);
			
			foreach($list as $k=>$v){
				$infos[$k][] = $v[$character];
				$map[$character] = $v[$character];		
				$map['status'] = 0;
				$infos[$k][] = $database->where($map)->count();
				$map['status'] = 1;
				$infos[$k][] = $database->where($map)->count();
				$map['status'] = 2;
				$infos[$k][] = $database->where($map)->count();				
			}

			$this->sortArrByField($infos, $order[0]['column'], $order[0]['dir']); //多维数组按字段排序
			array_slice($infos, I('param.start/d'), I('param.length/d')); //数组截取
		
			echo json_encode(array(
				"draw" => intval($draw),
				"recordsTotal" => intval($recordsTotal),
				"recordsFiltered" => intval($recordsFiltered),
				"data" => $infos?$infos:[]
			),JSON_UNESCAPED_UNICODE);				
		}
	}
	
	private function getStatList($character,$export=false){
		$map = $this->getMap();
		$database = M('order');
		if($export){
			$users = $database->field("{$character}")->where($map)->group("{$character}")->select();
		}else{
			$users = $database->field("{$character}")->where($map)->group("{$character}")->page(I('get.p/d').',25')->select();
		}
		foreach($users as $k=>$v){
			$map[$character] = $v[$character];		
			$map['status'] = 0;
			$list[$k]['todo'] = $database->where($map)->count();
			$map['status'] = 1;
			$list[$k]['doing'] = $database->where($map)->count();
			$map['status'] = 2;
			$list[$k]['done'] = $database->where($map)->count();
			$list[$k][$character] = $v[$character];
		}
		return $list;		
	}
	
	private function assignAdmin(){
		$admin = M('admin')->where('username=:username')->bind(':username',session('admin'))->find(); //获取管理权限范围
		$admin = json_decode($admin['location'],true);
		if(!empty($admin['area']))$this->assign('area',$admin['area']);
		if(!empty($admin['building']))$this->assign('building',$admin['building']);		
	}
	
	private function getMap(){
		$admin = M('admin')->where('username=:username')->bind(':username',session('admin'))->find(); //获取管理权限范围
		$admin = json_decode($admin['location'],true);
		//按区域楼栋搜索
		if(!empty($admin['area']) && !empty($admin['building'])){ //区域+楼栋
			$where['area'] = array('in',$admin['area']);
			$this->assign('area',$admin['area']);
			if(!empty(I('param.area')))$where['area'] = array('in',I('param.area'));
            
			$where['building'] = array('in',$admin['building']);
			$this->assign('building',$admin['building']);
			if(!empty(I('param.building')))$where['building'] = array('in',I('param.building'));

			$where['_logic'] = 'or';
			$map['_complex'] = $where;                
		}
		elseif(!empty($admin['area'])){ //仅区域
			$map['area'] = array('in',$admin['area']);
			$this->assign('area',$admin['area']);
			if(!empty(I('param.area')))$map['area'] = array('in',I('param.area'));
		}
		elseif(!empty($admin['building'])){ //仅楼栋
			$map['building'] = array('in',$admin['building']);
			$this->assign('building',$admin['building']);
			if(!empty(I('param.building')))$map['building'] = array('in',I('param.building'));
		}		
		//按时间搜索
		if(!empty(I('param.startDate')) && !empty(I('param.endDate'))){ //之间
			$map['time'] = array(array('egt',strtotime(I('param.startDate'))),array('elt',strtotime(I('param.endDate').' 23:59:59')));
		}
		elseif(!empty(I('param.startDate'))){ //大于起始日
			$map['time'] = array('egt',strtotime(I('param.startDate')));
		}
		elseif(!empty(I('param.endDate'))){ //小于截止日
			$map['time'] = array('elt',strtotime(I('param.endDate').' 23:59:59'));
		}
		if(!empty(I('param.emerg/d')))$map['emerg'] = I('param.emerg/d');
		if(!empty(I('param.repairer')))$map['repairer'] = array('like','%'.I('param.repairer').'%');
		if(!empty(I('param.doctor')))$map['doctor'] = array('like','%'.I('param.doctor').'%');

		return $map;		
	}	
	
    //导出数据方法
    protected function goods_export($goods_list=array())
    {
        $goods_list = $goods_list;
        $data = array();
        foreach ($goods_list as $k=>$goods_info){
            if(isset($goods_info['doctor']))$data[$k][doctor] = $goods_info['doctor'];
            if(isset($goods_info['repairer']))$data[$k][repairer] = $goods_info['repairer'];
			$data[$k][todo] = $goods_info['todo'];
			$data[$k][doing] = $goods_info['doing'];
			$data[$k][done] = $goods_info['done'];
        }

        foreach ($data as $field=>$v){
            if(isset($v['doctor']) and $field == 'doctor'){
                $headArr[]='管理员';
            }

            if(isset($v['repairer']) and $field == 'repairer'){
                $headArr[]='维修工';
            }

            if($field == 'todo'){
                $headArr[]='未处理';
            }

            if($field == 'doing'){
                $headArr[]='处理中';
            }

            if($field == 'done'){
                $headArr[]='已处理';
            }

        }

        $filename="goods_list";

        $this->getExcel($filename,$headArr,$data);
    }

    private  function getExcel($fileName,$headArr,$data){
        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能import导入
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");

        $date = date("Y_m_d",time());
        $fileName .= "_{$date}.xls";

        //创建PHPExcel对象，注意，不能少了\
        $objPHPExcel = new \PHPExcel();
        $objProps = $objPHPExcel->getProperties();

        //设置表头
        $key = ord("A");
        foreach($headArr as $v){
            $colum = chr($key);
            $objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
            $objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
            $key += 1;
        }

        $column = 2;
        $objActSheet = $objPHPExcel->getActiveSheet();

        foreach($data as $key => $rows){ //行写入
            $span = ord("A");
            foreach($rows as $keyName=>$value){// 列写入
                $j = chr($span);
                $objActSheet->setCellValue($j.$column, $value);
                $span++;
            }
            $column++;
        }

        $fileName = iconv("utf-8", "gb2312", $fileName);
        //重命名表
        //$objPHPExcel->getActiveSheet()->setTitle('test');
        //设置活动单指数到第一个表,所以Excel打开这是第一个表
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); //文件通过浏览器下载
        exit;
    }  	
	
	private function sortArrByField(&$array, $field, $method = 'asc'){
		$fieldArr = array();
		foreach ($array as $k => $v) {
			$fieldArr[$k] = $v[$field];
		}
		$sort = (strtolower($method) == 'asc') ? SORT_ASC : SORT_DESC;
		array_multisort($fieldArr, $sort, $array);
	}	
	
}