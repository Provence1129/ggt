<?php
/**
 * @Copyright (C) 2016.
 * @Description MyAuth
 * @FileName MyAuth.php
 * @Author Huang.Xiang
 * @Version 1.0.1
**/

declare(strict_types=1);//strict
namespace App\Auth;
use \Libs\Comm\Time;
use \Libs\Frame\Auth;
use \Libs\Tag\Db;
use \Libs\Comm\Net;
use \Libs\Frame\Conf;
use \App\Pub\Tips;
class MyAuth extends Auth{
    protected $Db           = NULL;         //数据库对象
    protected $tokenInfo    = [];           //Token信息
    protected $tokenArray   = [];           //Token令牌信息

    /**
     * @name __construct
     * @desciption 初始化认证
     **/
    public function __construct(){
        $this -> Db         = Db::tag('DB.AUTH', 'GMY');
        $this -> tokenInfo  = $this -> getSession() -> get('TOKEN');
    }

    /**
     * @name getSess
     * @desciption 获取当前会话对象
     * @return mixed
     */
    public function getSess(){
        return $this -> getSession();
    }

    /**
     * @name isLogin
     * @desciption 是否已登录
     * @return bool
    **/
    public function isLogin():bool{
        //读取令牌信息
        $usId = intval($this->tokenInfo['UID'] ?? '');
        if($usId < 1) return FALSE;   //ID错误
        return TRUE;
    }

    /**
     * @name check
     * @desciption 检测是否有权限
     * @param string $authString
     * @return bool
    **/
    public function check(string $authString):bool{
        //读取令牌信息
        $usId = intval($this->tokenInfo['UID'] ?? '');
        if($usId < 1) return FALSE;   //ID错误
        return TRUE;
    }

    /**
     * @name allowCall
     * @desciption 权限允许
     * @param string $newAction
     * @param string $oldAction
     * @param string $authString
     * @param int $status //认证开关[0-关闭认证,1-开启认证]
     * @param int $default //默认认证(没有配置或配置空[0-允许,1-禁止])
     * @return bool
    **/
    public function allowCall(string $newAction, string $oldAction, string $authString, int $status, int $default):bool{
        return TRUE;
    }

    /**
     * @name denyCall
     * @desciption 权限拒绝
     * @param string $newAction
     * @param string $oldAction
     * @param string $authString
     * @param int $status //认证开关[0-关闭认证,1-开启认证]
     * @param int $default //默认认证(没有配置或配置空[0-允许,1-禁止])
     * @return bool
    **/
    public function denyCall(string $newAction, string $oldAction, string $authString, int $status, int $default):bool{
        Tips::showNoPermission($newAction, $oldAction, $authString);    //显示无权限提示
        return TRUE;
    }
}