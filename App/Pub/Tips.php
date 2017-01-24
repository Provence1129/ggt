<?php
/**
 * @Copyright (C) 2016.
 * @Description Tips
 * @FileName Tips.php
 * @Author   Huang.Xiang
 * @Version  1.0.1
 **/

declare(strict_types = 1);//strict
namespace App\Pub;
use Libs\Comm\Comm;
use Libs\Frame\Url;
use \Libs\Tag\Tpl;
class Tips{
    /**
     * @name showNoPermission
     * @desciption  显示无权限提示
     * @param string $newAction
     * @param string $oldAction
     * @param string $authString
     * @return bool
     */
    public static function showNoPermission(string $newAction, string $oldAction, string $authString):bool{
        switch($authString){
            case 'DENY':{       //任意永远禁止
                $msg = '该功能已经停用,如有需要请与管理员联系!';
                $url = 'javascript: history.back();';
                $time = 3;
                break;
            }
            case 'LOGIN':{      //只需要登录即可通过
                $msg = '您未登录,请前往登录!';
                $url = Link::getLink('signin');
                $time = 1;
                break;
            }
            default:{           //按权限检查结果判定
                $msg = '您暂无权操作,如有需要请与管理员联系!';
                $url = 'javascript: history.back();';
                $time = 3;
            }
        }   //EndSwitch
        Tpl::Tag('TPL', 'GSM') -> assign('tipsMsg', preg_replace("'\n'", '<br />', htmlentities($msg)));
        Tpl::Tag('TPL', 'GSM') -> assign('tipsUrl', $url);
        Tpl::Tag('TPL', 'GSM') -> assign('tipsTime', $time);
        Tpl::Tag('TPL', 'GSM') -> show('Auth/tipsNoPermission.html');
        return TRUE;
    }

    /**
     * @name showExceptionError
     * @desciption 显示异常错误提示
     * @param string $msg
     * @param mixed $e
     * @return bool
     **/
    public static function showExceptionError(string $msg, $e):bool{
        Tpl::Tag('TPL', 'GSM') -> assign('tipsMsg', preg_replace("'\n'", '<br />', htmlentities($msg)));
        Tpl::Tag('TPL', 'GSM') -> show('Auth/tipsExceptionError.html');
        return TRUE;
    }

    /**
     * @name show
     * @desciption  显示提示
     * @param string $msg
     * @param string $url default['']
     * @param int $time default[3]
     * @param int $res default[0]1-成功,0-失败
     * @return bool
     */
    public static function show(string $msg, string $url = '', int $time = 3, $res=-1):bool{
        $errorData = ['失败', '错误', '禁止登录', '请填写正确', '请输入正确', '请先登录', '重试', '已过期', '上限'];
        $successData = ['成功', '完成'];
        if($res != 0 && $res != 1){
            foreach ($errorData as $val){
                if(preg_match("'".$val."'", $msg)){
                    $res = 0;
                    break;
                }
            }
        }
        if($res != 0 && $res != 1){
            foreach ($successData as $val){
                if(preg_match("'".$val."'", $msg)){
                    $res = 1;
                    break;
                }
            }
        }
        if($res != 0 && $res != 1){
            $string = 'javascript:history.back(';
            if(substr(strtolower(Comm::trimAll($url)), 0, strlen($string)) === $string){
                $res = 0;
            }
        }
        if($res != 0 && $res != 1) $res = 1;
        Tpl::Tag('TPL', 'GSM') -> assign('tipsMsg', preg_replace("'\n'", '<br />', htmlentities($msg)));
        Tpl::Tag('TPL', 'GSM') -> assign('tipsUrl', $url);
        Tpl::Tag('TPL', 'GSM') -> assign('tipsTime', $time);
        Tpl::Tag('TPL', 'GSM') -> assign('res', $res);
        Tpl::Tag('TPL', 'GSM') -> show('Auth/show.html');
        return TRUE;
    }
}