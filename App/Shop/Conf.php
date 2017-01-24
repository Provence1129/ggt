<?php
/**
 * @Copyright (C) 2016.
 * @Description Conf
 * @FileName Conf.php
 * @Author Huang.Xiang
 * @Version 1.0.1
 **/

declare(strict_types=1);//strict
use \Libs\Frame\Conf;
//模块配置
Conf::setModule('shop',                             '\App\Shop\Shop');              //默认模块

//别名配置
Conf::setAliase('shop',                             'shop-main');                   //商城首页
Conf::setAliase('shop-view',                        'shop-view');                   //商城详细
Conf::setAliase('shop-pay',                         'shop-pay');                    //商城购买
Conf::setAliase('shop-payok',                       'shop-payok');                  //商城购买结果

//权限配置
Conf::setAuth('shop-main',                          'NULL');
Conf::setAuth('shop-view',                          'NULL');
Conf::setAuth('shop-pay',                           'LOGIN');
Conf::setAuth('shop-payok',                         'LOGIN');
