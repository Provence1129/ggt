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
Conf::setModule('user',                             '\App\User\User');          //默认模块
Conf::setModule('userInfo',                         '\App\User\UserInfo');      //用户信息模块
Conf::setModule('Tariffs',                          '\App\User\Tariffs');       //揽货
Conf::setModule('Pallet',                           '\App\User\Pallet');        //发货
Conf::setModule('Entshop',                          '\App\User\Entshop');       //商铺管理
Conf::setModule('Box',                              '\App\User\Box');           //箱卡集市
Conf::setModule('Intertrad',                        '\App\User\Intertrad');     //国际贸易
Conf::setModule('Bid',                              '\App\User\Bid');           //项目竞标
Conf::setModule('Gtb',                              '\App\User\Gtb');           //港通宝
Conf::setModule('Expert',                           '\App\User\Expert');        //我是专家
Conf::setModule('Market',                           '\App\User\Market');        //效果营销
Conf::setModule('Service',                          '\App\User\Service');       //增值服务
Conf::setModule('MyZhidao',                         '\App\User\MyZhidao');      //知道
Conf::setModule('MyBaike',                          '\App\User\MyBaike');       //百科
Conf::setModule('Myydyl',                           '\App\User\Myydyl');        //一带一路

//别名配置
Conf::setAliase('signin',                           'user-signin');             //会员登录
Conf::setAliase('signout',                          'user-signout');            //会员登出
Conf::setAliase('signincode',                       'user-signincode');         //会员验证码登录
Conf::setAliase('reg',                              'user-reg');                //会员注册
Conf::setAliase('forgetpassword',                   'user-forgetPassword');     //忘记密码
Conf::setAliase('signupcode',                       'user-signupcode');         //会员验证码注册
Conf::setAliase('getregcode',                       'user-getregcode');         //获取手机验证码注册
Conf::setAliase('getresetpasscode',                 'user-resetpasscode');      //获取手机验证码重置密码

Conf::setAliase('user',                             'user-main');               //会员首页
Conf::setAliase('user-basic',                       'userInfo-basic');          //基本资料
Conf::setAliase('user-entauth',                     'userInfo-entauth');        //公司认证信息
Conf::setAliase('user-entauthupload',              'userInfo-entauthupload');    //公司认证
Conf::setAliase('user-safe',                        'userInfo-safe');           //帐号安全
Conf::setAliase('user-passwd',                      'userInfo-passwd');         //修改密码
Conf::setAliase('user-account',                     'userInfo-account');        //账户信息
Conf::setAliase('user-order',                       'userInfo-order');          //订单管理
Conf::setAliase('user-address',                     'userInfo-address');        //收货地址管理
Conf::setAliase('user-editmobile',                  'userInfo-editmobile');     //修改或验证手机号
Conf::setAliase('user-editemail',                   'userInfo-editemail');      //修改或验证邮箱
Conf::setAliase('user-punchlist',                   'userInfo-punchlist');      //签到记录
Conf::setAliase('user-invitereg',                   'userInfo-invitereg');      //邀请注册
Conf::setAliase('user-entauthorize',                'userInfo-entauthorize');   //公司授权信息
Conf::setAliase('user-entauthorizeupload',                'userInfo-entauthorizeupload');   //公司授权信息
Conf::setAliase('user-msg',                         'userInfo-msg');            //消息中心
Conf::setAliase('user-ggb',                         'userInfo-ggb');            //港港币
Conf::setAliase('user-lgggb',                       'userInfo-lgggb');          //赚取港港币
Conf::setAliase('user-syggb',                       'userInfo-syggb');          //使用港港币
Conf::setAliase('user-xinyong',                     'userInfo-xinyong');        //信用管理
Conf::setAliase('user-entauthmain',                 'userInfo-entauthmain');    //公司认证

Conf::setAliase('user-help',                        'userInfo-help');           //用户帮助

Conf::setAliase('tariffs',                          'Tariffs-main');            //揽货
Conf::setAliase('tariffs-multi',                    'Tariffs-typeMulti');       //揽货多式联运
Conf::setAliase('tariffs-air',                      'Tariffs-typeAir');         //揽货空运
Conf::setAliase('tariffs-detect',                   'Tariffs-typeDetect');      //揽货报关检测
Conf::setAliase('tariffs-land',                     'Tariffs-typeLand');        //揽货公路运输
Conf::setAliase('tariffs-railway',                  'Tariffs-typeRailway');     //揽货铁运
Conf::setAliase('tariffs-sea',                      'Tariffs-typeSea');         //揽货海运
Conf::setAliase('tariffs-storage',                  'Tariffs-typeStorage');     //揽货仓储
Conf::setAliase('tariffs-manage',                   'Tariffs-manage');          //揽货管理
Conf::setAliase('tariffs-recv',                     'Tariffs-recv');            //收到的询单
Conf::setAliase('tariffs-send',                     'Tariffs-send');            //我的询单

Conf::setAliase('pallet',                           'Pallet-main');             //发货
Conf::setAliase('pallet-multi',                     'Pallet-typeMulti');        //发货多式联运
Conf::setAliase('pallet-air',                       'Pallet-typeAir');          //发货空运
Conf::setAliase('pallet-detect',                    'Pallet-typeDetect');       //发货报关检测
Conf::setAliase('pallet-land',                      'Pallet-typeLand');         //发货公路运输
Conf::setAliase('pallet-railway',                   'Pallet-typeRailway');      //发货铁运
Conf::setAliase('pallet-sea',                       'Pallet-typeSea');          //发货海运
Conf::setAliase('pallet-storage',                   'Pallet-typeStorage');      //发货仓储
Conf::setAliase('pallet-manage',                    'Pallet-manage');           //发货管理
Conf::setAliase('pallet-recv',                      'Pallet-recv');             //收到的询单
Conf::setAliase('pallet-send',                      'Pallet-send');             //我的询单

Conf::setAliase('entshop',                          'Entshop-main');            //商铺管理
Conf::setAliase('entshop-set',                      'Entshop-setting');         //商铺设置
Conf::setAliase('entshop-msgleave',                 'Entshop-msgleave');        //留言管理
Conf::setAliase('entshop-reviews',                  'Entshop-reviews');         //点评管理
Conf::setAliase('entshop-gotomain',                 'Entshop-gotomain');        //进入商铺
Conf::setAliase('entshop-moban',                    'Entshop-moban');           //模板选择

Conf::setAliase('box',                                      'Box-main');                                //箱卡集市
Conf::setAliase('box-sendpurcha',                           'Box-sendpurcha');                          //询价管理采购
Conf::setAliase('box-orderpurcha',                          'Box-orderpurcha');                         //订单管理采购
Conf::setAliase('box-manage',                               'Box-manage');                              //箱卡管理
Conf::setAliase('box-releasecontainer',                     'Box-releasecontainer');                    //询价管理
Conf::setAliase('box-releasecontainer-save',                'Box-releasecontainer_save');               //箱卡保存
Conf::setAliase('box-releasecontainer-ChangeSaleStatus',    'Box-releasecontainer_ChangeSaleStatus');   //箱卡上下架
Conf::setAliase('box-releasecontainer-del',                 'Box-releasecontainer_del');                //箱卡上下架
Conf::setAliase('box-upimg',                                'Box-upimg');                               //上传封面图

Conf::setAliase('box-releasecard',                  'Box-releasecard');         //集装箱信息发布
Conf::setAliase('box-recv',                         'Box-recv');                //询价管理
Conf::setAliase('box-order',                        'Box-order');               //订单管理
Conf::setAliase('box-purchamore',                   'Box-purchamore');          //询价详情
Conf::setAliase('box-tuoche',                       'Box-tuoche');              //拖车管理
Conf::setAliase('box-addtuoche',                    'Box-addtuoche');           //添加拖车信息
Conf::setAliase('box-addtuoche-save',               'Box-addtuoche_save');      //添加拖车信息
Conf::setAliase('box-deltuoche',                    'Box-deltuoche');           //删除拖车
Conf::setAliase('box-getdriver',                    'Box-getdriver');           //获取司机
Conf::setAliase('box-adddriver',                    'Box-adddriver');           //添加司机
Conf::setAliase('box-savedriver',                   'Box-savedriver');          //保存司机
Conf::setAliase('box-deldriver',                    'Box-deldriver');           //删除司机
Conf::setAliase('box-driverupimg',                  'Box-driverupimg');         //上传司机头像
Conf::setAliase('box-getcar',                       'Box-getcar');              //获取司机
Conf::setAliase('box-addcar',                       'Box-addcar');              //添加司机
Conf::setAliase('box-savecar',                      'Box-savecar');             //保存司机
Conf::setAliase('box-delcar',                       'Box-delcar');              //删除司机
Conf::setAliase('box-carupimg',                     'Box-carupimg');            //上传司机头像
Conf::setAliase('box-recvconsult',                  'Box-recvconsult');         //商家咨询列表
Conf::setAliase('box-recvorder',                    'Box-recvorder');           //商家订单
Conf::setAliase('box-sendconsult',                  'Box-sendconsult');         //采购咨询列表
Conf::setAliase('box-sendorder',                    'Box-sendorder');           //采购订单
Conf::setAliase('box-recvordercheak',               'Box-recvordercheak');      //采购订单
Conf::setAliase('box-recvordermor',                 'Box-recvordermor');        //采购订单
Conf::setAliase('box-tuochesendorder',              'Box-tuochesendorder');     //采购订单
Conf::setAliase('box-tuocherecvorder',              'Box-tuocherecvorder');     //采购订单
Conf::setAliase('box-tuocherecvordercheak',         'Box-tuocherecvordercheak');     //采购订单
Conf::setAliase('box-tuocherecvordermor',           'Box-tuocherecvordermor');     //采购订单





Conf::setAliase('intertrad',                        'Intertrad-main');          //国际贸易
Conf::setAliase('intertrad-manageproduct',          'Intertrad-manageproduct'); //产品管理
Conf::setAliase('intertrad-releaseproduct',         'Intertrad-releaseproduct');//产品发布
Conf::setAliase('intertrad-managepurcha',           'Intertrad-managepurcha');  //采购需求管理
Conf::setAliase('intertrad-releasepurcha',          'Intertrad-releasepurcha'); //采购需求发布
Conf::setAliase('intertrad-recv',                   'Intertrad-recv');          //我的咨询
Conf::setAliase('intertrad-recvhf',                 'Intertrad-recvhf');        //我的咨询回复
Conf::setAliase('intertrad-order',                  'Intertrad-order');         //我的订单
Conf::setAliase('intertrad-orderview',              'Intertrad-orderview');     //我的订单查看
Conf::setAliase('intertrad-shoporder',              'Intertrad-shoporder');     //积分商城订单查看

Conf::setAliase('bid',                              'Bid-main');                //项目竞标
Conf::setAliase('bid-manage',                       'Bid-manage');              //招标公告管理
Conf::setAliase('bid-release',                      'Bid-release');             //发布招标公告

Conf::setAliase('gtb',                              'Gtb-main');                //港通宝
Conf::setAliase('gtb-insurance',                    'Gtb-insurance');           //我的保险
Conf::setAliase('gtb-financing',                    'Gtb-financing');           //融资管理
Conf::setAliase('gtb-financingrecord',              'Gtb-financingrecord');     //投资约谈管理
Conf::setAliase('gtb-financingfb',                  'Gtb-financingRelease');    //发布融资
Conf::setAliase('gtb-investment',                   'Gtb-investment');          //投资管理
Conf::setAliase('gtb-investmentfb',                 'Gtb-investmentRelease');   //发布投资
Conf::setAliase('gtb-investmentrecord',             'Gtb-investmentrecord');    //投递项目管理

Conf::setAliase('expert',                           'Expert-main');             //我是专家
Conf::setAliase('expert-favorite',                  'Expert-favorite');         //我的收藏
Conf::setAliase('expert-release',                   'Expert-release');          //案例发布
Conf::setAliase('expert-manage',                    'Expert-manage');           //案例管理
Conf::setAliase('expert-save',                      'Expert-save');             //案例保存
Conf::setAliase('expert-del',                       'Expert-del');              //案例删除
Conf::setAliase('expert-upfile',                    'Expert-upfile');           //案例删除


Conf::setAliase('market',                           'Market-main');             //效果营销
Conf::setAliase('market-visitor',                   'Market-visitor');          //访客分析
Conf::setAliase('market-send',                      'Market-send');             //询盘分析

Conf::setAliase('service',                          'Service-main');            //增值服务
Conf::setAliase('service-appmy',                    'Service-appmy');           //我的应用
Conf::setAliase('service-appall',                   'Service-appall');          //全部应用
Conf::setAliase('service-appview',                  'Service-appview');         //应用详细

Conf::setAliase('myzhidao',                         'MyZhidao-main');           //知道管理
Conf::setAliase('myzhidao-add',                     'MyZhidao-add');            //我要提问
Conf::setAliase('myzhidao-save',                    'MyZhidao-save');           //保存提问
Conf::setAliase('myzhidao-del',                     'MyZhidao-del');            //删除
Conf::setAliase('myzhidao-close',                   'MyZhidao-close');          //关闭
Conf::setAliase('myzhidao-upimg',                   'MyZhidao-upimg');          //上传封面图
Conf::setAliase('myzhidao-question',                'MyZhidao-question');       //我的问题
Conf::setAliase('myzhidao-answer',                  'MyZhidao-answer');         //我的回答
Conf::setAliase('myzhidao-answerwaite',             'MyZhidao-answerwaite');    //等我答
Conf::setAliase('myzhidao-waitanswer',              'MyZhidao-waitanswer');     //回答页
Conf::setAliase('myzhidao-waitanswersave',          'MyZhidao-waitanswersave'); //回答页
Conf::setAliase('myzhidao-showwaitanswer',          'MyZhidao-showwaitanswer'); //查看回答

Conf::setAliase('mybaike',                          'MyBaike-main');            //百科管理
Conf::setAliase('mybaike-manage',                   'MyBaike-manage');          //词条管理
Conf::setAliase('mybaike-release',                  'MyBaike-release');         //发布词条
Conf::setAliase('mybaike-save',                     'MyBaike-save');            //词条管理
Conf::setAliase('mybaike-del',                      'MyBaike-del');             //词条管理
Conf::setAliase('mybaike-upimg',                    'MyBaike-upimg');           //上传封面图
Conf::setAliase('mybaike-favorite',                 'MyBaike-favorite');        //我的收藏

//权限配置
Conf::setAuth('user-signin',                        'NULL');                //会员登录
Conf::setAuth('user-signout',                       'NULL');                //会员登出
Conf::setAuth('user-signincode',                    'NULL');                //会员验证码登录
Conf::setAuth('user-reg',                           'NULL');                //会员注册
Conf::setAuth('user-forgetPassword',                'NULL');                //忘记密码
Conf::setAuth('user-signupcode',                    'NULL');                //会员验证码注册
Conf::setAuth('user-getregcode',                    'NULL');                //获取手机验证码注册
Conf::setAuth('user-resetpasscode',                 'NULL');                //获取手机验证码重置密码

Conf::setAuth('user-main',                          'LOGIN');               //会员首页
Conf::setAuth('userInfo-basic',                     'LOGIN');               //基本资料
Conf::setAuth('userInfo-entauth',                   'LOGIN');               //公司认证信息
Conf::setAuth('userInfo-entauthupload',                   'LOGIN');               //公司认证信息
Conf::setAuth('userInfo-safe',                      'LOGIN');               //帐号安全
Conf::setAuth('userInfo-passwd',                    'LOGIN');               //修改密码
Conf::setAuth('userInfo-account',                   'LOGIN');               //账户信息
Conf::setAuth('userInfo-order',                     'LOGIN');               //订单管理
Conf::setAuth('userInfo-address',                   'LOGIN');               //收货地址管理
Conf::setAuth('userInfo-editmobile',                'LOGIN');               //修改或验证手机号
Conf::setAuth('userInfo-editemail',                 'LOGIN');               //修改或验证邮箱
Conf::setAuth('userInfo-punchlist',                 'LOGIN');               //签到记录
Conf::setAuth('userInfo-invitereg',                 'LOGIN');               //邀请注册
Conf::setAuth('userInfo-entauthorize',              'LOGIN');               //公司授权信息
Conf::setAuth('userInfo-entauthorizeupload',              'LOGIN');               //公司授权信息
Conf::setAuth('userInfo-msg',                       'LOGIN');               //消息中心
Conf::setAuth('userInfo-ggb',                       'LOGIN');               //港港币
Conf::setAuth('userInfo-lgggb',                     'LOGIN');               //赚取港港币
Conf::setAuth('userInfo-syggb',                     'LOGIN');               //使用港港币
Conf::setAuth('userInfo-xinyong',                   'LOGIN');               //信用管理
Conf::setAuth('userInfo-entauthmain',               'LOGIN');               //公司认证
Conf::setAuth('userInfo-help',                      'LOGIN');               //用户帮助

Conf::setAuth('Tariffs-main',                       'LOGIN');               //揽货
Conf::setAuth('Tariffs-typeMulti',                  'LOGIN');               //揽货多式联运
Conf::setAuth('Tariffs-typeAir',                    'LOGIN');               //揽货空运
Conf::setAuth('Tariffs-typeDetect',                 'LOGIN');               //揽货报关检测
Conf::setAuth('Tariffs-typeLand',                   'LOGIN');               //揽货公路运输
Conf::setAuth('Tariffs-typeRailway',                'LOGIN');               //揽货铁运
Conf::setAuth('Tariffs-typeSea',                    'LOGIN');               //揽货海运
Conf::setAuth('Tariffs-typeStorage',                'LOGIN');               //揽货仓储
Conf::setAuth('Tariffs-manage',                     'LOGIN');               //揽货管理
Conf::setAuth('Tariffs-recv',                       'LOGIN');               //收到的询单
Conf::setAuth('Tariffs-send',                       'LOGIN');               //我的询单

Conf::setAuth('Pallet-main',                        'LOGIN');               //发货
Conf::setAuth('Pallet-typeMulti',                   'LOGIN');               //发货多式联运
Conf::setAuth('Pallet-typeAir',                     'LOGIN');               //发货空运
Conf::setAuth('Pallet-typeDetect',                  'LOGIN');               //发货报关检测
Conf::setAuth('Pallet-typeLand',                    'LOGIN');               //发货公路运输
Conf::setAuth('Pallet-typeRailway',                 'LOGIN');               //发货铁运
Conf::setAuth('Pallet-typeSea',                     'LOGIN');               //发货海运
Conf::setAuth('Pallet-typeStorage',                 'LOGIN');               //发货仓储
Conf::setAuth('Pallet-manage',                      'LOGIN');               //发货管理
Conf::setAuth('Pallet-recv',                        'LOGIN');               //收到的询单
Conf::setAuth('Pallet-send',                        'LOGIN');               //我的询单

Conf::setAuth('Entshop-main',                       'LOGIN');               //商铺管理
Conf::setAuth('Entshop-setting',                    'LOGIN');               //商铺设置
Conf::setAuth('Entshop-msgleave',                   'LOGIN');               //留言管理
Conf::setAuth('Entshop-reviews',                    'LOGIN');               //点评管理
Conf::setAuth('Entshop-gotomain',                   'LOGIN');               //进入商铺
Conf::setAuth('Entshop-moban',                      'LOGIN');               //模板选择

Conf::setAuth('Box-main',                           'LOGIN');               //箱卡集市
Conf::setAuth('Box-sendpurcha',                     'LOGIN');               //询价管理采购
Conf::setAuth('Box-orderpurcha',                    'LOGIN');               //订单管理采购
Conf::setAuth('Box-manage',                         'LOGIN');               //箱卡管理
Conf::setAuth('Box-releasecontainer',               'LOGIN');               //询价管理
Conf::setAuth('Box-releasecontainer_save',          'LOGIN');               //询价管理
Conf::setAuth('Box-releasecontainer_ChangeSaleStatus',  'LOGIN');           //询价管理
Conf::setAuth('Box-releasecontainer_del',          'LOGIN');                //询价管理
Conf::setAuth('Box-releasecard',                    'LOGIN');               //集装箱信息发布
Conf::setAuth('Box-recv',                           'LOGIN');               //询价管理
Conf::setAuth('Box-order',                          'LOGIN');               //订单管理
Conf::setAuth('Box-purchamore',                     'LOGIN');               //询价详情
Conf::setAuth('Box-tuoche',                         'LOGIN');               //拖车管理
Conf::setAuth('Box-addtuoche',                      'LOGIN');               //添加拖车信息
Conf::setAuth('Box-addtuoche_save',                 'LOGIN');               //添加拖车信息
Conf::setAuth('Box-deltuoche',                      'LOGIN');               //删除拖车
Conf::setAuth('Box-upimg',                          'LOGIN');               //上传封面图
Conf::setAuth('Box-getdriver',                      'LOGIN');               //获取司机信息
Conf::setAuth('Box-adddriver',                      'LOGIN');               //添加司机
Conf::setAuth('Box-savedriver',                     'LOGIN');               //保存司机
Conf::setAuth('Box-deldriver',                      'LOGIN');               //删除司机
Conf::setAuth('Box-driverupimg',                    'LOGIN');               //上传司机头像
Conf::setAuth('Box-getcar',                         'LOGIN');               //获取司机信息
Conf::setAuth('Box-addcar',                         'LOGIN');               //添加司机
Conf::setAuth('Box-savecar',                        'LOGIN');               //保存司机
Conf::setAuth('Box-delcar',                         'LOGIN');               //删除司机
Conf::setAuth('Box-carupimg',                       'LOGIN');               //上传司机头像
Conf::setAuth('Box-recvconsult',                    'LOGIN');               //商家咨询列表
Conf::setAuth('Box-recvorder',                      'LOGIN');               //商家订单
Conf::setAuth('Box-sendconsult',                    'LOGIN');               //采购咨询列表
Conf::setAuth('Box-sendorder',                      'LOGIN');               //采购订单
Conf::setAuth('Box-recvordercheak',                 'LOGIN');               //采购订单
Conf::setAuth('Box-recvordermor',                   'LOGIN');               //采购订单
Conf::setAuth('Box-tuochesendorder',                'LOGIN');               //采购订单
Conf::setAuth('Box-tuocherecvorder',                'LOGIN');               //采购订单
Conf::setAuth('Box-tuocherecvordercheak',           'LOGIN');               //采购订单
Conf::setAuth('Box-tuocherecvordermor',             'LOGIN');               //采购订单


Conf::setAuth('Intertrad-main',                     'LOGIN');               //国际贸易
Conf::setAuth('Intertrad-manageproduct',            'LOGIN');               //产品管理
Conf::setAuth('Intertrad-releaseproduct',           'LOGIN');               //产品发布
Conf::setAuth('Intertrad-managepurcha',             'LOGIN');               //采购需求管理
Conf::setAuth('Intertrad-releasepurcha',            'LOGIN');               //采购需求发布
Conf::setAuth('Intertrad-recv',                     'LOGIN');               //我的咨询
Conf::setAuth('Intertrad-recvhf',                   'LOGIN');               //我的咨询回复
Conf::setAuth('Intertrad-order',                    'LOGIN');               //我的订单
Conf::setAuth('Intertrad-orderview',                'LOGIN');               //我的订单查看
Conf::setAuth('Intertrad-shoporder',                'LOGIN');               //积分商城订单查看

Conf::setAuth('Bid-main',                           'LOGIN');               //项目竞标
Conf::setAuth('Bid-manage',                         'LOGIN');               //招标公告管理
Conf::setAuth('Bid-release',                        'LOGIN');               //发布招标公告

Conf::setAuth('Gtb-main',                           'LOGIN');               //港通宝
Conf::setAuth('Gtb-insurance',                      'LOGIN');               //我的保险
Conf::setAuth('Gtb-financing',                      'LOGIN');               //融资管理
Conf::setAuth('Gtb-financingrecord',                'LOGIN');               //投资约谈管理
Conf::setAuth('Gtb-financingRelease',               'LOGIN');               //发布融资
Conf::setAuth('Gtb-investment',                     'LOGIN');               //投资管理
Conf::setAuth('Gtb-investmentRelease',              'LOGIN');               //发布投资
Conf::setAuth('Gtb-investmentrecord',               'LOGIN');               //投递项目管理

Conf::setAuth('Expert-main',                        'LOGIN');               //我是专家
Conf::setAuth('Expert-favorite',                    'LOGIN');               //我的收藏
Conf::setAuth('Expert-release',                     'LOGIN');               //案例发布
Conf::setAuth('Expert-manage',                      'LOGIN');               //案例管理
Conf::setAuth('Expert-save',                        'LOGIN');               //案例保存
Conf::setAuth('Expert-del',                         'LOGIN');               //案例删除
Conf::setAuth('Expert-upfile',                      'LOGIN');               //上传图片



Conf::setAuth('Market-main',                        'LOGIN');               //效果营销
Conf::setAuth('Market-visitor',                     'LOGIN');               //访客分析
Conf::setAuth('Market-send',                        'LOGIN');               //询盘分析

Conf::setAuth('Service-main',                       'LOGIN');               //增值服务
Conf::setAuth('Service-appmy',                      'LOGIN');               //我的应用
Conf::setAuth('Service-appall',                     'LOGIN');               //全部应用
Conf::setAuth('Service-appview',                    'LOGIN');               //应用详细

Conf::setAuth('MyZhidao-main',                      'LOGIN');               //知道
Conf::setAuth('MyZhidao-add',                       'LOGIN');               //知道管理
Conf::setAuth('MyZhidao-save',                      'LOGIN');               //保存知道
Conf::setAuth('MyZhidao-del',                       'LOGIN');               //删除
Conf::setAuth('MyZhidao-close',                     'LOGIN');               //关闭
Conf::setAuth('MyZhidao-upimg',                     'LOGIN');               //上传封面图
Conf::setAuth('MyZhidao-question',                  'LOGIN');               //我的问题
Conf::setAuth('MyZhidao-answer',                    'LOGIN');               //我的回答
Conf::setAuth('MyZhidao-answerwaite',               'LOGIN');               //等我答
Conf::setAuth('MyZhidao-waitanswer',                'LOGIN');               //回答页
Conf::setAuth('MyZhidao-waitanswersave',            'LOGIN');               //回答页保存
Conf::setAuth('MyZhidao-showwaitanswer',            'LOGIN');               //查看回答

Conf::setAuth('MyBaike-main',                       'LOGIN');               //百科管理
Conf::setAuth('MyBaike-manage',                     'LOGIN');               //词条管理
Conf::setAuth('MyBaike-release',                    'LOGIN');               //发布词条
Conf::setAuth('MyBaike-save',                       'LOGIN');               //词条管理
Conf::setAuth('MyBaike-del',                        'LOGIN');               //词条管理
Conf::setAuth('MyBaike-upimg',                      'LOGIN');               //上传封面图
Conf::setAuth('MyBaike-favorite',                   'LOGIN');               //我的收藏