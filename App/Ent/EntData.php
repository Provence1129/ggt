<?php
/**
 * @Copyright (C) 2016.
 * @Description EntData
 * @FileName EntData.php
 * @Author   Huang.Xiang
 * @Version  1.0.1
 **/

declare(strict_types = 1);//strict
namespace App\Ent;
use App\Pub\Tips;
use Libs\Comm\From;
use Libs\Comm\Http;
use \Libs\Comm\Time;
use \Libs\Tag\Db;
use \Libs\Frame\Conf;
class EntData{
    private $Db             = NULL;         //数据库对象
    private static $Domain  = '';           //域名
    private static $usId    = 0;            //域名用户ID

    /**
     * @name __construct
     * @desciption 初始化认证
     **/
    public function __construct(){
    }

    /**
     * @name getDomain
     * @desciption 获取域名名
     * @return string
     */
    public static function getDomain():string{
        if(strlen(self::$Domain) < 1){
            $domain = From::valTrim('domain');
            if(strlen($domain) < 1){
                $currDomain = Http::getServerName();
                $domain = substr($currDomain, 0, strpos($currDomain, '.'));
            }
            self::$Domain = $domain;
        }
        return self::$Domain;
    }

    /**
     * @name getLink
     * @desciption 获取链接地址
     * @param string $QueryString
     * @param string $pageName
     * @return string
     */
    public static function getLink(string $QueryString, string $pageName = 'index.php'):string{
        $pageName = strlen($pageName) < 1 ? 'index.php' : $pageName;
        $domain = From::valTrim('domain');
        if(strlen($domain) < 1){
            $url = Http::getHttpDomain(TRUE).$pageName;
        }else{
            $url = Http::getHttpDomain(TRUE).'ent/'.$pageName.'?domain='.urlencode($domain);
        }
        if(strlen($QueryString) > 0) $url .= (strpos($url, '?') === FALSE ? '?' : '&').$QueryString;
        return $url;
    }

    /**
     * @name checkInfo
     * @desciption 检查域名信息
     * @param string $domain
     * @return bool
     */
    public static function checkInfo(string $domain):bool{
        $Db = Db::tag('DB.USER', 'GMY');
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('ent_setting').' WHERE es_domain=\''.addslashes($domain).'\' AND es_isdel=0 ORDER BY es_last_time DESC';
        $esInfo = $Db->getDataOne($sql);
        $isEs = intval($esInfo['es_id'] ?? 0) > 0 ? 1 : 0;
        if($isEs == 0) Tips::show('商铺还未开通!', 'javascript: history.back();');
        $es_status = intval($esInfo['es_status']) == 1 ? 1 : 0; //是否已禁止[1-是,0-否]禁止将无法访问
        if($es_status == 1) Tips::show('商铺已停用，请联系我们！', 'javascript: history.back();');
        return TRUE;
    }

    /**
     * @name getInfo
     * @desciption 获取域名信息
     * @param string $domain
     * @return array
     */
    public static function getInfo(string $domain):array{
        $Db = Db::tag('DB.USER', 'GMY');
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('ent_setting').' WHERE es_domain=\''.addslashes($domain).'\' AND es_isdel=0 ORDER BY es_last_time DESC';
        $esInfo = $Db->getDataOne($sql);
        return $esInfo;
    }

    /**
     * @name getUsId
     * @desciption 获取商铺用户ID
     * @return int
     */
    public static function getUsId():int{
        if(self::$usId < 1){
            $entInfo = self::getInfo(self::getDomain());
            $usId = intval($entInfo['us_id'] ?? 0);
            self::$usId = $usId;
        }
        return self::$usId;
    }

    /**
     * @name getSeo
     * @desciption 获取域名SEO信息
     * @return array
     */
    public static function getSeo():array{
        $usId = self::getUsId();
        $Db = Db::tag('DB.USER', 'GMY');
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('ent_seo').' WHERE us_id=\''.$usId.'\' AND es_isdel=0 ORDER BY es_last_time DESC';
        $seoInfo = $Db->getDataOne($sql);
        if(isset($seoInfo['Description'])){
            $seoInfo['es_title']    = preg_replace("/<.*>/", '', $seoInfo['es_title']);
            $seoInfo['es_key']      = preg_replace("/<.*>/", '', $seoInfo['es_key']);
            $seoInfo['es_desc']     = preg_replace("/<.*>/", '', $seoInfo['es_desc']);
        }
        return $seoInfo;
    }

    /**
     * @name getBasic
     * @desciption 获取基本信息
     * @return array
     */
    public static function getBasic():array{
        $usId = self::getUsId();
        $Db = Db::tag('DB.USER', 'GMY');
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('user_info').' WHERE us_id=\''.$usId.'\' AND ui_isdel=0 ORDER BY ui_last_time DESC';
        $seoInfo = $Db->getDataOne($sql);
        return $seoInfo;
    }

    /**
     * @name getCompany
     * @desciption 获取公司介绍
     * @return array
     */
    public static function getCompany():array{
        $usId = self::getUsId();
        $Db = Db::tag('DB.USER', 'GMY');
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('ent_company').' WHERE us_id=\''.$usId.'\' AND ec_isdel=0 ORDER BY ec_last_time DESC';
        $seoInfo = $Db->getDataOne($sql);
        return $seoInfo;
    }

    /**
     * @name getEnterprise
     * @desciption 获取企业认证信息
     * @return array
     */
    public static function getEnterprise():array{
        $usId = self::getUsId();
        $Db = Db::tag('DB.USER', 'GMY');
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('enterprise').' WHERE us_id=\''.$usId.'\' AND ent_isdel=0 ORDER BY ent_last_time DESC';
        $seoInfo = $Db->getDataOne($sql);
        return $seoInfo;
    }

    /**
     * @name getEnterpriseAuth
     * @desciption 获取企业授权信息
     * @return array
     */
    public static function getEnterpriseAuth():array{
        $usId = self::getUsId();
        $Db = Db::tag('DB.USER', 'GMY');
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('enterprise_shouquan').' WHERE us_id=\''.$usId.'\' AND ents_isdel=0 ORDER BY ents_last_time DESC';
        $seoInfo = $Db->getDataOne($sql);
        return $seoInfo;
    }

    /**
     * @name getShopUrl
     * @desciption 获取商铺地址
     * @return string
     */
    public static function getShopUrl(int $usId){
        $url = '';
        $Db = Db::tag('DB.USER', 'GMY');
        //开通商铺信息
        $sql = 'SELECT * FROM '.$Db -> getTableNameAll('ent_setting').' WHERE us_id=\''.$usId.'\' AND es_isdel=0 ORDER BY es_last_time DESC';
        $esInfo = $Db->getDataOne($sql);
        $isEs = intval($esInfo['es_id'] ?? 0) > 0 ? 1 : 0;
        if($isEs == 0) return $url;
        $es_status = intval($esInfo['es_status']) == 1 ? 1 : 0; //是否已禁止[1-是,0-否]禁止将无法访问
        if($es_status == 1) return $url;
        $domain = trim($esInfo['es_domain']);
        $domain = preg_replace("/[^a-z\d\-]+/i", '', $domain);  //只允许字母数字和中线
        if(strlen($domain) < 1) return $url;
        if(Conf::get('Ent.isDomain') == 1){ //域名模式
            $pubDomain = Conf::get('Ent.domain');
            if(strlen($pubDomain) < 1) return $url;
            $isHttps = Conf::get('Ent.isHttps');
            $url = ($isHttps ? 'https://' : 'http://').$domain.'.'.ltrim(trim($pubDomain), '.');
        }else{
            $url = Http::getHttpDomain(TRUE).'ent/index.php?domain='.urlencode($domain);
        }
        return $url;
    }
}