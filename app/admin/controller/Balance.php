<?php
namespace app\admin\controller;
use think\Db;

class Balance extends Base {
	public function get() {
		$id   = input('id');
		$user = Db::name('merchant')->where('id', $id)->find();
		showMsg('', 0, ['usdt' => $user['usdt'], 'usdtd' => $user['usdtd'],8]);
	}

	public function set() {
		if (request()->isPost() && request()->isAjax()) {
			$param = input('post.');
			$param['usdt'] == 0 && $param['usdtd'] == 0 && showMsg('余额没变动', 400);
			Db::startTrans();
			if ($param['usdt'] != 0) {
				$type = $param['usdt'] < 0 ? 1 : 0;
				financeLog($param['id'], $param['usdt'], '后台修改USDT余额', $type, $this->username);//添加日志
			}
			if ($param['usdtd'] != 0) {
				$type = $param['usdtd'] < 0 ? 1 : 0;
				financeLog($param['id'], $param['usdtd'], '后台修改USDT冻结余额', $type, $this->username);//添加日志
			}
			!balanceChange(FALSE, $param['id'], $param['usdt'], 0, $param['usdtd'], 0, BAL_SYS, '', '管理员修改') && $this->rollbackShowMsg('修改余额失败');
			Db::commit();
			showMsg('修改成功');
		}
		$id = input('id');
		$this->assign('user', Db::name('merchant')->where('id', $id)->find());
		return $this->fetch();
	}
}