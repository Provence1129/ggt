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

class MyKouanData
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
     * @param $cid 分类ID
     * @param $Page 分页
     * @return array
     */
    public function getListPage($cid,$Page){
        $limit  = $Page -> getLimit();
        $sql = "select SQL_CALC_FOUND_ROWS art.*,c.ac_parent_id as cid from ".$this -> Db -> getTableNameAll('article')." as art 
        inner join ".$this -> Db -> getTableNameAll('article_category_relation')." as b on art.ar_id = b.ar_id 
        inner join ".$this -> Db -> getTableNameAll('article_category')." as c on c.ac_id = b.ac_id
        where c.ac_id in($cid) AND art.ar_status=1 AND art.ar_isdel=0 order by art.ar_last_time DESC";
        $sql .= " LIMIT ".$limit[0].",".$limit[1];
        $results = $this -> Db -> getData($sql);
        $totalNum = $this -> Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);
        $rows = array();
        foreach ($results as $key => $item){
            $keywords = explode(",",$item["ar_keywords"]);
            if(!$item["ar_img_num"]){
                $item["ar_img_num"] = "/Static/images/defaule_pic.jpg";
            }
            $item["ar_keywords"] = $keywords;
            $item['ar_last_time'] = date("Y-m-d",$item['ar_last_time']);
            $rows[] = $item;
        }
        // 返回数据
        return isset($rows[0]) && !empty($rows[0]) ? $rows : [];

    }

    /**
     * @param Page $Page
     * @param array $whereArray 搜索分类
     * @return array
     */
    public function getDataList($categories,$search,$Page){
        // 条件处理
        $limit          = $Page -> getLimit();
        $sql = 'SELECT SQL_CALC_FOUND_ROWS kouan.* FROM '.$this -> Db -> getTableNameAll('kouan').' as  kouan ';
        if($categories){
            $sql .= "where kouan.categories in ($categories)";
        }
        if($search){
            $sql .= " and (kouan.title_cn like '%$search%'";
            $sql .= " or kouan.title_en like '%$search%'";
            $sql .= " or kouan.title_ru like '%$search%')";
        }
        $sql .=' ORDER BY ordering DESC, id DESC LIMIT '.$limit[0].', '.$limit[1];
        $result = $this -> Db -> getData($sql);
		$items = null;
		foreach($result as $key => $item){
			$description = mb_substr($item['description'],0,150);
			if(strlen($item['description'])>150){
				$description .= "...";
			}
			$item['description'] = $description;
			$items[] = $item;
		}
        $totalNum = $this -> Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);
        return is_array($items) && isset($items[0]) && !empty($items[0]) ? $items : [];
    }

    /**
     * @param $cid'  分类ID
     */
    public function getChildrenCategoires($cid){//
        $sql = "select ac_id from ".$this -> Db -> getTableNameAll('article_category')." where ac_parent_id = ".$cid;
        $results = $this -> Db -> getData($sql);
        $ac_id = array();
        $ac_id[0] = $cid;
        foreach ($results as $key => $item){
            $ac_id[] = $item["ac_id"];
        }
        $ac_id = implode(",",$ac_id);
        return $ac_id;
    }

    /**
     * @name getDate 得到详细数据
     * @param $id
     * @return mixed
     */
    public function getData($id){
        $sql = 'SELECT kouan.* FROM '.$this -> Db -> getTableNameAll('kouan').' as  kouan where kouan.id ='.$id;
        $result = $this -> Db -> getDataOne($sql);
        return $result;
    }
}