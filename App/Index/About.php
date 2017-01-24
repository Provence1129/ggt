<?php
/**
 * @Copyright (C) 2016.
 * @Description About
 * @FileName About.php
 * @Author Huang.Xiang
 * @Version 1.0.1
**/

declare(strict_types=1);//strict
namespace App\Index;
use \Libs\Frame\Action;
use \Libs\Tag\Db;
class About extends Action{
    private $Db         = NULL;         //数据库对象
    //配置
    public function conf(){
        $this -> Db         = Db::tag('DB.USER', 'GMY');
    }

    //Main
    public function main(string $action){
        $Tpl = $this -> getTpl();
        $Tpl -> show('About/index.html');
    }

    //assistant
    public function assistant(string $action){
        $Tpl = $this -> getTpl();
        $Tpl -> show('About/assistant.html');
    }

    //careers
    public function careers(string $action){
        $Tpl = $this -> getTpl();
        $Tpl -> show('About/careers.html');
    }

    //contact
    public function contact(string $action){
        $Tpl = $this -> getTpl();
        $Tpl -> show('About/contact.html');
    }

    //customer
    public function customer(string $action){
        $Tpl = $this -> getTpl();
        $Tpl -> show('About/customer.html');
    }

    //help
    public function help(string $action){
        $Tpl = $this -> getTpl();
        $Tpl -> show('About/help.html');
    }

    //open
    public function open(string $action){
        $Tpl = $this -> getTpl();
        $Tpl -> show('About/open.html');
    }

   //zhaopin
    public function zhaopin(string $action){
        $Tpl = $this -> getTpl();
        $sql = 'SELECT * FROM '.$this -> Db -> getTableNameAll('zhaopin').' where zp_id =  1';
        $item = $this -> Db -> getDataOne($sql);
        $Tpl->assign("item",$item);
        $Tpl -> show('About/zhaopin.html');
    }
    
    //service
    public function service(string $action){
        $Tpl = $this -> getTpl();
        $Tpl -> show('About/service.html');
    }

    //sitemap
    public function sitemap(string $action){
        $Tpl = $this -> getTpl();
        $Tpl -> show('About/sitemap.html');
    }
}