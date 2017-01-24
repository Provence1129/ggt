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
Conf::setModule('box',                          '\App\Box\Box');        //默认模块

//别名配置
Conf::setAliase('box-index',                    'box-index');               // 首页
Conf::setAliase('box-lists',                        'box-lists');               // 列表
Conf::setAliase('box-detail',                       'box-detail');              // 详情
Conf::setAliase('box-tuochemain',                   'box-tuochemain');          // 详情
Conf::setAliase('box-tuochedetail',                 'box-tuochedetail');        // 详情
Conf::setAliase('box-tuocheconsult',                'box-tuocheconsult');
Conf::setAliase('box-order',                        'box-order');              // 订单
Conf::setAliase('box-ordersave',                    'box-ordersave');           // 订单
Conf::setAliase('box-tuocheorder',                  'box-tuocheorder');           // 拖车订单
Conf::setAliase('box-tuocherdersave',               'box-tuocherdersave');      // 拖车订单


//权限配置
Conf::setAuth('box-index',                      'NULL');
Conf::setAuth('box-lists',                      'NULL');
Conf::setAuth('box-detail',                     'NULL');
Conf::setAuth('box-tuochemain',                 'NULL');
Conf::setAuth('box-tuochedetail',               'NULL');
Conf::setAuth('box-tuocheconsult',              'LOGIN');
Conf::setAuth('box-order',                      'LOGIN');              // 订单
Conf::setAuth('box-ordersave',                  'LOGIN');           // 订单
Conf::setAuth('box-tuocheorder',                'LOGIN');           // 拖车订单
Conf::setAuth('box-tuocherdersave',             'LOGIN');           // 拖车订单