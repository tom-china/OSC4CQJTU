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

namespace Admin\Model;
use Think\Model;
class AdminModel extends Model{

	protected $insertFields = array('username','password','salt','right','location');
	protected $updateFields = array('password','salt','lastip','lasttime','right','location');	
	
	protected $_validate = array(
		array('verify','require','验证码必须'),
		array('username','require','用户名必须'), 
		array('password','6,20','密码长度不正确',0,'length')
	);
   
}
