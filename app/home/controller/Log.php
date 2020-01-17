<?php
namespace app\home\controller;
use think\Db;

class Log extends Base {
	public function _initialize() {
		parent::_initialize();
		!$this->uid && $this->error('请登陆操作', url('home/login/login'));
	}

	//资金流水
	public function capitalFlow() {
		!$this->uid && $this->error('请登陆操作', url('home/login/login'));
		$page = (int)input('page');
		$page < 1 && ($page = 1);
		$limit = 50;
		$balModel = Db::name('merchant_balance_log');
		$list = $balModel->where('merchant_id', $this->uid)->order('bal_log_id DESC')->page($page, $limit)->select();
		$count = $balModel->where('merchant_id', $this->uid)->count();
		foreach ($list as $k => $v) {
			$list[$k]['action_type']       = BAL_REC[$v['action_type']];
			$list[$k]['amt_before']        = round($v['amt_before'], 8);
			$list[$k]['amt_after']         = round($v['amt_after'], 8);
			$list[$k]['amt_change']        = round($v['amt_change'], 8);
			$list[$k]['amt_fee']           = round($v['amt_fee'], 8);
			$list[$k]['frozen_amt_before'] = round($v['frozen_amt_before'], 8);
			$list[$k]['frozen_amt_after']  = round($v['frozen_amt_after'], 8);
			$list[$k]['frozen_amt_change'] = round($v['frozen_amt_change'], 8);
			$list[$k]['frozen_amt_fee']    = round($v['frozen_amt_fee'], 8);
		}
		$this->assign('list', $list);
		$this->assign('count', $count);
		$this->assign('cur_page', $page);
		$this->assign('total_page', (int)ceil($count / $limit));
		return $this->fetch();
	}
}

?>