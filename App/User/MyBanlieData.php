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

class MyBanlieData
{
    private $Db         = NULL;         //数据库对象
    public $trains = null;            //国内班列还是国际班列
    public $banlieCategory = null;   //班列线路
    public $railway = null ;          //铁路局
    public $operationCycle = null;   //开行周期
    public $banlieTitle = null;
    /**
     * @name __construct
     * @desciption 初始化认证
     **/
    public function __construct(){
        $this -> Db         = Db::tag('DB.USER', 'GMY');
        $this->get_GETData();
    }

    /**
     *@name get_GETData 得到页面上传过来的参数
     */
    public function get_GETData(){
        $this->trains = From::val("trains"); //服务类别
        $this->trains = !empty($this->trains) ? $this->trains : 1;
        if(empty($this->trains)){
            $trains = $this->geTrains();
            $this->trains = $trains[1]['id'];
        }
        $this->banlieCategory = From::val("banliecategory"); //班列线路
        $this->railway = From::val("railway"); //铁路局
        $this->operationCycle = From::val("operationcycle"); //开行周期
        $this->banlieTitle = From::val("banlietitle"); //搜索的班列名称
    }

    /**
     * @name getOptions 处理页面上的筛选
     * @return array
     */
    public function getOptions(){
        $trains = null;
        $banlieCategory =  null;
        $railway =  null;
        $operationCycle =  null;
        if($this->trains){
            $item = $this->geTrains($this->trains);
            $itemInfo = array_shift($item);
            $trains['url'] = "/banlie/?trains=".$itemInfo['id'];
            $trains['name'] = $itemInfo['category'];
            $trains['category'] = "服务类别：";
        }
        if($this->banlieCategory){
            $item = $this->getBanlieCategory($this->banlieCategory);
            $itemInfo = array_shift($item);
            $url = $itemInfo['url'];
            $url = preg_replace("/\&banliecategory=[0-9]+/","",$url);
            $banlieCategory['url'] = $url;
            $banlieCategory['name'] = $itemInfo['category'];
            $banlieCategory['category'] = "班列线路：";
        }
        if($this->railway){
            $item = $this->getRailway($this->railway);
            $itemInfo = array_shift($item);
            $url = $itemInfo['url'];
            $url = preg_replace("/\&railway=[0-9]+/","",$url);
            $railway['url'] = $url;
            $railway['name'] = $itemInfo['railway'];
            $railway['category'] = "铁路局：";
        }
        if($this->operationCycle){
            $item = $this->getOperationCycle($this->operationCycle);
            $itemInfo = array_shift($item);
            $url = $itemInfo['url'];
            $url = preg_replace("/\&operationcycle=[0-9]+/","",$url);
            $operationCycle['url'] = $url;
            $operationCycle['name'] = $itemInfo['operation_cycle'];
            $operationCycle['category'] = "开行周期：";
        }
        $row = array($trains,$banlieCategory,$railway,$operationCycle);
        return $row;
    }

    /**
     * @name geTrains  得到班列服务种类 国内，国外
     * @param null $id
     * @return array
     */
    public function geTrains($id = null){
        $sql = "select * from ".$this -> Db -> getTableNameAll('trains_server_category');
        if($id != null){
            $sql .= " where id =".$id;
        }
        $items = $this->Db->getData($sql);
        $rows = array();
        foreach ($items as $key => $item){
            $item['url'] = "/banlie/?trains=".$item['id'];
            $rows[] = $item;
        }
        return $rows;
    }

    /**
     * @name getBanlieData  获取班列信息的详情
     * @param $id 班列的ID
     * @return mixed
     * @throws Exception
     */
    public function getBanlieData($id){
        $sql = "select SQL_CALC_FOUND_ROWS info.*,banliecategory.category as banlieleibie,trains.category as fuwuzhonglei from ".$this -> Db -> getTableNameAll('trains_information'). " as info".//查询列车信息
            " left join ".$this -> Db -> getTableNameAll('trains_category')." as banliecategory on banliecategory.id = info.category".//查询班列线路
            " left join ".$this -> Db -> getTableNameAll('trains_server_category')." as trains on trains.id = banliecategory.trains_server_category".//查询国内|国际
            " left join ".$this -> Db -> getTableNameAll('trains_railway')." as railway on railway.id = info.railway_out".//查询铁路局
            " left join ".$this -> Db -> getTableNameAll('trains_operation_cycle')." as operation_cycle on operation_cycle.id = info.operation_cycle";//查询开行周期
        if($id){
            $sql .=" where info.id=".$id;
            $results = $this->Db->getDataOne($sql);
            return $results;
        }else{
            throw new Exception('没有找到对应的ID');
        }
    }

    /**
     * @name getBanlieListPage 获取班列信息的列表
     * @param Page $Page
     * @return array
     */
    public function getBanlieListPage(Page $Page){
        
        $limit  = $Page -> getLimit();

        $sql = "select SQL_CALC_FOUND_ROWS info.* from ".$this -> Db -> getTableNameAll('trains_information'). " as info".//查询列车信息
            " left join ".$this -> Db -> getTableNameAll('trains_category')." as banliecategory on banliecategory.id = info.category".//查询车次
            " left join ".$this -> Db -> getTableNameAll('trains_server_category')." as trains on trains.id = banliecategory.trains_server_category".//查询国内|国际
            " left join ".$this -> Db -> getTableNameAll('trains_railway')." as railway on railway.id = info.railway_out".//查询铁路局
            " left join ".$this -> Db -> getTableNameAll('trains_operation_cycle')." as operation_cycle on operation_cycle.id = info.operation_cycle";//查询开行周期
        if($this->trains){
            $sql .=" where trains.id=".$this->trains;
        }
        if($this->banlieCategory){
            $sql .=" and banliecategory.id=".$this->banlieCategory;
        }
        if($this->railway){
            $sql .=" and railway.id=".$this->railway;
        }
        if($this->operationCycle){
            $sql .=" and operation_cycle.id=".$this->operationCycle;
        }
        if($this->banlieTitle){
            $sql .=" and banliecategory.category like '%$this->banlieTitle%'";
        }
        $sql .= " LIMIT ".$limit[0].",".$limit[1];
        $results = $this -> Db -> getData($sql);
        $totalNum = $this -> Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);
        // 返回数据
        return isset($results[0]) && !empty($results[0]) ? $results : [];
    }

    /**
     * @name getUrl 显示在页面上的url处理
     * @param null $parameter  需要替换的参数名
     * @param null $value  需要替换的值
     * @return string
     */
    public function getUrl($parameter = null,$value = null){
        $path = Http::getUrlPath();
        if(!strpos($path,"/?")&&!strpos($path,"/index.php?")){
            $path = $path."?trains=".$this->trains;
        }
        $replace = $parameter."=".$value;
        if(strpos($path,$parameter)){//替换已有参数
            $path = preg_replace("/".$parameter."=[0-9]+/",$replace,$path);
            $path = preg_replace("/pg=[0-9]*/","pg=1",$path);//参数更改后分页为第一页
        }else{//如果没有现有参数就增加参数
            $path =$path."&".$replace;
        }
        $path = str_replace("/&","/?",$path);
        return $path;
    }

    /**
     * @name 得到班列线路的种类
     * @param null $id 班列线路的ID
     * @return array
     */
    public function getBanlieCategory($id = null){
        $sql = "select * from ".$this -> Db -> getTableNameAll('trains_category')." where trains_server_category = ".$this->trains;
        if($id != null){
            $sql .= " and id =".$id;
        }
        $items = $this->Db->getData($sql);
        $rows = array();
        foreach ($items as $key => $item){
            $path = $this->getUrl("banliecategory",$item['id']);
            $item['url'] = $path;
            $rows[] = $item;
        }
        return $rows;
    }

    /**
     * @name getOperationCycle 得到开行周期
     * @param null $id 开行周期的ID
     * @return array
     */
    public function getOperationCycle($id = null){
        $sql = "select * from ".$this -> Db -> getTableNameAll('trains_operation_cycle')." where trains_server_category = ".$this->trains;
        if($id != null){
            $sql .= " and id =".$id;
        }
        $items = $this->Db->getData($sql);
        $rows = array();;
        foreach ($items as $key => $item){
            $path = $this->getUrl("operationcycle",$item['id']);
            $item['url'] = $path;
            $rows[$item['id']] = $item;
        }
        return $rows;
    }

    /**
     * @name getRailway  获取铁路局
     * @param null $id   铁路局ID
     * @return array|null
     */
    public function getRailway($id = null){
        if($this->trains != 1){
            return null;
        }
        $sql = "select * from ".$this -> Db -> getTableNameAll('trains_railway');
        if($id != null){
            $sql .= " where id =".$id;
        }
        $items = $this->Db->getData($sql);
        $rows = array();;
        foreach ($items as $key => $item){
            $path = $this->getUrl("railway",$item['id']);
            $item['url'] = $path;
            $rows[$item['id']] = $item;
        }
        return $rows;
    }

    /**
     * @name getDaili 获取国外代理
     * @return array
     */
    public function getDaili(){
        $sql = "select country,name,web from ".$this -> Db -> getTableNameAll('daili'). " LIMIT 0,4";
        $results = $this -> Db -> getData($sql);
        return isset($results[0]) && !empty($results[0]) ? $results : [];
    }

}