<?php
namespace app\admin\controller;
use app\admin\model\UserModel;
use app\admin\model\UserType;
use think\Db;

class User extends Base {
	/**
	 * [index 用户列表]
	 * @return [type] [description]
	 * @author [田建龙] [864491238@qq.com]
	 */
	public function index() {
		$key = input('key');
		$map = [];
		if ($key && $key !== '') {
			$map['username'] = ['like', "%" . $key . "%"];
		}
		$page          = input('get.page') ? input('get.page') : 1;
		$rows          = input('get.rows');                      // 获取总条数
		$count         = Db::name('admin')->where($map)->count();//计算总页面
		$user          = new UserModel();
		$lists         = $user->getUsersByWhere($map, $page, $rows);
		$data['list']  = $lists;
		$data['count'] = $count;
		$data['page']  = $page;
		if (input('get.page')) {
			return json($data);
		}
		return $this->fetch();
	}

	/**
	 * [userAdd 添加用户]
	 * @return [type] [description]
	 * @author [田建龙] [864491238@qq.com]
	 */
	public function add() {
		if (request()->isAjax()) {
			$param             = input('post.');
			$param['password'] = md5(md5($param['password']) . config('auth_key'));
			$user              = new UserModel();
			$flag              = $user->insertUser($param);
			$accdata           = [
				'uid'      => $user['id'],
				'group_id' => $param['groupid'],
			];
			$group_access      = Db::name('auth_group_access')->insert($accdata);
			return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
		}
		$role = new UserType();
		$this->assign('role', $role->getRole());
		return $this->fetch();
	}

	/**
	 * [userEdit 编辑用户]
	 * @return [type] [description]
	 * @author [田建龙] [864491238@qq.com]
	 */
	public function edit() {
		$user = new UserModel();
		if (request()->isAjax()) {
			$param = input('post.');
			if (empty($param['password'])) {
				unset($param['password']);
			} else {
				$param['password'] = md5(md5($param['password']) . config('auth_key'));
			}
			$flag         = $user->editUser($param);
			$group_access = Db::name('auth_group_access')->where('uid', $user['id'])->update(['group_id' => $param['groupid']]);
			return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
		}
		$id   = input('param.id');
		$role = new UserType();
		$this->assign([
			'user' => $user->getOneUser($id),
			'role' => $role->getRole()
		]);
		return $this->fetch();
	}

	/**
	 * [UserDel 删除用户]
	 * @return [type] [description]
	 * @author [田建龙] [864491238@qq.com]
	 */
	public function del() {
		$id   = input('param.id');
		$id == 1 && showMsg('超管不允许删除', 400, '');
		$role = new UserModel();
		$flag = $role->delUser($id);
		return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
	}

	/**
	 * [user_state 用户状态]
	 * @return [type] [description]
	 * @author [田建龙] [864491238@qq.com]
	 */
	public function state() {
		$id     = input('param.id');
		$status = Db::name('admin')->where('id', $id)->value('status');//判断当前状态情况
		if ($status == 1) {
			$flag = Db::name('admin')->where('id', $id)->setField(['status' => 0]);
			return json(['code' => 1, 'data' => $flag['data'], 'msg' => '已禁止']);
		} else {
			$flag = Db::name('admin')->where('id', $id)->setField(['status' => 1]);
			return json(['code' => 0, 'data' => $flag['data'], 'msg' => '已开启']);
		}
	}

	/**
	 * 支付方式查看
	 */
	public function payment($id) {
		$data             = [];
		$data['bankcard'] = Db::name('merchant_bankcard')->where('merchant_id=' . $id)->select();
		$data['alipay']   = Db::name('merchant_zfb')->where('merchant_id=' . $id)->select();
		$data['wxpay']    = Db::name('merchant_wx')->where('merchant_id=' . $id)->select();
		$data['unionpay'] = Db::name('merchant_ysf')->where('merchant_id=' . $id)->select();
		$this->assign('data', $data);
		return $this->fetch('user/payment');
	}

	// 删除支付方式
	public function delPayment($type, $id) {
		if ($type == 0) {
			Db::name('merchant_bankcard')->where('id=' . $id)->delete();
		} elseif ($type > 0 && $type < 4) {
			$arr = ['1' => 'merchant_zfb', '2' => 'merchant_wx', '3' => 'merchant_ysf'];
			$res = Db::name($arr[$type])->where('id=' . $id)->find();
			unlink(UPLOAD_PATH . '/uploads/face/' . $res['c_bank_detail']);
			Db::name($arr[$type])->where('id=' . $id)->delete();
		}
		return $this->error('删除成功');
	}
}
