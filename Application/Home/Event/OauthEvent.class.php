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

namespace Home\Event;
use Think\Controller;
class OauthEvent extends Controller {
	
	public $tablename;
	public $map;
	public $bind;
	
	protected function uc($uid=''){
		require_cache(MODULE_PATH."Conf/uc.php");
		$info = M()->db(1,"mysql://".UC_DBUSER.":".UC_DBPW."@".UC_DBHOST.":3306/".UC_DBNAME)
				   ->table($this->tablename)
				   ->where($this->map)
				   ->bind($this->bind)
				   ->find();
		if(empty($info)){
			return false; 
		}else{
			return $info;
		}         		
	}
}