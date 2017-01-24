<?php
/**
 * @Copyright (C) 2016.
 * @Description Article
 * @FileName ArticleModel.php
 * @Author Huang.Xiang
 * @Version 1.0.1
 **/

declare(strict_types=1);//strict
namespace App\Article;

use \Libs\Tag\Db;
use \Libs\Tag\Page;
use \Libs\Comm\Time;
use \Libs\Comm\Net;
use \Libs\Frame\Conf;

class ArticleModel
{

    private $Db         = NULL;         //数据库对象
    const SUCCESS       = "success";    // 成功
    const FAIL          = "fail";       // 失败
    const ISDEL         = 0;            // 删除

    /**
     * @name __construct
     * @desciption 初始化认证
     **/
    public function __construct(){
        $this -> Db         = Db::tag('DB.ADMIN', 'GMY');
    }


    /**
     * @name getDataList
     * @desciption 获取列表
     **/
    public function getDataList(array $whereArray = [], Page $Page)
    {
        // 条件处理
        $limit          = $Page -> getLimit();
        $whereString    = 'a.ar_iscommend=0 and a.ar_status=1 and a.ar_isdel='.static::ISDEL;
        if(isset($whereArray['ac_id'])) $whereString .= ' and b.ac_id!=65';
        if(count($whereArray) > 0) foreach($whereArray as $key => $val){
            switch($key){
                // 栏目ID
                case 'ac_id':{
                    if($val > 0) $whereString .= ($whereString == ''?'':' AND ').'b.ac_id in ('.$val.')';
                    break;
                }
                case 'ar_title':{
                    $whereString .= ($whereString == ''?'':' AND ').'(a.ar_title like \'%'.$val.'%\' OR a.ar_keywords like \'%'.$val.'%\')';
                    break;
                }
            }
        }

        // 获取数据
        $sql    = 'SELECT SQL_CALC_FOUND_ROWS distinct a.ar_id,a.au_id,a.ar_title,a.ar_keywords,a.ar_description,a.ar_hits,a.ar_heart,a.ar_comments,a.ar_source,a.ar_status,a.ar_order,a.ar_thumb_img,a.ar_first_time,a.ar_last_time FROM '.$this -> Db -> getTableNameAll('article').' a';
        if(isset($whereArray['ac_id'])) $sql    .= ' LEFT JOIN '.$this -> Db -> getTableNameAll('article_category_relation').' b ON a.ar_id=b.ar_id';
        $sql    .=' WHERE '.$whereString.' ORDER BY a.ar_order ASC, a.ar_id DESC LIMIT '.$limit[0].', '.$limit[1];
        $result = $this -> Db -> getData($sql);
        // 分页处理
        $totalNum = $this -> Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');

        // 处理标签和图片地址
        $results = [];
        if(is_array($result) && isset($result[0]) && !empty($result[0])){
            foreach($result as $val){
                $val['ar_thumb_img']    = $this->getThumbImgUrl($val['ar_thumb_img']);
                $val['tags']            = $this->getDataListTags([$val]);
                $viewUsId = intval($val['au_id']);
                $sql = 'SELECT * FROM '.$this -> Db -> getTableNameAll('adm_user_info').' WHERE au_id=\''.$viewUsId.'\' AND aui_isdel=0';
                $info = $this -> Db->getDataOne($sql);
                if(isset($info['aui_id'])) $val = array_merge($val, $info);
                $results[]              = $val;
            }
        }

        $Page -> setParam('totalNum', $totalNum);
        return $results;
    }


    /**
     * @name getDataListTags
     * @desciption 获取列表关键词
     **/
    public function getDataListTags(array $dataList = [])
    {
        $tags = [];
        foreach($dataList as $val){
            $tag = isset($val['ar_keywords']) && !empty($val['ar_keywords']) ? explode(' ', $val['ar_keywords']) : [];
            $tags = array_merge($tags, $tag);
        }
        $tags = array_unique(array_filter($tags));
        return array_slice($tags, 0, 10);
    }


    /**
     * @name getDataCommendList
     * @desciption 获取推荐列表
     **/
    public function getDataCommendList(array $whereArray = [], int $limitNum = 1)
    {
        // 条件处理
        $whereString    = 'a.ar_iscommend=1 AND a.ar_status AND a.ar_isdel='.static::ISDEL;
        if(count($whereArray) > 0) foreach($whereArray as $key => $val){
            switch($key){
                // 栏目ID
                case 'ac_id':{
                    $whereString .= ($whereString == ''?'':' AND ').'b.ac_id in ('.$val.')';
                    break;
                }
            }
        }

        // 获取数据
        $sql    = 'SELECT SQL_CALC_FOUND_ROWS distinct a.ar_id,a.au_id,a.ar_title,a.ar_keywords,a.ar_description,a.ar_hits,a.ar_heart,a.ar_comments,a.ar_source,a.ar_status,a.ar_order,a.ar_thumb_img,a.ar_first_time,a.ar_last_time FROM '.$this -> Db -> getTableNameAll('article').' a';
        if(isset($whereArray['ac_id'])) $sql    .= ' LEFT JOIN '.$this -> Db -> getTableNameAll('article_category_relation').' b ON a.ar_id=b.ar_id';
        $sql    .=' WHERE '.$whereString.' ORDER BY a.ar_order ASC, a.ar_id DESC LIMIT '.$limitNum;
        $result = $this -> Db -> getData($sql);

        // 处理标签和图片地址
        $results = [];
        if(is_array($result) && isset($result[0]) && !empty($result[0])){
            foreach($result as $val){
                $val['ar_thumb_img']    = $this->getThumbImgUrl($val['ar_thumb_img']);
                $val['tags']            = $this->getDataListTags([$val]);
                $results[]              = $val;
            }
        }

        return $results;
    }


    /**
     * @name getDataHeatList
     * @desciption 获取热门列表
     **/
    public function getDataHeatList(array $whereArray = [], int $limitNum = 1)
    {
        // 条件处理
        $whereString    = 'a.ar_iscommend=0 AND a.ar_status=1 AND a.ar_isdel='.static::ISDEL;
        if(count($whereArray) > 0) foreach($whereArray as $key => $val){
            switch($key){
                // 栏目ID
                case 'ac_id':{
                    $whereString .= ($whereString == ''?'':' AND ').'b.ac_id in ('.$val.')';
                    break;
                }
            }
        }

        // 获取数据
        $sql    = 'SELECT SQL_CALC_FOUND_ROWS distinct a.ar_id,a.au_id,a.ar_title,a.ar_keywords,a.ar_description,a.ar_hits,a.ar_heart,a.ar_comments,a.ar_source, a.ar_status,a.ar_order,a.ar_thumb_img,a.ar_first_time,a.ar_last_time FROM '.$this -> Db -> getTableNameAll('article').' a';
        if(isset($whereArray['ac_id'])) $sql    .= ' LEFT JOIN '.$this -> Db -> getTableNameAll('article_category_relation').' b ON a.ar_id=b.ar_id';
        $sql    .=' WHERE '.$whereString.' ORDER BY a.ar_hits ASC, a.ar_order ASC, a.ar_id DESC LIMIT '.$limitNum;
        $result = $this -> Db -> getData($sql);

        // 处理标签和图片地址
        $results = [];
        if(is_array($result) && isset($result[0]) && !empty($result[0])){
            foreach($result as $val){
                $val['ar_thumb_img']    = $this->getThumbImgUrl($val['ar_thumb_img']);
                $val['tags']            = $this->getDataListTags([$val]);
                $results[]              = $val;
            }
        }

        return $results;
    }


    /**
     * @name getDataInfo
     * @desciption 获取详情
     **/
    public function getDataInfo(array $whereArray = [])
    {
        // 条件处理
        $whereString    = 'a.ar_status=1 AND a.ar_isdel='.static::ISDEL;
        if(count($whereArray) > 0) foreach($whereArray as $key => $val){
            switch($key){
                // 资讯ID
                case 'ar_id':{
                    $whereString .= ($whereString == ''?'':' AND ').'a.ar_id='.$val;
                    break;
                }

                // 栏目ID
                case 'ac_id':{
                    $whereString .= ($whereString == ''?'':' AND ').'b.ac_id in ('.$val.')';
                    break;
                }
            }
        }

        // 获取资讯详情
        $sql        = 'SELECT SQL_CALC_FOUND_ROWS distinct a.ar_id,a.au_id,b.ac_id,a.ar_title,a.ar_keywords,a.ar_description,a.ar_hits,a.ar_heart,a.ar_comments,a.ar_source, a.ar_status,a.ar_order,a.ar_thumb_img,a.ar_first_time,a.ar_last_time FROM '.$this -> Db -> getTableNameAll('article').' a';
        $sql        .= ' LEFT JOIN '.$this -> Db -> getTableNameAll('article_category_relation').' b ON a.ar_id=b.ar_id WHERE '.$whereString;
        $resule     = $this->Db->getDataOne($sql);

        // 获取资讯内容
        if(isset($resule['ar_id'])){
            $infoSql        = 'SELECT ai_content FROM '.$this -> Db -> getTableNameAll('article_info').' WHERE ar_id='.$resule['ar_id'];
            $resuleInfo     = $this->Db->getDataOne($infoSql);

            // 处理 内容、关键词、封面图片
            if(is_array($resule) && !empty($resule)){
                $resule['ar_thumb_img']     = $this->getThumbImgUrl($resule['ar_thumb_img']);
                $resule['tags']             = $this->getDataListTags([$resule]);
                $resule['ar_content']       = isset($resuleInfo['ai_content']) ? $resuleInfo['ai_content'] : '';
            }
            $viewUsId = intval($resule['au_id']);
            $sql = 'SELECT * FROM '.$this -> Db -> getTableNameAll('adm_user_info').' WHERE au_id=\''.$viewUsId.'\' AND aui_isdel=0';
            $info = $this -> Db->getDataOne($sql);
            if(isset($info['aui_id'])) $resule = array_merge($resule, $info);
        }
        return is_array($resule) ? $resule : [];
    }

    /**
     * @name setDataViewInc
     * @desciption 设置浏览器增加1
     **/
    public function setDataViewInc($ar_id)
    {
        $ar_id = intval($ar_id);
        if($ar_id < 1) return FALSE;
        $sql        = 'UPDATE '.$this -> Db -> getTableNameAll('article').' SET ar_hits=1+ar_hits WHERE ar_id='.$ar_id.' AND ar_isdel=0 AND ar_status=1';
        return $this->Db->getDataNum($sql) > 0 ? TRUE : FALSE;
    }

    /**
     * 保存评论
     */
    public function saveComment(array $data = [])
    {
        $ar_id              = isset($data['ar_id']) ? intval($data['ar_id']) : 0;
        $us_id              = isset($data['us_id']) ? intval($data['us_id']) : 0;
        $ac_text            = isset($data['ac_text']) ? trim($data['ac_text']) : '';
        $sql        = 'UPDATE '.$this -> Db -> getTableNameAll('article').' SET ar_comments=1+ar_comments WHERE ar_id='.$ar_id.' AND ar_isdel=0 AND ar_status=1';
        $ar_comments = $this->Db->getDataNum($sql) > 0 ? TRUE : FALSE;
        if($ar_comments){
            $ac_ip              = Net::getIpLong();
            $currTime           = Time::getTimeStamp();
            $sql                = 'INSERT INTO '.$this -> Db -> getTableNameAll('article_comment').' SET ar_id='.$ar_id.', us_id='.$us_id.', ac_text=\''.addslashes($ac_text).'\', ac_ip=\''.$ac_ip.'\', ac_isdel=0, ac_first_time='.$currTime.', ac_last_time='.$currTime;
            $result_id          = $this->Db->getDataId($sql);
            return $result_id;
        }else{
            return 0;
        }
    }


    /**
     * 上一页
     * @param int $id
     * @return array
     */
    public function getDataUp(array $whereArray = [])
    {
        // 条件处理
        $whereString    = 'a.ar_status=1 AND a.ar_isdel='.static::ISDEL;
        if(count($whereArray) > 0) foreach($whereArray as $key => $val){
            switch($key){
                // 资讯ID
                case 'ar_id':{
                    $whereString .= ($whereString == ''?'':' AND ').'a.ar_id<'.$val;
                    break;
                }

                // 栏目ID
                case 'ac_id':{
                    $whereString .= ($whereString == ''?'':' AND ').'b.ac_id in ('.$val.')';
                    break;
                }
            }
        }
        // 获取资讯详情
        $sql         = 'SELECT SQL_CALC_FOUND_ROWS distinct a.ar_id,a.au_id,a.ar_title,a.ar_order FROM '.$this -> Db -> getTableNameAll('article').' a';
        $sql        .= ' LEFT JOIN '.$this -> Db -> getTableNameAll('article_category_relation').' b ON a.ar_id=b.ar_id WHERE '.$whereString.' ORDER BY ar_id DESC';
        $resule     = $this->Db->getDataOne($sql);
        isset($resule['ar_title']) && $resule['ar_title'] = stripslashes($resule['ar_title']);
        return is_array($resule) ? $resule : [];
    }

    /**
     * 下一页
     * @param int $id
     * @return array
     */
    public function getDataNext(array $whereArray = [])
    {
        // 条件处理
        $whereString    = 'a.ar_status=1 AND a.ar_isdel='.static::ISDEL;
        if(count($whereArray) > 0) foreach($whereArray as $key => $val){
            switch($key){
                // 资讯ID
                case 'ar_id':{
                    $whereString .= ($whereString == ''?'':' AND ').'a.ar_id>'.$val;
                    break;
                }

                // 栏目ID
                case 'ac_id':{
                    $whereString .= ($whereString == ''?'':' AND ').'b.ac_id in ('.$val.')';
                    break;
                }
            }
        }

        // 获取资讯详情
        $sql         = 'SELECT SQL_CALC_FOUND_ROWS distinct a.ar_id,a.au_id,a.ar_title,a.ar_order FROM '.$this -> Db -> getTableNameAll('article').' a';
        $sql        .= ' LEFT JOIN '.$this -> Db -> getTableNameAll('article_category_relation').' b ON a.ar_id=b.ar_id WHERE '.$whereString.' ORDER BY ar_id ASC';
        $resule     = $this->Db->getDataOne($sql);
        isset($resule['ar_title']) && $resule['ar_title'] = stripslashes($resule['ar_title']);
        return is_array($resule) ? $resule : [];
    }


    /**
     * 获取ID字符
     * @param array $category
     */
    public function getCategoryIds(array $category = [])
    {
        $data = [];
        foreach($category as $val)
        {
            $data[] = $val['id'];
        }

        return !empty($data) ? implode(',', $data) : '';
    }

    /**
     * 获取分类树数据
     *
     * @param int $parent_id    上级栏目ID
     * @return array
     */
    public function getCategoryList(int $parent_id=0)
    {
        $sql = 'SELECT ac_id,ac_parent_id,ac_name,ac_isopen FROM '.$this -> Db -> getTableNameAll('article_category').' WHERE ac_parent_id='.$parent_id.' AND ac_isdel=0';
        $result = $this->Db->getData($sql);
        $results = [];
        foreach($result as $row){
            $node = [];
            $node['id'] = $row['ac_id'];
            $node['text'] = $row['ac_name'];
            $childTree = $this->getCategoryList(intval($row['ac_id']));
            if(is_array($childTree) && !empty($childTree)){
                $node['state'] = $row['ac_isopen'] == 1 ? 'open' : 'closed';
                $node['children'] = $childTree;
            }
            $results[$node['id']] = $node;
        }
        return $results;
    }


    /**
     * 获取栏目详细信息
     * @param int $id 栏目ID
     * @return mixed
     */
    public function getCategoryInfo(int $id=0)
    {
        if(empty($id)) return [];

        $sql = 'SELECT ac_id,ac_parent_id,ac_name,ac_keywords,ac_description FROM '.$this -> Db -> getTableNameAll('article_category').' WHERE ac_id='.$id.' AND ac_isdel=0';
        $resule = $this->Db->getDataOne($sql);
        return is_array($resule) ? $resule : [];
    }


    /**
     * 获取图片地址
     * @param string $url
     */
    public function getThumbImgUrl(string $url='')
    {
        if(empty($url)) return '';

        $host = Conf::get('URL.RES');

        $url        = ltrim($url, '/');
        $url        = str_replace('Static/data/', '', $url);
        $hostUrl    = rtrim($host, '/').'/';
        return $hostUrl.$url;
    }

    /**
     * @name getArticleList
     * @desction 获取资讯列表
     * @param int $acId
     * @param int $num
     * @return array
     */
    public function getArticleList(int $acId, int $num):array{
        $acId = max($acId, 0);
        $num = max($num, 1);
        $acIdList = [];
        if($acId > 0){
            $acIdList[] = $acId;
            $sql = 'SELECT ac_id FROM '.$this -> Db -> getTableNameAll('article_category').' WHERE ac_parent_id='.$acId.' AND ac_isdel=0';
            $list = $this -> Db -> getData($sql);
            if(count($list) > 0) foreach ($list as $key => $val) $acIdList[] = intval($val['ac_id']);
            $acIdList = array_unique($acIdList);
        }
        $sql = 'SELECT a.*,b.* FROM '.$this -> Db -> getTableNameAll('article_category_relation').' AS b LEFT JOIN '.$this -> Db -> getTableNameAll('article').' AS a ON b.ar_id=a.ar_id WHERE '.(count($acIdList) > 0 ? 'b.ac_id IN ('.implode(',', $acIdList).') AND ':'').'a.ar_isdel=0 AND a.ar_status=1 GROUP BY a.ar_id ORDER BY a.ar_hits DESC, a.ar_last_time DESC LIMIT '.$num;
        $resData = $this -> Db -> getData($sql);
        return is_array($resData) ? $resData : [];
    }

    /**
     * @name getArticleImgList
     * @desction 获取资讯有图片列表
     * @param int $acId
     * @param int $num
     * @return array
     */
    public function getArticleImgList(int $acId, int $num):array{
        $acId = max($acId, 0);
        $num = max($num, 1);
        $acIdList = [];
        if($acId > 0){
            $acIdList[] = $acId;
            $sql = 'SELECT ac_id FROM '.$this -> Db -> getTableNameAll('article_category').' WHERE ac_parent_id='.$acId.' AND ac_isdel=0';
            $list = $this -> Db -> getData($sql);
            if(count($list) > 0) foreach ($list as $key => $val) $acIdList[] = intval($val['ac_id']);
            $acIdList = array_unique($acIdList);
        }
        $sql = 'SELECT a.*,b.* FROM '.$this -> Db -> getTableNameAll('article_category_relation').' AS b LEFT JOIN '.$this -> Db -> getTableNameAll('article').' AS a ON b.ar_id=a.ar_id WHERE '.(count($acIdList) > 0 ? 'b.ac_id IN ('.implode(',', $acIdList).') AND ':'').'a.ar_isdel=0 AND a.ar_status=1 AND a.ar_thumb_img !=\'\' GROUP BY a.ar_id ORDER BY a.ar_hits DESC, a.ar_last_time DESC LIMIT '.$num;
        $resData = $this -> Db -> getData($sql);
        return is_array($resData) ? $resData : [];
    }

    /**
     * @name getCommentList
     * @desction 获取资讯评论列表
     * @param int $arId
     * @return array
     */
    public function getCommentList(int $arId):array{
        if($arId < 1) return [];
        $dataList = [];
        $sql = 'SELECT * FROM '.$this -> Db -> getTableNameAll('article_comment').' WHERE ar_id='.$arId.' AND ac_isdel=0 ORDER BY ac_id DESC';
        $list = $this -> Db -> getData($sql);
        $urlRes = Conf::get('URL.RES');
        if(count($list) > 0) foreach ($list as $key => $val){
            $viewUsId = intval($val['us_id']);
            $sql = 'SELECT * FROM '.$this -> Db -> getTableNameAll('user_info').' WHERE us_id=\''.$viewUsId.'\' AND ui_isdel=0';
            $info = $this -> Db->getDataOne($sql);
            if(isset($info['ui_photo'])){
                if(strlen(trim($info['ui_photo'])) < 3){
                    $info['photo'] = '';
                }else{
                    $info['photo'] = $urlRes.ltrim($info['ui_photo'], '/');
                }
                $val = array_merge($val, $info);
            }
            $val['ac_text'] = htmlentities($val['ac_text']);
            $dataList[] = $val;
        }
        return $dataList;
    }

}