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

	public function c() {
		$key     = input('key');
		$arr     = Db::name("merchant")->column("name,id"); //获取用户列表
		$nowPage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');// 获取总条数
		$count   = Db::name('merchant_balance_log');
		// 计算总页面
		$key && $count->where('merchant_id', $arr[$key]);
		$count   = $count->count();
		$allPage = intval(ceil($count / $limits));
		$list    = Db::name('merchant_balance_log');
		$key && $list->where('merchant_id', $arr[$key]);
		$list = $list->page($nowPage, $limits)->order('bal_log_id desc')->select();
		foreach ($list as $k => $v) {
			$list[$k]['merchant_name'] = Db::name('merchant')->where('id', $v['merchant_id'])->value('name');
			$list[$k]['action_type']   = BAL_REC[$v['action_type']];
			$list[$k]['amt_before'] = round($v['amt_before'], 8);
			$list[$k]['amt_after'] = round($v['amt_after'], 8);
			$list[$k]['amt_change'] = round($v['amt_change'], 8);
			$list[$k]['amt_fee'] = round($v['amt_fee'], 8);
			$list[$k]['frozen_amt_before'] = round($v['frozen_amt_before'], 8);
			$list[$k]['frozen_amt_after'] = round($v['frozen_amt_after'], 8);
			$list[$k]['frozen_amt_change'] = round($v['frozen_amt_change'], 8);
			$list[$k]['frozen_amt_fee'] = round($v['frozen_amt_fee'], 8);
		}
		$this->assign('Nowpage', $nowPage); //当前页
		$this->assign('allpage', $allPage); //总页数
		$this->assign('count', $count);
		$this->assign('val', $key);
		$this->assign("search_user", $arr);
		if (input('get.page')) {
			return json($list);
		}
		return $this->fetch();
	}
}

?>