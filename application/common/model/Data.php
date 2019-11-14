<?php
namespace app\common\model;
use think\Model;
use think\Db;
class Data
{
	public static function head(){
		return [
		      ['id','序号'],
			['username','用户名'],
			['num','客户充值USDT'],
			['from_address','转出地址'],
			['to_address','转入地址'],
			['fee','手续费支出'],
			['mum','实到'],
			['status','状态'],
			['addtime','日期'],
			];
	}
	public static function headAddress(){
	    return [
	        ['id','序号'],
	        ['username','用户名'],
	        ['address','地址'],
	        ['addtime','申请时间'],
	    ];
	}
	public static function headPkorder(){
		return [
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
		];
	}
	public static function headWithdraw(){
	    return [
	        ['id','序号'],
	        ['username','用户名'],
	        ['num','客户提币USDT'],
	        ['address','转出地址'],
	        ['fee','手续费'],
	        ['mum','实到'],
	        ['txid','Txid'],
	        ['status','状态'],
	        ['addtime','创建日期'],
	        ['endtime','审核日期'],
	    ];
	}
	public static function headTibi(){
	    return [
	        ['id','序号'],
	        ['ordersn','订单号'],
	        ['num','提币USDT'],
	        ['address','转出地址'],
	        ['fee','手续费'],
	        ['mum','实到'],
	        ['txid','Txid'],
	        ['status','状态'],
	        ['addtime','创建日期'],
	        ['endtime','审核日期'],
	    ];
	}
	public static function data(){
		return [
			['autoid'=>'1','school'=>'云南大学','addr'=>'云南省','type'=>'综合'],
			['autoid'=>'2','school'=>'云南财经大学','addr'=>'云南省','type'=>'财经'],
			['autoid'=>'3','school'=>'云南民族大学','addr'=>'云南省','type'=>'综合'],
			['autoid'=>'4','school'=>'云南师范大学','addr'=>'云南省','type'=>'师范'],
			['autoid'=>'5','school'=>'云南旅游大学','addr'=>'云南省','type'=>'综合'],
			['autoid'=>'6','school'=>'贵州大学','addr'=>'贵州省','type'=>'综合'],
			['autoid'=>'7','school'=>'贵州财经大学','addr'=>'贵州省','type'=>'财经'],
			['autoid'=>'7','school'=>'贵州师范大学','addr'=>'贵州省','type'=>'师范']
			];
	}
	public static function adindex(){
		return [];
	}
}