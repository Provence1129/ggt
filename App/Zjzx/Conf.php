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
Conf::setModule('zjzx',                             '\App\Zjzx\Zjzx');      //默认模块

//别名配置
Conf::setAliase('zjzx_main',                        'zjzx-main');           //专家咨询主界面
Conf::setAliase('zjzx_save',                        'zjzx-save');           //保存专家咨询
Conf::setAliase('zjzx-dlsy',                        'zjzx-dlsy');           //多式联运溯源

//权限配置
Conf::setAuth('zjzx-main',                          'NULL');                //专家咨询主界面
Conf::setAuth('zjzx-save',                          'NULL');                //保存数据
Conf::setAuth('zjzx-dlsy',                          'NULL');                //多式联运溯源
