<?php
namespace Home\Model;
use Think\Model;
class UserModel extends Model{

   protected $_validate = array(
     array('area','require','参数非法'),
	 array('location','require','参数非法')
   );
   
}
