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

class MyDlsqData
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
     * @param Page $Page
     * @param array $whereArray 搜索分类
     * @return array
     */
    public function getDataList(Page $Page, array $whereArray){
        // 条件处理
        $limit          = $Page -> getLimit();
        $whereString = '';
        if(count($whereArray) > 0) foreach($whereArray as $key => $val){
            switch($key){
                case 'sq_category':{
                    $whereString .= ($whereString == ''?'':' AND ').'(sq_category = \''.addslashes($val).'\')';
                    break;
                }
                case 'sq_region':{
                    $whereString .= ($whereString == ''?'':' AND ').'(region = \''.addslashes($val).'\')';
                    break;
                }

                case 'sq_company_title':{
                    $whereString .= ($whereString == ''?'':' AND ').'(sq_company_title like \'%'.addslashes($val).'%\')';
                    break;
                }
            }
        }
        if(strlen($whereString) > 0) $whereString = ' WHERE '.$whereString;
        $sql = 'SELECT SQL_CALC_FOUND_ROWS dlsq.* FROM '.$this -> Db -> getTableNameAll('dlsq').' as  dlsq '.$whereString.' ORDER BY id DESC, ordering DESC LIMIT '.$limit[0].', '.$limit[1];
        $result = $this -> Db -> getData($sql);
        $totalNum = $this -> Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);
        return is_array($result) && isset($result[0]) && !empty($result[0]) ? $result : [];
    }

    /**
     * @name 得到商圈的类别
     * @return array
     */
    public function getSqcat(){
        $sql = 'SELECT DISTINCT sq_category FROM '.$this -> Db -> getTableNameAll('dlsq');
        $result = $this -> Db -> getData($sql);
        return is_array($result) && isset($result[0]) && !empty($result[0]) ? $result : [];
    }

    /**
     * @name  得到商圈的地区
     * @return array
     */
    public function getRegion(){
        $sql = 'SELECT DISTINCT region FROM '.$this -> Db -> getTableNameAll('dlsq');
        $result = $this -> Db -> getData($sql);
        return is_array($result) && isset($result[0]) && !empty($result[0]) ? $result : [];
    }

    /**
     * @name getDate 得到详细数据
     * @param $id
     * @return mixed
     */
    public function getData($id){
        $sql = 'SELECT Dlsq.* FROM '.$this -> Db -> getTableNameAll('dlsq').' as  Dlsq where Dlsq.id ='.$id;
        $result = $this -> Db -> getDataOne($sql);
        return $result;
    }
}