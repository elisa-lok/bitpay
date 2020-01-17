<?php
namespace app\home\controller;
use app\common\model\Data;
use app\common\model\PHPExcel;
use app\home\model\OrderBuyModel;
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
		$limit    = 50;
		$balModel = Db::name('merchant_balance_log');
		$list     = $balModel->where('merchant_id', $this->uid)->order('bal_log_id DESC')->page($page, $limit)->select();
		$count    = $balModel->where('merchant_id', $this->uid)->count();
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

	public function outputOrder() {
		/* [
		['order_no','订单编号'],
		['buy_username','买家'],
		['raw_amount','订单金额'],
		['raw_num','订单数量'],
		['deal_amount','交易金额'],
		['deal_num','交易数量'],
		['deal_price','交易价格'],
		['rec','到账数量'],
		['rec_amount','	到账金额'],
		['fee','手续费数量'],
		['fee_amount','手续费金额'],
		['fee_rate','费率'],
		['ctime','创建时间'],
		['status','交易状态'],
		] */
		!$this->uid && $this->error('请登陆操作');
		$where['buy_id'] = $this->uid;
		$get             = input('get.');
		$order           = 'id DESC';
		$model           = new OrderBuyModel();
		$status          = input('get.status');
		$status > 0 && ($where['status'] = $status);
		(!empty($get['created_at']['start']) && !empty($get['created_at']['end'])) && ($where['ctime'] = ['between', [strtotime($get['created_at']['start']), strtotime($get['created_at']['end'])]]);
		$list = $model->getAllByWhere($where, $order);
		if ($list) {
			$usdtPriceWay = config('usdt_price_way');
			$dealerFee    = 0; //承兑商费用
			$newList      = collection($list)->toArray();
			$sellerIds    = array_unique(array_column($newList, 'sell_id'));
			$mcModel      = Db::name('merchant');
			$agentIds     = $mcModel->where('id', 'in', array_unique($sellerIds))->column('pid', 'id');
			$agFeeRate    = 0;
			$agentIds && ($agFeeRate = $mcModel->where('id', 'in', array_values($agentIds))->column('trader_parent_get', 'id'));
			$addFee    = config('usdt_price_add');
			$statusArr = [0 => '代付款', 1 => '待放行', 4 => '已完成', 5 => '已关闭', 6 => '申诉中', 9 => '订单失败'];
			foreach ($list as $k => $v) {
				$list[$k]['fee_amount'] = $list[$k]['fee'] = $list[$k]['rec_amount'] = $list[$k]['rec'] = $list[$k]['fee_rate'] = 0;
				if ($v['status'] == 4) {
					$agentFeeRate = isset($agentIds[$v['sell_id']]) && isset($agFeeRate[$agentIds[$v['sell_id']]]) ? $agFeeRate[$agentIds[$v['sell_id']]] / 100 : 0;
					($usdtPriceWay == 2) && ($dealerFee = (strpos($addFee, '%') !== FALSE ? $v['deal_price'] * (((float)$addFee) / 100) : $addFee));
					$list[$k]['fee_amount'] = $v['deal_amount'] - (($v['deal_num'] - $v['platform_fee'] - number_format($v['deal_num'] * $agentFeeRate, 8, '.', '')) * ($v['deal_price'] - $dealerFee)); //费用金额
					$list[$k]['fee']        = $list[$k]['fee_amount'] / $v['deal_price'];
					$list[$k]['rec_amount'] = $v['deal_amount'] - $list[$k]['fee_amount'];                                  // 到账费用
					$list[$k]['rec']        = $v['deal_num'] - $list[$k]['fee'];                                            // 到账数量
					$list[$k]['fee_rate']   = number_format($list[$k]['fee_amount'] * 100 / $v['deal_amount'], 1, '.', ''); // 到账数量
				}
				isset($statusArr[$v['status']]) && ($list[$k]['status'] = $statusArr[$v['status']]);
				$list[$k]['ctime'] = date("Y-m-d H:i:s", $v['ctime']);
			}
		}
		//文件名称
		$data                = collection($list)->toArray();
		$Excel['fileName']   = "订单列表" . date('Y年m月d日-His', time());//or $xlsTitle
		$Excel['cellName']   = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N'];
		$Excel['H']          = ['A' => 10, 'B' => 15, 'C' => 15, 'D' => 35, 'E' => 35, 'F' => 15, 'G' => 15, 'H' => 20, 'I' => 30, 'J' => 20, 'K' => 15, 'L' => 20, 'M' => 15, 'N' => 20];//横向水平宽度
		$Excel['V']          = ['1' => 40, '2' => 26];                                                                                                                                    //纵向垂直高度
		$Excel['sheetTitle'] = "订单列表";                                                                                                                                                    //大标题，自定义
		$Excel['xlsCell']    = Data::headPkorder();
		PHPExcel::excelPut($Excel, $data);
	}
}

?>