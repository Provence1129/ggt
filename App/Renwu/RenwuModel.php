<?php
/**
 * @Copyright (C) 2016.
 * @Description Article
 * @FileName ArticleModel.php
 * @Author Huang.Xiang
 * @Version 1.0.1
 **/

declare(strict_types=1);//strict
namespace App\Renwu;

use \Libs\Tag\Db;
use \Libs\Tag\Page;
use \Libs\Comm\Time;
use \Libs\Comm\Net;
use \Libs\Frame\Conf;
use \App\Article\ArticleModel;

class RenwuModel
{

    private $Db = NULL;         //数据库对象
    const SUCCESS = "success";    // 成功
    const FAIL = "fail";       // 失败
    const ISDEL = 0;            // 删除

    /**
     * @name __construct
     * @desciption 初始化认证
     **/
    public function __construct()
    {
        $this->Db = Db::tag('DB.ADMIN', 'GMY');
    }


    /**
     * @name getDataList
     * @desciption 获取列表
     **/
    public function getList(array $whereArray = [], Page $Page)
    {
        $articleModel = new ArticleModel();

        // 条件处理
        $limit          = $Page -> getLimit();
        $whereString    = 'a.ar_iscommend=0 AND a.ar_status=1 AND a.ar_isdel='.static::ISDEL;
        if(count($whereArray) > 0) foreach($whereArray as $key => $val){
            switch($key){
                // 栏目ID
                case 'ac_id':{
                    $whereString .= ($whereString == ''?'':' AND ').'b.ac_id in ('.$val.')';
                    break;
                }
                case 'ar_title':{
                    $whereString .= ($whereString == ''?'':' AND ').'a.ar_title like \'%'.$val.'%\'';
                    break;
                }
            }
        }

        // 获取数据
        $sql    = 'SELECT SQL_CALC_FOUND_ROWS distinct a.ar_id,b.ac_id,a.au_id,a.ar_title,a.ar_keywords,a.ar_description,a.ar_hits,a.ar_heart,a.ar_comments,a.ar_source,a.ar_status,a.ar_order,a.ar_thumb_img,a.ar_first_time,a.ar_last_time FROM '.$this -> Db -> getTableNameAll('article').' a';
        if(isset($whereArray['ac_id'])) $sql    .= ' LEFT JOIN '.$this -> Db -> getTableNameAll('article_category_relation').' b ON a.ar_id=b.ar_id';
        $sql    .=' WHERE '.$whereString.' ORDER BY a.ar_order ASC, a.ar_id DESC LIMIT '.$limit[0].', '.$limit[1];
        $result = $this -> Db -> getData($sql);

        // 处理标签和图片地址
        $results = [];
        if(is_array($result) && isset($result[0]) && !empty($result[0])){
            foreach($result as $val){
                $val['ar_thumb_img']    = $articleModel->getThumbImgUrl($val['ar_thumb_img']);
                $val['tags']            = $articleModel->getDataListTags([$val]);
                $results[]              = $val;
            }
        }

        // 分页处理
        $totalNum = $this -> Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);
        return $results;
    }



}