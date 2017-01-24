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

class MyDailiData
{
    private $Db         = NULL;         //数据库对象
    /**
     * @name __construct
     * @desciption 初始化认证
     **/
    public function __construct(){
        $this -> Db         = Db::tag('DB.USER', 'GMY');
    }

    /**
     * @name getArea  得到国外代理的分类
     * @return array
     */
    public function getArea(){
        $sql = "select * from ".$this -> Db -> getTableNameAll('daili_area');
        $results = $this -> Db -> getData($sql);
        return isset($results[0]) && !empty($results[0]) ? $results : [];
    }

    /**
     * @name getListPage 得到国外代理的列表
     * @param $areaId 分类ID
     * @param Page $Page
     * @return array|null
     */
    public function getListPage($whereArray,Page $Page){//
        
        $limit  = $Page -> getLimit();
        $whereString = '';
        if(count($whereArray) > 0) foreach($whereArray as $key => $val){
            switch($key){
                case 'area':{
                    $whereString .= ($whereString == ''?'':' AND ').'(daili.area_id = \''.addslashes($val).'\')';
                    break;
                }
                case 'name':{
                    $whereString .= ($whereString == ''?'':' AND ').'(daili.name like \'%'.addslashes($val).'%\')';
                    break;
                }
            }
        }
        if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString;
        $sql = "select SQL_CALC_FOUND_ROWS daili.* from ".$this -> Db -> getTableNameAll('daili'). " as daili".
            " inner join ".$this -> Db -> getTableNameAll('daili_area')." as area on area.id = daili.area_id";
        $sql .= " ".$whereString." order by id desc LIMIT ".$limit[0].",".$limit[1];
        $results = $this -> Db -> getData($sql);
        $rows = null;
        foreach ($results as $key => $item){
            $item['service'] = substr($item['service'],0,300);//取主要服务中300个字符
            $rows[] = $item;
        }
        $totalNum = $this -> Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);
        return isset($rows[0]) && !empty($rows[0]) ? $rows : [];
    }
}