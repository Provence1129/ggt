<?php
/**
 * @Copyright (C) 2016.
 * @Description Tools
 * @FileName Tools.php
 * @Author Huang.Xiang
 * @Version 1.0.1
**/

declare(strict_types=1);//strict
namespace App\Index;
use Libs\Comm\From;
use \Libs\Frame\Action;
use \Libs\Frame\Conf;
use \Libs\Tag\Db;
class Tools extends Action{
    //配置
    public function conf(){
    }

    //船运
    public function chuanyun(string $action){
        $Tpl = $this -> getTpl();
        $Tpl -> show('tool/chuanyun/index.html');
    }

    //车站代码
    public function czdm(string $action){
        $Tpl = $this -> getTpl();
        $Tpl -> show('tool/czdm/index.html');
    }

    //航运
    public function hangyun(string $action){
        $Tpl = $this -> getTpl();
        $Tpl -> show('tool/hangyun/hyshy.html');
    }

    //积载因数
    public function jizaiyinsu(string $action){
        $Tpl = $this -> getTpl();
        $type = From::valTrim('type');
        if($type == 'detail'){
            $Tpl -> show('tool/jizaiyinsu/jzys_detail.html');
        }else{
            $Tpl -> show('tool/jizaiyinsu/jzysh.html');
        }
    }

    //集装箱参数
    public function jizhuangxiang(string $action){
        $Tpl = $this -> getTpl();
        $Tpl -> show('tool/jizhuangxiang/index.html');
    }

    //口岸
    public function kouan(string $action){
        $Tpl = $this -> getTpl();
        $Tpl -> show('tool/kouan/kanzf.html');
    }

    //口岸杂费
    public function kouanzafei(string $action){
        $Tpl = $this -> getTpl();
        $Tpl -> show('tool/kouanzafei/kanzf.html');
    }

    //邮政区号
    public function youzhengquhao(string $action){
        $Tpl = $this -> getTpl();
        $type = From::valTrim('type');
        ($type == '') && $type = 'Beijing';
        $tplHtml = 'tool/youzhengquhao/'.$type.'.html';
        $Tpl -> show($tplHtml);
    }
}