<?php
namespace Home\Model;
use Think\Model;
class RankModel extends Model{

   protected $_validate = array(
     array('order','require','工单号必须'),
     array('content','require','内容必须')
   );
   
}
