<?php
namespace app\admin\controller;
use app\admin\model\BannerModel;
use app\admin\model\BannerPositionModel;
use think\Db;

class Banner extends Base {
	//*********************************************挂单列表*********************************************//
	/**
	 * [index 挂单列表]
	 * @return [type] [description]
	 * @author [田建龙] [864491238@qq.com]
	 */
	public function index() {
		$key           = input('key');
		$map           = [];
		$map['closed'] = 0;
		if ($key && $key !== "") {
			$map['title'] = ['like', "%" . $key . "%"];
		}
		$nowPage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');                     // 获取总条数
		$count   = Db::name('banner')->where($map)->count();//计算总页面
		$allPage = intval(ceil($count / $limits));
		$ad      = new BannerModel();
		$lists   = $ad->getAdAll($map, $nowPage, $limits);
		$this->assign('Nowpage', $nowPage); //当前页
		$this->assign('allpage', $allPage); //总页数
		$this->assign('val', $key);
		if (input('get.page')) {
			return json($lists);
		}
		return $this->fetch();
	}

	/**
	 * [add_ad 添加挂单]
	 * @return [type] [description]
	 * @author [田建龙] [864491238@qq.com]
	 */
	public function add() {
		if (request()->isAjax()) {
			$param           = input('post.');
			$param['closed'] = 0;
			$ad              = new BannerModel();
			$flag            = $ad->insertAd($param);
			return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
		}
		$position = new BannerPositionModel();
		$this->assign('position', $position->getAllPosition());
		return $this->fetch();
	}

	/**
	 * [edit_ad 编辑挂单]
	 * @return [type] [description]
	 * @author [田建龙] [864491238@qq.com]
	 */
	public function edit() {
		$ad = new BannerModel();
		if (request()->isPost()) {
			$param = input('post.');
			$flag  = $ad->editAd($param);
			return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
		}
		$id = input('param.id');
		$this->assign('ad', $ad->getOneAd($id));
		return $this->fetch();
	}

	/**
	 * [del_ad 删除挂单]
	 * @return [type] [description]
	 * @author [田建龙] [864491238@qq.com]
	 */
	public function del() {
		$id   = input('param.id');
		$ad   = new BannerModel();
		$flag = $ad->delAd($id);
		return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
	}

	/**
	 * [ad_state 挂单状态]
	 * @return [type] [description]
	 * @author [田建龙] [864491238@qq.com]
	 */
	public function state() {
		$id     = input('param.id');
		$status = Db::name('banner')->where(['id' => $id])->value('status');//判断当前状态情况
		if ($status == 1) {
			$flag = Db::name('banner')->where(['id' => $id])->setField(['status' => 0]);
			return json(['code' => 1, 'data' => $flag['data'], 'msg' => '已禁止']);
		} else {
			$flag = Db::name('banner')->where(['id' => $id])->setField(['status' => 1]);
			return json(['code' => 0, 'data' => $flag['data'], 'msg' => '已开启']);
		}
	}



	//*********************************************挂单位*********************************************//

	/**
	 * [index_position 挂单位列表]
	 * @return [type] [description]
	 * @author [田建龙] [864491238@qq.com]
	 */
	public function index_position() {
		$ad      = new BannerPositionModel();
		$nowPage = input('get.page') ? input('get.page') : 1;
		$limits  = 10;                                  // 获取总条数
		$count   = Db::name('banner_position')->count();//计算总页面
		$allPage = intval(ceil($count / $limits));
		$list    = $ad->getAll($nowPage, $limits);
		$this->assign('nowpage', $nowPage); //当前页
		$this->assign('allpage', $allPage); //总页数
		$this->assign('list', $list);
		return $this->fetch();
	}

	/**
	 * [add_position 添加挂单位]
	 * @return [type] [description]
	 * @author [田建龙] [864491238@qq.com]
	 */
	public function add_position() {
		if (request()->isAjax()) {
			$param = input('post.');
			$ad    = new BannerPositionModel();
			$flag  = $ad->insertAdPosition($param);
			return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
		}
		return $this->fetch();
	}

	/**
	 * [edit_position 编辑挂单位]
	 * @return [type] [description]
	 * @author [田建龙] [864491238@qq.com]
	 */
	public function edit_position() {
		$ad = new BannerPositionModel();
		if (request()->isAjax()) {
			$param = input('post.');
			$flag  = $ad->editAdPosition($param);
			return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
		}
		$id = input('param.id');
		$this->assign('ad', $ad->getOne($id));
		return $this->fetch();
	}

	/**
	 * [del_position 删除挂单位]
	 * @return [type] [description]
	 * @author [田建龙] [864491238@qq.com]
	 */
	public function del_position() {
		$id   = input('param.id');
		$ad   = new BannerPositionModel();
		$flag = $ad->delAdPosition($id);
		return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
	}

	/**
	 * [position_state 挂单位状态]
	 * @return [type] [description]
	 * @author [田建龙] [864491238@qq.com]
	 */
	public function position_state() {
		$id     = input('param.id');
		$status = Db::name('banner_position')->where(['id' => $id])->value('status');//判断当前状态情况
		if ($status == 1) {
			$flag = Db::name('banner_position')->where(['id' => $id])->setField(['status' => 0]);
			return json(['code' => 1, 'data' => $flag['data'], 'msg' => '已禁止']);
		} else {
			$flag = Db::name('banner_position')->where(['id' => $id])->setField(['status' => 1]);
			return json(['code' => 0, 'data' => $flag['data'], 'msg' => '已开启']);
		}
	}
}
