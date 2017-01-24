<?php
/**
 * Created by PhpStorm.
 * User: zhangjunyu
 * Date: 2016/10/15
 * Time: 0:28
 */

namespace App\User;
use \App\Pub\Common;
use Libs\Comm\From;
use Libs\Comm\Http;
use \Libs\Comm\Time;
use \Libs\Comm\Valid;
use Libs\Frame\Url;
use \Libs\Tag\Db;
use \Libs\Comm\Net;
use \Libs\Tag\Page;
use \App\Auth\MyAuth;
use \Libs\Frame\Conf;

class MyZjzxData
{
    private $Db         = NULL;         //数据库对象
    const SUCCESS       = "success";    // 成功
    const FAIL          = "fail";       // 失败
    /**
     * @name __construct
     * @desciption 初始化认证
     **/
    public function __construct(){
        $this -> Db         = Db::tag('DB.USER', 'GMY');
    }

    /**
     * @name  保存数据
     */
    public function save($data){
        $dbKey = null;
        $dbVal = null;
        foreach ($data as $key => $item){
            $dbKey .= $key.",";
            $dbVal .= "'".(is_string($item) ? addslashes($item) : $item)."',";
        }
        $dbKey = substr($dbKey,0,strlen($dbKey)-1);
        $dbVal = substr($dbVal,0,strlen($dbVal)-1);
        $sql = 'insert into '.$this -> Db -> getTableNameAll('zjzx').'('.$dbKey.') values ('.$dbVal.')';
        $result_id      = $this->Db->getDataId($sql);
        if($result_id){
            return static::SUCCESS;
        }
        return static::FAIL;
    }

    public function getListPage($Page){
        $limit  = $Page -> getLimit();
        $sql = "select SQL_CALC_FOUND_ROWS zjzx.* from ".$this -> Db -> getTableNameAll('zjzx')." as zjzx where zjzx.published = 1 
        order by zjzx.id DESC";
        $sql .= " LIMIT ".$limit[0].",".$limit[1];
        $results = $this -> Db -> getData($sql);
        $totalNum = $this -> Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);
        // 返回数据
        return isset($results[0]) && !empty($results[0]) ? $results : [];

    }
}