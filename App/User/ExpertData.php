<?php
/**
 * @Copyright (C) 2016.
 * @Description AdmGroupData
 * @FileName AdmGroupData.php
 * @Author   Huang.Xiang
 * @Version  1.0.1
 **/

declare(strict_types = 1);//strict
namespace App\User;

use \App\Pub\Common;
use \Libs\Comm\Time;
use \Libs\Comm\Valid;
use \Libs\Tag\Db;
use \Libs\Comm\Net;
use \Libs\Tag\Page;
use \App\Auth\MyAuth;
use \Libs\Frame\Conf;

class ExpertData
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
     * @desciption 获取分类列表
     **/
    public function getDataList(array $whereArray = [], Page $Page)
    {
        // 条件处理
        $limit          = $Page -> getLimit();
        $whereString    = 'zj_isdel='.static::ISDEL;
        if(count($whereArray) > 0) foreach($whereArray as $key => $val){
            switch($key){
                // 栏目ID
                case 'zt_id':{
                    $whereString .= ($whereString == ''?'':' AND ').'zt_id='.$val;
                    break;
                }
                case 'zj_title':{
                    $whereString .= ($whereString == ''?'':' AND ').'zj_title like \'%'.$val.'%\'';
                    break;
                }
                case 'tag':{
                    $whereString .= ($whereString == ''?'':' AND ').'zj_keywords like \'%'.$val.'%\'';
                    break;
                }
            }
        }

        // 获取数据
        $sql    = 'SELECT SQL_CALC_FOUND_ROWS distinct zj_id,zt_id,au_id,zj_title,zj_keywords,zj_description,zj_address,zj_hits,zj_heart,zj_comments,zj_img_url,zj_file_url,zj_file_name,zj_source,zj_status,zj_order,zj_first_time,zj_last_time FROM '.$this -> Db -> getTableNameAll('zhuanjia');
        $sql    .=' WHERE '.$whereString.' ORDER BY zj_order ASC, zj_id DESC LIMIT '.$limit[0].', '.$limit[1];
        $result = $this -> Db -> getData($sql);

        // 处理标签和图片地址
        $results = [];
        if(is_array($result) && isset($result[0]) && !empty($result[0])){
            foreach($result as $val){
                $val['zj_show_img_url'] = $this->getThumbImgUrl($val['zj_img_url']);
                $val['tags']            = $this->getDataListTags([$val]);
                $results[]              = $val;
            }
        }

        $totalNum = $this -> Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);
        return $results;
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
     * @name 获取展会广告位
     */
    public function getZhanhuiBanner($id){
        $sql = 'SELECT banner.* FROM '.$this -> Db -> getTableNameAll('banner').' as  banner where banner.id ='.$id;
        $zhanhuibanner = $this -> Db -> getDataOne($sql);
        return $zhanhuibanner;
    }

    /**
     * @name getDataListTags
     * @desciption 获取列表关键词
     **/
    public function getDataListTags(array $dataList = [])
    {
        $tags = [];
        foreach($dataList as $val){
            $tag = isset($val['zj_keywords']) && !empty($val['zj_keywords']) ? explode(' ', $val['zj_keywords']) : [];
            $tags = array_merge($tags, $tag);
        }
        $tags = array_unique(array_filter($tags));
        return array_slice($tags, 0, 10);
    }

    /**
     * 保存文章
     * @param array $data
     */
    public function saveData(array $data = [])
    {
        $id                 = isset($data['id']) ? intval($data['id']) : 0;
        $au_id              = isset($data['au_id']) ? intval($data['au_id']) : 0;
        $zt_id              = isset($data['zt_id']) ? intval($data['zt_id']) : 0;
        $zj_title           = isset($data['zj_title']) ? $data['zj_title'] : '';
        $zj_keywords        = isset($data['zj_keywords']) ? $data['zj_keywords'] : '';
        $zj_description     = isset($data['zj_description']) ? $data['zj_description'] : '';
        $zj_img_url         = isset($data['zj_img_url']) ? $data['zj_img_url'] : '';
        $zj_file_url        = isset($data['zj_file_url']) ? $data['zj_file_url'] : '';
        $zj_file_name       = isset($data['zj_file_name']) ? $data['zj_file_name'] : '';
        $zj_content         = isset($data['zj_content']) ? $data['zj_content'] : '';
        $zj_order           = isset($data['zj_order']) ? intval($data['zj_order']) : 50;
        $zj_hits            = isset($data['zj_hits']) ? intval($data['zj_hits']) : 0;
        $zj_heart           = isset($data['zj_heart']) ? intval($data['zj_heart']) : 0;
        $zj_source          = isset($data['zj_source']) ? $data['zj_source'] : '站内';
        $zj_address         = isset($data['zj_address']) ? $data['zj_address'] : '待定';
        $currTime           = !empty($data['zj_first_time']) ? strtotime($data['zj_first_time']) : Time::getTimeStamp();
        $currIp             = Net::getIpLong();

        if(empty($id)){
            $sql            = 'INSERT INTO '.$this -> Db -> getTableNameAll('zhuanjia').' SET au_id='.$au_id.', zt_id='.$zt_id.', zj_hits='.$zj_hits.', zj_heart='.$zj_heart.', zj_title=\''.addslashes($zj_title).'\', zj_keywords=\''.addslashes($zj_keywords).'\', zj_description=\''.addslashes($zj_description).'\', zj_img_url=\''.$zj_img_url.'\', zj_file_url=\''.$zj_file_url.'\', zj_file_name=\''.$zj_file_name.'\', zj_status=0, zj_source=\''.$zj_source.'\', zj_address=\''.$zj_address.'\', zj_order='.$zj_order.', zj_create_ip='.$currIp.', zj_first_time='.$currTime.', zj_last_time='.$currTime.', zj_isdel='.static::ISDEL;
            $result_id      = $this->Db->getDataId($sql);

            // 插入内容
            $infoSql        = 'INSERT INTO '.$this -> Db -> getTableNameAll('zhuanjia_info').' SET zj_id='.$result_id.', zi_content=\''.addslashes($zj_content).'\'';
            $result_infoId  = $this->Db->getDataId($infoSql);
        }else{
            $sql            = 'UPDATE '.$this -> Db -> getTableNameAll('zhuanjia').' SET zj_hits='.$zj_hits.', zt_id='.$zt_id.', zj_heart='.$zj_heart.', zj_title=\''.addslashes($zj_title).'\', zj_keywords=\''.addslashes($zj_keywords).'\', zj_description=\''.addslashes($zj_description).'\', zj_img_url=\''.$zj_img_url.'\', zj_file_url=\''.$zj_file_url.'\', zj_file_name=\''.$zj_file_name.'\', zj_source=\''.$zj_source.'\', zj_address=\''.$zj_address.'\', zj_order='.$zj_order.', zj_last_time='.$currTime.' WHERE zj_id='.$id.' AND zj_isdel='.static::ISDEL;
            $result_id      = $this->Db->getDataNum($sql);

            $infoSql        = 'UPDATE '.$this -> Db -> getTableNameAll('zhuanjia_info').' SET zi_content=\''.addslashes($zj_content).'\' WHERE zj_id='.$id;
            $result_infoId  = $this->Db->getDataNum($infoSql);
        }

        if($result_id || $result_infoId){
            return static::SUCCESS;
        }
        return static::FAIL;
    }


    /**
     * 删除数据
     * @param int $id
     * @return array
     */
    public function delData(int $zj_id = 0)
    {
        if (empty($zj_id)) static::FAIL;

        // 删除附件
        $sql = 'UPDATE ' . $this->Db->getTableNameAll('zhuanjia') . ' SET zj_isdel=1 WHERE zj_id=' . $zj_id . ' AND zj_isdel=' . static::ISDEL;
        if ($this->Db->getDataNum($sql) > 0) return static::SUCCESS;
        return static::FAIL;
    }


    /**
     * 获取栏目详细信息
     * @param int $id 栏目ID
     * @return mixed
     */
    public function getInfo(int $id=0)
    {
        if(empty($id)) return [];
        $sql            = 'SELECT zj_id,au_id,zt_id,zj_title,zj_keywords,zj_description,zj_img_url,zj_file_url,zj_file_name,zj_hits,zj_heart,zj_source,zj_order,zj_last_time FROM '.$this -> Db -> getTableNameAll('zhuanjia').' WHERE zj_id='.$id.' AND zj_isdel=0';
        $resule         = $this->Db->getDataOne($sql);

        $infoSql        = 'SELECT zi_content FROM '.$this -> Db -> getTableNameAll('zhuanjia_info').' WHERE zj_id='.$id;
        $resuleInfo     = $this->Db->getDataOne($infoSql);

        if(is_array($resule) && !empty($resule)){
            $resule['zj_content'] = isset($resuleInfo['zi_content']) ? $resuleInfo['zi_content'] : '';
            $resule['zj_show_img_url'] = !empty($resule['zj_img_url']) ? $this->getThumbImgUrl($resule['zj_img_url']) : '';
            $resule['tags']            = $this->getDataListTags([$resule]);
        }

        return is_array($resule) ? $resule : [];
    }

    /**
     * 获取关联信息
     * @param int $type_id        分类ID
     */
    public function getTypeList(int $category_id = 0)
    {
        $where = " zt_isdel=".static::ISDEL;
        if(!empty($category_id)){
            $where .= " AND zt_id={$category_id}";
        }

        $sql = 'SELECT zt_id,zt_name FROM '.$this -> Db -> getTableNameAll('zhuanjia_type').' WHERE'.$where.' ORDER BY zt_order ASC, zt_id desc';
        $resule = $this->Db->getData($sql);
        return is_array($resule) && isset($resule[0]) && !empty($resule[0]) ? $resule : [];
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
     * 上一页
     * @param int $id
     * @return array
     */
    public function getDataUp(int $id = 0)
    {
        // 条件处理
        $whereString = 'zj_isdel=' . static::ISDEL;
        $whereString .= ($whereString == '' ? '' : ' AND ') . 'zj_id<' . $id;

        // 获取资讯详情
        $sql    = 'SELECT zj_id,au_id,zj_title,zj_order FROM ' . $this->Db->getTableNameAll('zhuanjia') . ' WHERE ' . $whereString . ' ORDER BY zj_id DESC';
        $resule = $this->Db->getDataOne($sql);
        return is_array($resule) ? $resule : [];
    }

    /**
     * 下一页
     * @param int $id
     * @return array
     */
    public function getDataNext(int $id = 0)
    {
        // 条件处理
        $whereString    = 'zj_isdel='.static::ISDEL;
        $whereString .= ($whereString == '' ? '' : ' AND ') . 'zj_id>' . $id;

        // 获取资讯详情
        $sql         = 'SELECT zj_id,au_id,zj_title,zj_order FROM '.$this -> Db -> getTableNameAll('zhuanjia').' WHERE '.$whereString.' ORDER BY zj_id ASC';
        $resule     = $this->Db->getDataOne($sql);
        return is_array($resule) ? $resule : [];
    }


    /**
     * @name getDataHeatList
     * @desciption 获取热门列表
     **/
    public function getDataHeatList(array $whereArray = [], int $limitNum = 1)
    {
        // 条件处理
        $whereString    = 'zj_isdel='.static::ISDEL;
        if(count($whereArray) > 0) foreach($whereArray as $key => $val){
            switch($key){
                // 栏目ID
                case 'zt_id':{
                    $whereString .= ($whereString == ''?'':' AND ').'zt_id='.$val;
                    break;
                }
            }
        }

        // 获取数据
        $sql    = 'SELECT zj_id,au_id,zt_id,zj_title,zj_keywords,zj_description,zj_img_url,zj_order FROM '.$this -> Db -> getTableNameAll('zhuanjia').' WHERE '.$whereString.' ORDER BY zj_hits ASC, zj_order ASC, zj_id DESC LIMIT '.$limitNum;
        $result = $this -> Db -> getData($sql);

        // 处理标签和图片地址
        $results = [];
        if(is_array($result) && isset($result[0]) && !empty($result[0])){
            foreach($result as $val){
                $val['zj_show_img_url'] = $this->getThumbImgUrl($val['zj_img_url']);
                $val['tags']            = $this->getDataListTags([$val]);
                $results[]              = $val;
            }
        }

        return $results;
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

}