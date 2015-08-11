<?php
namespace Home\Model;
use Think\Model;
class ArticleModel extends Model{

   protected $_validate = array(
     array('title','require','标题必须'),
     array('content','require','内容必须')
   );
   
}
