<?php
/**
 * @Copyright (C) 2016.
 * @Description MyZhidao
 * @FileName MyZhidao.php
 * @Author Huang.Xiang
 * @Version 1.0.1
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

class MyZhidaoData {
    public $userid;
    private $Db         = NULL;         //数据库对象
    const SUCCESS       = "success";    // 成功
    const FAIL          = "fail";       // 失败
    const ISDEL         = 0;            // 删除
    const IMG_HOST      = 'http://res.ggt.gzphp.cn/';

    /**
     * @name __construct
     * @desciption 初始化认证
     **/
    public function __construct(){
        $this -> Db         = Db::tag('DB.USER', 'GMY');

        $this->userid       = $_SESSION['TOKEN']['INFO']['id'];
    }


    /**
     * 获取列表
     * @param string $whereString
     * @param array $limit
     * @return array
     */
    public function getList(string $whereString = '', array $limit = [], string $orderString = '')
    {
        // 条件处理
        $whereString    = (empty($whereString) ? $whereString : rtrim($whereString, 'AND').' AND ').'zd_isdel='.static::ISDEL;

        // 获取数据
        $sql    = 'SELECT SQL_CALC_FOUND_ROWS zd_id,us_id,zd_title,zd_thumb_img,zd_keywords,zd_description,zd_release_ip,zd_favs,zd_answer,zd_hits,zd_status,zd_order,zd_first_time FROM '.$this -> Db -> getTableNameAll('zhidao').'
        WHERE '.$whereString.' ORDER BY '.$orderString.' LIMIT '.$limit[0].', '.$limit[1];
        $results = $this -> Db -> getData($sql);

        // 数据处理
        foreach($results as $val){
            $val['zd_thumb_img']    = $this->getThumbImgUrl($val['zd_thumb_img']);
            $val['zd_release_ip']   = Net::longIp(intval($val['zd_release_ip']));
            $val['zd_tags']         = $this->getDataListTags([$val]);
            $result[] = $val;
        }

        // 返回数据
        return isset($result[0]) && !empty($result[0]) ? $result : [];
    }


    /**
     * 获取列表
     * @param array $whereArray
     * @return array
     */
    public function getListPage(string $whereString = '', Page $Page)
    {
        $limit  = $Page -> getLimit();

        // 条件处理
        $whereString    = (empty($whereString) ? $whereString : rtrim($whereString, 'AND').' AND ').'zd_isdel='.static::ISDEL;
        // echo $whereString;

        // 获取数据
        $sql    = 'SELECT SQL_CALC_FOUND_ROWS zd_id,us_id,zd_title,zd_thumb_img,zd_keywords,zd_description,zd_release_ip,zd_favs,zd_answer,zd_hits,zd_status,zd_first_time FROM '.$this -> Db -> getTableNameAll('zhidao').' WHERE '.$whereString.' ORDER BY zd_id DESC LIMIT '.$limit[0].', '.$limit[1];

        $results = $this -> Db -> getData($sql);
        $totalNum = $this -> Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);

        // 数据处理
        foreach($results as $val){
            $val['zd_thumb_img']    = $this->getThumbImgUrl($val['zd_thumb_img']);
            $val['zd_release_ip']   = Net::longIp(intval($val['zd_release_ip']));
            $val['zd_tags']         = $this->getDataListTags([$val]);
            $val['zd_answer']       = $this->getAnswerNum(intval($val['zd_id']));
            $result[] = $val;
        }

        // 处理标签和图片地址
        $results = [];
        if(is_array($result) && isset($result[0])){
            foreach($result as $val){
                $val['zd_thumb_img']    = $this->getThumbImgUrl($val['zd_thumb_img']);
                $val['tags']            = $this->getDataListTags([$val]);
                $results[]              = $val;
            }
        }

        // 返回数据
        return isset($result[0]) && !empty($result[0]) ? $result : [];
    }

    /**
     * 获取回答列表
     * @param string $whereString
     * @param Page $Page
     * @return array
     */
    public function getAnswerList(string $whereString = '', Page $Page)
    {
        $limit  = $Page -> getLimit();

        // 条件处理
        $whereString    = (empty($whereString) ? $whereString : rtrim($whereString, 'AND').' AND ').'b.zd_isdel='.static::ISDEL;
        // echo $whereString;

        // 获取数据
        $sql    = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT b.zd_id,b.us_id,b.zd_title,b.zd_thumb_img,b.zd_keywords,b.zd_description,b.zd_favs,b.zd_answer,b.zd_hits,b.zd_status,b.zd_first_time FROM '.$this -> Db -> getTableNameAll('zhidao_answer').'as a
        LEFT JOIN '.$this -> Db -> getTableNameAll('zhidao').' as b ON a.zd_id=b.zd_id WHERE '.$whereString.' ORDER BY a.zd_id DESC LIMIT '.$limit[0].', '.$limit[1];

        $result = $this -> Db -> getData($sql);
        $totalNum = $this -> Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);

        // 返回数据
        return isset($result[0]) && !empty($result[0]) ? $result : [];
    }

    /**
     * 获取问题回答列表
     * @param string $whereString
     * @param Page $Page
     * @return array
     */
    public function getZhidaoAnswerList(string $whereString = '', Page $Page)
    {
        $limit  = $Page -> getLimit();

        // 条件处理
        $whereString    = (empty($whereString) ? $whereString : rtrim($whereString, 'AND').' AND ').'a.za_status= 1';
        // echo $whereString;

        // 获取数据
        $sql    = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT a.za_content,a.za_click_up,a.za_click_down,a.za_first_time,b.ui_name FROM '.$this -> Db -> getTableNameAll('zhidao_answer').'as a
        LEFT JOIN '.$this -> Db -> getTableNameAll('user_info').' as b ON a.us_id=b.us_id WHERE '.$whereString.' ORDER BY a.zd_id DESC LIMIT '.$limit[0].', '.$limit[1];

        $result = $this -> Db -> getData($sql);
        $totalNum = $this -> Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);

        // 返回数据
        return isset($result[0]) && !empty($result[0]) ? $result : [];
    }

    /**
     * 获取问题回答列表
     * @param string $whereString
     * @return array
     */
    public function getZhidaoAnswerAll(string $whereString = '')
    {

        // 条件处理
        $whereString    = (empty($whereString) ? $whereString : rtrim($whereString, 'AND').' AND ').'a.za_status= 1';
        // echo $whereString;

        // 获取数据
        $sql    = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT a.za_content,a.za_click_up,a.za_click_down,a.za_first_time,a.za_best,b.ui_name FROM '.$this -> Db -> getTableNameAll('zhidao_answer').'as a
        LEFT JOIN '.$this -> Db -> getTableNameAll('user_info').' as b ON a.us_id=b.us_id WHERE '.$whereString.' ORDER BY a.za_id DESC ';

        $result = $this -> Db -> getData($sql);
        // 返回数据
        return isset($result[0]) && !empty($result[0]) ? $result : [];
    }




    /**
     * 获取问答详情
     * @param string $whereString
     * @return array
     */
    public function getInfo(string $whereString = '')
    {
        // 获取资讯详情
        $sql        = 'SELECT zd_id,us_id,zd_title,zd_thumb_img,zd_keywords,zd_description,zd_favs,zd_answer,zd_hits,zd_status,zd_first_time FROM '.$this -> Db -> getTableNameAll('zhidao').' WHERE '.$whereString;
        $result     = $this->Db->getDataOne($sql);

        // 返回数据
        return isset($result) && !empty($result) ? $result : [];
    }

    /**
     * 获取问答详情
     * @param string $whereString
     * @return array
     */
    public function getZhidaoInfo(string $whereString = '')
    {
        // 获取资讯详情
        $sql        = 'SELECT a.zd_id,a.us_id,a.zd_title,a.zd_thumb_img,a.zd_keywords,a.zd_description,a.zd_favs,a.zd_answer,a.zd_hits,a.zd_status,a.zd_first_time,b.ui_name FROM '.$this -> Db -> getTableNameAll('zhidao').' as a left join  '.$this -> Db -> getTableNameAll('user_info').' as b on a.us_id = b.us_id WHERE '.$whereString;
        $result     = $this->Db->getDataOne($sql);

        // 返回数据
        return isset($result) && !empty($result) ? $result : [];
    }

    /**
     * 设置查看数量+1
     * @param string $whereString
     * @return bool
     */
    public function setZhidaoViewInc(string $whereString = '')
    {
        $sql        = 'UPDATE '.$this -> Db -> getTableNameAll('zhidao').' SET zd_hits=1+zd_hits WHERE '.$whereString.' AND zd_isdel=0';
        return $this->Db->getDataNum($sql) > 0 ? TRUE : FALSE;
    }

    /**
     * @name signout
     * @desciption 保存知道数据
     * @param array $data
     * @return string
     */
    public function saveData(array $data = [])
    {
        $zd_id              = isset($data['zd_id']) ? intval($data['zd_id']) : 0;
        $us_id              = isset($data['us_id']) ? intval($data['us_id']) : 0;
        $zd_title           = isset($data['zd_title']) ? $data['zd_title'] : '';
        $zd_keywords        = isset($data['zd_keywords']) ? $data['zd_keywords'] : '';
        $zd_description     = isset($data['zd_description']) ? $data['zd_description'] : '';
        $zd_thumb_img       = isset($data['zd_thumb_img']) ? $data['zd_thumb_img'] : [];
        $zd_order           = isset($data['zd_order']) ? intval($data['zd_order']) : 50;
        $zd_favs            = isset($data['zd_favs']) ? intval($data['zd_favs']) : 0;
        $zd_answer          = isset($data['zd_answer']) ? intval($data['zd_answer']) : 0;
        $zd_hits            = isset($data['zd_hits']) ? intval($data['zd_hits']) : 0;
        $zd_release_ip      = Net::getIpLong();
        $currTime           = Time::getTimeStamp();

        // 处理封面图
        $zd_thumb_img       = is_array($zd_thumb_img) ? implode(',', $zd_thumb_img) : '';

        if(empty($id)) {
            $sql = 'INSERT INTO ' . $this->Db->getTableNameAll('zhidao') . ' SET us_id=' . $us_id . ',zd_title=\'' . addslashes($zd_title) .'\',
                zd_keywords=\'' . addslashes($zd_keywords) . '\', zd_description=\'' . addslashes($zd_description) . '\', zd_thumb_img=\'' . $zd_thumb_img . '\',
                zd_status=0, zd_order=' . $zd_order . ', zd_favs=\'' . $zd_favs . '\', zd_answer=' . $zd_answer . ', zd_hits=' . $zd_hits . ', zd_release_ip=' . $zd_release_ip . ',
                zd_first_time=' . $currTime . ', zd_last_time=' . $currTime . ', zd_isdel=' . static::ISDEL;
            $result_id = $this->Db->getDataId($sql);
        }else{
            $sql = 'UPDATE '.$this -> Db -> getTableNameAll('zhidao').' SET zd_title=\'' . addslashes($zd_title) .'\',
                zd_keywords=\'' . addslashes($zd_keywords) . '\', zd_description=\'' . addslashes($zd_description) . '\', zd_thumb_img=\'' . $zd_thumb_img . '\', zd_last_time=' . $currTime . ' WHERE zd_id='.$zd_id.' AND us_id='.$us_id.' AND zd_isdel='.static::ISDEL;
            $result_id      = $this->Db->getDataNum($sql);
        }

        // 返回结果
        if($result_id){
            return static::SUCCESS;
        }
        return static::FAIL;
    }


    /**
     * 删除
     * @param array $whereArray
     * @return string
     */
    public function del(array $whereArray = [])
    {
        if(!isset($whereArray['zd_id'])) static::FAIL;

        // 条件处理
        $whereString    = 'us_id='.$this->userid.' AND zd_isdel='.static::ISDEL;
        if(count($whereArray) > 0) foreach($whereArray as $key => $val){
            switch($key){
                // 标题
                case 'zd_id':{
                    $whereString .= ($whereString == ''?'':' AND ').'zd_id = '.intval($val);
                    break;
                }
            }
        }

        // 删除附件
        $sql    = 'UPDATE '.$this -> Db -> getTableNameAll('zhidao').' SET zd_isdel=1 WHERE '.$whereString;
        if($this -> Db -> getDataNum($sql) > 0) return static::SUCCESS;
        return static::FAIL;
    }

    /**
     * 更新状态
     * @param array $whereArray
     * @param int $status   状态[0-待处理 1-已解决 2-已关闭]
     * @return string
     */
    public function upStatus(array $whereArray = [], int $status = 0)
    {
        if(!isset($whereArray['zd_id'])) static::FAIL;

        // 条件处理
        $whereString    = 'us_id='.$this->userid.' AND zd_isdel='.static::ISDEL;
        if(count($whereArray) > 0) foreach($whereArray as $key => $val){
            switch($key){
                // 标题
                case 'zd_id':{
                    $whereString .= ($whereString == ''?'':' AND ').'zd_id = '.intval($val);
                    break;
                }
            }
        }

        // 删除附件
        $sql    = 'UPDATE '.$this -> Db -> getTableNameAll('zhidao').' SET zd_status='.intval($status).' WHERE '.$whereString;
        if($this -> Db -> getDataNum($sql) > 0) return static::SUCCESS;
        return static::FAIL;
    }

    /**
     * 保存回答信息
     */
    public function saveWaitanswer(array $data = [])
    {
        $zd_id              = isset($data['zd_id']) ? intval($data['zd_id']) : 0;
        $us_id              = isset($data['us_id']) ? intval($data['us_id']) : 0;
        $za_content         = isset($data['za_content']) ? trim($data['za_content']) : '';
        $za_status          = 1;
        $currTime           = Time::getTimeStamp();

        $sql            = 'INSERT INTO '.$this -> Db -> getTableNameAll('zhidao_answer').' SET zd_id='.$zd_id.', us_id='.$us_id.', za_content=\''.addslashes($za_content).'\',
        za_status=\''.$za_status.'\', za_click_up=0, za_click_down=0, za_first_time='.$currTime.', za_last_time='.$currTime;
        $result_id      = $this->Db->getDataId($sql);

        return $result_id;
    }


    /**
     * 获取回答信息
     * @param string $whereString
     */
    public function getWaitanswer(string $whereString = '', Page $Page)
    {
        $limit  = $Page -> getLimit();

        // 获取数据
        $sql    = 'SELECT SQL_CALC_FOUND_ROWS za_id,us_id,zd_id,za_content,za_status,za_click_up,za_click_down,za_last_time FROM '.$this -> Db -> getTableNameAll('zhidao_answer').' WHERE '.$whereString.' ORDER BY za_id DESC LIMIT '.$limit[0].', '.$limit[1];
        $result = $this -> Db -> getData($sql);
        $totalNum = $this -> Db -> getDataInt('SELECT FOUND_ROWS() as num', 'num');
        $Page -> setParam('totalNum', $totalNum);

        // 返回数据
        return isset($result[0]) && !empty($result[0]) ? $result : [];
    }


    /**
     * 更新回答次数
     * @param int $num
     */
    public function upAnswer(int $num = 1, string $whereString = '')
    {
        $whereString = (empty($whereString) ? $whereString : rtrim($whereString, 'AND').' AND ').'zd_isdel=' . static::ISDEL;
        $sql = 'UPDATE '.$this -> Db -> getTableNameAll('zhidao').' SET zd_answer=zd_answer+'.$num.' WHERE '.$whereString;
        if($this -> Db -> getDataNum($sql) > 0) return static::SUCCESS;
        return static::FAIL;
    }

    /**
     * @name getDataListTags
     * @desciption 获取列表关键词
     **/
    public function getDataListTags(array $dataList = [])
    {
        $tags = [];
        foreach($dataList as $val){
            $tag = isset($val['zd_keywords']) && !empty($val['zd_keywords']) ? explode(' ', $val['zd_keywords']) : [];
            $tags = array_merge($tags, $tag);
        }
        $tags = array_unique(array_filter($tags));
        return array_slice($tags, 0, 10);
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
     * @name getAnswerNum
     * @desction 获取知道回答数
     * @param int $zdId
     * @return int
     */
    public function getAnswerNum(int $zdId):int{
        if($zdId < 1) return 0;
        $sql = 'SELECT count(*) as num FROM '.$this -> Db -> getTableNameAll('zhidao_answer').' WHERE zd_id='.$zdId.' AND za_status IN (0, 1)';
        return $this -> Db -> getDataInt($sql, 'num');
    }


}