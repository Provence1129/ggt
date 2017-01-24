<?php
/**
 * @Copyright (C) 2016.
 * @Description Link
 * @FileName Link.php
 * @Author   Huang.Xiang
 * @Version  1.0.1
 **/

declare(strict_types = 1);//strict
namespace App\Pub;
use \Exception;
use \Libs\Comm\Comm;
use \Libs\Tag\Cache;
use \Libs\Frame\Conf;
use \Libs\Comm\From;
use \Libs\Comm\Net;
use \Libs\Comm\Time;
use \Libs\Comm\Valid;
use \Libs\Tag\Db;
class Link{
    /**
     * @name getDb
     * @desciption 获取数据操作对象
     * @return mixed
     */
    public static function getDb(){
        return Db::tag('DB.ADMIN', 'GMY');
    }

    /**
     * @name getLink
     * @desciption 获取链接URL地址
     * @param string $type
     * @return string
     **/
    public static function getLink(string $type):string{
        $type = trim($type);
        $url = '';
        switch($type){
            case 'user':{ //会员中心
                $url = '/user/index.php';
                break;
            }
            case 'signincode':{ //登录验证码
                $url = '/user/signincode.php';
                break;
            }
            case 'signin':{ //登录
                $url = '/user/signin.php';
                break;
            }
            case 'signout':{ //登出
                $url = '/user/signout.php';
                break;
            }
            case 'reg':{ //注册
                $url = '/user/reg.php';
                break;
            }
            case 'signupcode':{ //注册验证码
                $url = '/user/signupcode.php';
                break;
            }
            case 'getregcode':{ //获取手机验证码注册
                $url = '/user/getregcode.php';
                break;
            }
            case 'getresetpasscode':{ //获取手机验证码重置密码
                $url = '/user/getresetpasscode.php';
                break;
            }
            case 'forgetpasswd':{ //忘记密码
                $url = '/user/forgetpasswd.php';
                break;
            }
            case 'tariffs':{ //揽货
                $url = '/user/tariffs.php';
                break;
            }
            case 'tariffs_multi':{ //多式联运
                $url = '/user/tariffs_type_multi.php';
                break;
            }
            case 'tariffs_railway':{ //铁路运输
                $url = '/user/tariffs_type_railway.php';
                break;
            }
            case 'tariffs_sea':{ //海运
                $url = '/user/tariffs_type_sea.php';
                break;
            }
            case 'tariffs_air':{ //空运
                $url = '/user/tariffs_type_air.php';
                break;
            }
            case 'tariffs_land':{ //公路运
                $url = '/user/tariffs_type_land.php';
                break;
            }
            case 'tariffs_storage':{ //仓储
                $url = '/user/tariffs_type_storage.php';
                break;
            }
            case 'tariffs_detect':{ //报关报检
                $url = '/user/tariffs_type_detect.php';
                break;
            }
            case 'pallet':{ //发货
                $url = '/user/pallet.php';
                break;
            }
            case 'pallet_multi':{ //多式联运
                $url = '/user/pallet_type_multi.php';
                break;
            }
            case 'pallet_railway':{ //铁路运输
                $url = '/user/pallet_type_railway.php';
                break;
            }
            case 'pallet_sea':{ //海运
                $url = '/user/pallet_type_sea.php';
                break;
            }
            case 'pallet_air':{ //空运
                $url = '/user/pallet_type_air.php';
                break;
            }
            case 'pallet_land':{ //公路运
                $url = '/user/pallet_type_land.php';
                break;
            }
            case 'pallet_storage':{ //仓储
                $url = '/user/pallet_type_storage.php';
                break;
            }
            case 'pallet_detect':{ //报关报检
                $url = '/user/pallet_type_detect.php';
                break;
            }
            case 'entshop':{ //商铺管理
                $url = '/user/entshop.php';
                break;
            }
            case 'box':{ //箱卡集市
                $url = '/user/box.php';
                break;
            }
            case 'intertrad':{ //国际贸易
                $url = '/user/intertrad.php';
                break;
            }
            case 'bid':{ //项目竞标
                $url = '/user/bid.php';
                break;
            }
            case 'gtb':{ //港通宝
                $url = '/user/gtb.php';
                break;
            }
            case 'expert':{ //我是专家
                $url = '/user/expert.php';
                break;
            }
            case 'market':{ //效果营销
                $url = '/user/market.php';
                break;
            }
            case 'service':{ //增值服务
                $url = '/user/service.php';
                break;
            }
            case 'mybaike':{ //百科文库
                $url = '/user/mybaike.php';
                break;
            }
            case 'myzhidao':{ //知道
                $url = '/user/myzhidao.php';
                break;
            }
            case 'myydyl':{   // 一带一路
                $url = '/user/myydyl.php';
                break;
            }
            //以下是前台网站
            case 'index':{ //首页
                $url = '/index.php';
                break;
            }
            case 'shop':{ //商城
                $url = '/shop/index.php';
                break;
            }
            case 'baike':{ //百科文库
                $url = '/baike/index.php';
                break;
            }
            case 'banlie':{ //班列信息
                $url = '/banlie/index.php';
                break;
            }
            case 'kouan':{ //口岸中心
                $url = '/kouan/index.php';
                break;
            }
            case 'zjzx':{ //口岸中心
                $url = '/zjzx/index.php';
                break;
            }
            case 'dlsq':{ //口岸中心
                $url = '/dlsq/index.php';
                break;
            }
            case 'daili':{ //百科文库
                $url = '/daili/index.php';
                break;
            }
            case 'zhidao':{ //知道
                $url = '/zhidao/index.php';
                break;
            }
            case 'renwu':{ //人物
                $url = '/renwu/index.php';
                break;
            }
            case 'ydyl':{   // 一带一路
                $url = '/ydyl/index.php';
                break;
            }
            case 'zhuanjia':{ //我是专家
                $url = '/zhuanjia/index.php';
                break;
            }
            case 'news':{ //资讯
                $url = '/news/index.php';
                break;
            }
            case 'news_lists':{ //首页
                $url = '/news/lists.php';
                break;
            }
            case 'news_detail':{ //首页
                $url = '/news/detail.php';
                break;
            }
            case 'about':{ //关于
                $url = '/about/index.php';
                break;
            }
            case 'help':{ //帮助中心
                $url = '/about/help.php';
                break;
            }
            case 'sitemap':{ //网站地图
                $url = '/about/sitemap.php';
                break;
            }
            case 'xkjs':{ //箱卡集市
                $url = '/xkjs/index.php';
                break;
            }
        }
        return $url;
    }
}