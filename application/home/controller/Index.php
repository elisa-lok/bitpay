<?php
namespace app\home\controller;
use app\home\model\IndexModel;
use think\Db;

class Index extends Base
{

    public function index(){
        //echo config('logo');
        $list = Db::name('member')->limit(3,1)->select();//dump($list);
        $key = input('key');
        $cate_id = input('param.cate_id');
        $map = [];
        if($key&&$key!==""){
            $map['title'] = ['like',"%" . $key . "%"];
        }
        if($cate_id&&$cate_id!==""){
            $map['think_article.cate_id'] = $cate_id;
        }

        $nowpage = input('get.page') ? input('get.page'):1;
        $limits = 5;// 获取总条数
        $article = new IndexModel();
        $count = $article->getByCount($map);//计算总页面
        $allpage = intval(ceil($count / $limits));
        $lists = $article->getArticleByWhere($map, $nowpage, $limits);
        $this->assign('nowpage', $nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('val', $key);
        $this->assign('lists', $lists);
        return $this->fetch('index');
    }

}
