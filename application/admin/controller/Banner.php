<?php

namespace app\admin\controller;
use app\admin\model\BannerModel;
use app\admin\model\BannerPositionModel;
use think\Db;

class Banner extends Base{

    //*********************************************广告列表*********************************************//
    /**
     * [index 广告列表]
     * @return [type] [description]
     * @author [田建龙] [864491238@qq.com]
     */
    public function index(){
        $key = input('key');
        $map = [];
        $map['closed'] =0;
        if($key&&$key!==""){
            $map['title'] = ['like',"%" . $key . "%"];
        }
        $Nowpage = input('get.page') ? input('get.page'):1;
        $limits = config('list_rows');// 获取总条数
        $count = Db::name('banner')->where($map)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $ad = new BannerModel();
        $lists = $ad->getAdAll($map, $Nowpage, $limits);
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('val', $key);
        if(input('get.page')){
            return json($lists);
        }
        return $this->fetch();
    }


    /**
     * [add_ad 添加广告]
     * @return [type] [description]
     * @author [田建龙] [864491238@qq.com]
     */
    public function add(){
        if(request()->isAjax()){
            $param = input('post.');
            $param['closed'] = 0;
            $ad = new BannerModel();
            $flag = $ad->insertAd($param);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }
        $position = new BannerPositionModel();
        $this->assign('position',$position->getAllPosition());
        return $this->fetch();
    }


    /**
     * [edit_ad 编辑广告]
     * @return [type] [description]
     * @author [田建龙] [864491238@qq.com]
     */
    public function edit(){
        $ad = new BannerModel();
        if(request()->isPost()){
            $param = input('post.');
            $flag = $ad->editAd($param);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }
        $id = input('param.id');
        $this->assign('ad',$ad->getOneAd($id));
        return $this->fetch();
    }


    /**
     * [del_ad 删除广告]
     * @return [type] [description]
     * @author [田建龙] [864491238@qq.com]
     */
    public function del(){
        $id = input('param.id');
        $ad = new BannerModel();
        $flag = $ad->delAd($id);
        return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
    }


    /**
     * [ad_state 广告状态]
     * @return [type] [description]
     * @author [田建龙] [864491238@qq.com]
     */
    public function state(){
        $id=input('param.id');
        $status = Db::name('banner')->where(array('id'=>$id))->value('status');//判断当前状态情况
        if($status==1)
        {
            $flag = Db::name('banner')->where(array('id'=>$id))->setField(['status'=>0]);
            return json(['code' => 1, 'data' => $flag['data'], 'msg' => '已禁止']);
        } else {
            $flag = Db::name('banner')->where(array('id'=>$id))->setField(['status'=>1]);
            return json(['code' => 0, 'data' => $flag['data'], 'msg' => '已开启']);
        }
    }



    //*********************************************广告位*********************************************//
    /**
     * [index_position 广告位列表]
     * @return [type] [description]
     * @author [田建龙] [864491238@qq.com]
     */
    public function index_position(){
        $ad = new BannerPositionModel();
        $nowpage = input('get.page') ? input('get.page'):1;
        $limits = 10;// 获取总条数
        $count = Db::name('banner_position')->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $list = $ad->getAll($nowpage, $limits);
        $this->assign('nowpage', $nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('list', $list);
        return $this->fetch();
    }


    /**
     * [add_position 添加广告位]
     * @return [type] [description]
     * @author [田建龙] [864491238@qq.com]
     */
    public function add_position(){
        if(request()->isAjax()){
            $param = input('post.');
            $ad = new BannerPositionModel();
            $flag = $ad->insertAdPosition($param);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }
        return $this->fetch();
    }


    /**
     * [edit_position 编辑广告位]
     * @return [type] [description]
     * @author [田建龙] [864491238@qq.com]
     */
    public function edit_position(){
        $ad = new BannerPositionModel();
        if(request()->isAjax()){
            $param = input('post.');
            $flag = $ad->editAdPosition($param);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }
        $id = input('param.id');
        $this->assign('ad',$ad->getOne($id));
        return $this->fetch();
    }


    /**
     * [del_position 删除广告位]
     * @return [type] [description]
     * @author [田建龙] [864491238@qq.com]
     */
    public function del_position(){
        $id = input('param.id');
        $ad = new BannerPositionModel();
        $flag = $ad->delAdPosition($id);
        return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
    }



    /**
     * [position_state 广告位状态]
     * @return [type] [description]
     * @author [田建龙] [864491238@qq.com]
     */
    public function position_state(){
        $id=input('param.id');
        $status = Db::name('banner_position')->where(array('id'=>$id))->value('status');//判断当前状态情况
        if($status==1){
            $flag = Db::name('banner_position')->where(array('id'=>$id))->setField(['status'=>0]);
            return json(['code' => 1, 'data' => $flag['data'], 'msg' => '已禁止']);
        } else {
            $flag = Db::name('banner_position')->where(array('id'=>$id))->setField(['status'=>1]);
            return json(['code' => 0, 'data' => $flag['data'], 'msg' => '已开启']);
        }
    }

}
