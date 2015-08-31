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
class TaskController extends SimpleController {
	public function __construct(){
		parent::__construct();
		if(!session('?admin')){
			$this->redirect('Main/index');
		}
	}

    public function index(){
    	$this->redirect('Main/dashboard');
    }	
	
	//待处理
    public function todo(){   	
    	if(IS_AJAX && IS_POST){
    		$this->updateOrder(I('post.order/a'),ACTION_NAME);
    	}else{
			$database = M('order');
            $map = $this->getMap(0);
            //EXCEL导出
            if(I('get.action') == 'export'){
				$this->export($database,$map);
            }

	    	$this->display('admin-table-todo');
    	}
    }
	
	//处理中
    public function doing(){
    	if(IS_AJAX && IS_POST){
    		$this->updateOrder(I('post.order/a'),ACTION_NAME);
    	}else{  
			$database = M('order');
            $map = $this->getMap(1);
            //EXCEL导出
            if(I('get.action') == 'export'){
                $this->export($database,$map);
            }
   	
	    	$this->display('admin-table-doing');
  		}
    }

	//已处理
    public function done(){
    	
    	if(IS_AJAX && IS_POST){
    		$this->updateOrder(I('post.order/a'),ACTION_NAME);
    	}else{
			$database = M('order');
			$map = $this->getMap(2);			
            //EXCEL导出
            if(I('get.action') == 'export'){
                $this->export($database,$map);
            }
  	
	    	$this->display('admin-table-done');
    	}
    } 
	
	//数据删除
    public function del(){
    	if(IS_AJAX && IS_POST){
			$order = I('post.order/a');
			$total = count($order);
			$order = implode(',', $order);
			$row = M('order')->delete($order);
			if($row){
				$this->success($row.'/'.$total.'条记录删除成功');
			}else{
				$this->error($row.'/'.$total.'条记录删除成功');
			}				
    	}
    } 

	private function export($database,$map){
		$goods_list = $database->where($map)->order('time desc')->select();
		if(empty($goods_list)){
			$this->error('没有搜索结果，无法导出数据');
		}
		$this->goods_export($goods_list);	
	}
	
    //导出数据方法
    protected function goods_export($goods_list=array())
    {
        $goods_list = $goods_list;
        $data = array();
        foreach ($goods_list as $k=>$goods_info){
            $data[$k][order] = $goods_info['order'];
            $data[$k][area] = area($goods_info['area']);
            $data[$k][building] = building($goods_info['area'],$goods_info['building']);
            $data[$k][location] = $goods_info['location'];
            $data[$k][good]  = $goods_info['good'];
            $data[$k][description]  = $goods_info['description'];
            $data[$k][user]  = $goods_info['user'];
            $data[$k][time] = date('Y/m/d H:i:s',$goods_info['time']);
            $data[$k][dotime] = date('Y/m/d H:i:s',$goods_info['dotime']);
            $data[$k][donetime] = date('Y/m/d H:i:s',$goods_info['donetime']);
            $data[$k][canceltime] = date('Y/m/d H:i:s',$goods_info['canceltime']);
            $data[$k][status] = status($goods_info['status']);
            $data[$k][emerg] = $goods_info['emerg'];
            $data[$k][doctor] = $goods_info['doctor'];
            $data[$k][repairer] = $goods_info['repairer'];
        }

        foreach ($data as $field=>$v){
            if($field == 'order'){
                $headArr[]='工单号';
            }

            if($field == 'area'){
                $headArr[]='区域';
            }

            if($field == 'building'){
                $headArr[]='楼栋';
            }

            if($field == 'location'){
                $headArr[]='地点';
            }

            if($field == 'good'){
                $headArr[]='物品';
            }

            if($field == 'description'){
                $headArr[]='描述';
            }

            if($field == 'user'){
                $headArr[]='用户';
            }
            if($field == 'time'){
                $headArr[]='报修时间';
            }

            if($field == 'dotime'){
                $headArr[]='确认时间';
            }

            if($field == 'donetime'){
                $headArr[]='完成时间';
            }

            if($field == 'canceltime'){
                $headArr[]='取消时间';
            }

            if($field == 'status'){
                $headArr[]='维修状态';
            }

            if($field == 'emerg'){
                $headArr[]='是否紧急';
            } 

            if($field == 'doctor'){
                $headArr[]='管理员';
            }

            if($field == 'repairer'){
                $headArr[]='维修工';
            } 

        }

        $filename="goods_list";

        $this->getExcel($filename,$headArr,$data);
    }

    private function getExcel($fileName,$headArr,$data){
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

	private function updateOrder($order,$action){
		$database = M('order');
		$total = count($order);
		$success = 0;
		foreach($order as $k=>$v){
			switch($action){
				case 'todo':
					$database->status = '0';
					$database->donetime = '';
					$database->dotime = '';	
					$database->doctor = session('admin'); //记录操作管理员
					$database->repairer = '';
				break;
				case 'doing':
					$database->status = '1';
					$database->dotime = time(); 
					$database->donetime = '';
					$database->doctor = session('admin'); //记录操作管理员
					if(!empty(I('post.repairer')))$database->repairer = I('post.repairer'); //记录维修工人					
				break;
				case 'done':
					$database->status = '2';
					$database->donetime = time();  
					$database->doctor = session('admin'); //记录操作管理员
					if(!empty(I('post.repairer')))$database->repairer = I('post.repairer'); //记录维修工人							
				break;					
			}
			$row = $database->lock(true)->where('`order`=:order')->bind(':order',$v)->save();
			if($row){
				$success++;
			}else{
				$error[] = $v;
			}
		}
		if(isset($error)){
			$this->error($success.'/'.$total.'条记录标记成功'.join(',',$error).'标记失败');
		}else{
			$this->success($success.'/'.$total.'条记录标记成功');
		}						
	}
	
	private function getMap($status){
		$admin = M('admin')->where('username=:username')->bind(':username',session('admin'))->find(); //获取管理权限范围
		$admin = json_decode($admin['location'],true);
		//按区域楼栋搜索
		if(!empty($admin['area']) && !empty($admin['building'])){ //区域+楼栋
			$where['area'] = array('in',$admin['area']);
			$this->assign('area',$admin['area']);
			if(!empty(I('get.area')))$where['area'] = array('in',I('get.area'));
            
			$where['building'] = array('in',$admin['building']);
			$this->assign('building',$admin['building']);
			if(!empty(I('get.building')))$where['building'] = array('in',I('get.building'));

			$where['_logic'] = 'or';
			$map['_complex'] = $where;                
		}
		elseif(!empty($admin['area'])){ //仅区域
			$map['area'] = array('in',$admin['area']);
			$this->assign('area',$admin['area']);
			if(!empty(I('get.area')))$map['area'] = array('in',I('get.area'));
		}
		elseif(!empty($admin['building'])){ //仅楼栋
			$map['building'] = array('in',$admin['building']);
			$this->assign('building',$admin['building']);
			if(!empty(I('get.building')))$map['building'] = array('in',I('get.building'));
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
		$map['status'] = $status;
		if(!empty(I('param.emerg/d'))){
			$map['emerg'] = I('param.emerg/d');
		}
		if(!empty(I('param.ordersn'))){
			$map['order'] = array('like','%'.I('param.ordersn').'%');
		}		

		return $map;
	}
	
	public function getTable(){
		if(IS_AJAX and IS_POST){
			$database = M('order');
			$map = $this->getMap(I('get.type'));

			$columns = I('post.columns');
			$order = I('post.order');
			
			$order[0]['column'] = $order[0]['column']?$order[0]['column']:5;
			$order[0]['dir'] = $order[0]['dir']?$order[0]['dir']:'desc';
			$orderby = trim($columns[$order[0]['column']]['name'].' '.$order[0]['dir']);
	
			$list = $database->where($map)->order($orderby)->limit(I('param.start/d'),I('param.length/d'))->select();

			$draw = I('post.draw');
			$recordsTotal = $database->count();	
			$recordsFiltered = $database->where($map)->count();
			foreach($list as $k=>$v){
				$infos[] = array(
					'<input class="ids" type="checkbox" name="order[]" value="'.$v['order'].'"/>',
					'<a href="'.U('Home/Report/detail',array('order'=>$v['order'])).'" target="_blank">'.$v['order'].'</a>',
					area($v['area']),
					building($v['area'],$v['building']),
					$v['location'],
					date('Y-m-d',$v['time']),
					$v['user'],
					$v['good'],
					$v['description']?'有':'无',
					$v['doctor'],
					$v['repairer']
				);
			}
			
			echo json_encode(array(
				"draw" => intval($draw),
				"recordsTotal" => intval($recordsTotal),
				"recordsFiltered" => intval($recordsFiltered),
				"data" => $infos?$infos:[]
			),JSON_UNESCAPED_UNICODE);	
		}
	}
}