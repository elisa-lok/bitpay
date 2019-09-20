<?php 
namespace app\home\model;
use think\Model;
use think\db;
use think\request;
class RechargeModel extends Model{
    protected $name = 'merchant_user_recharge';
    public function getRecharge($where, $order){
        $join=[
            ['__MERCHANT_USER_ADDRESS__ b','b.address=a.to_address','LEFT'],
        ];
        return $this->alias('a')->field('a.*, b.username')->join($join)->where($where)->order($order)->paginate(20, false, ['query'=>Request::instance()->param()]);
    }
    public function getAllByWhere($where, $order){
        $join=[
            ['__MERCHANT_USER_ADDRESS__ b','b.address=a.to_address','LEFT'],
        ];
        return $this->alias('a')->field('a.*, b.username')->join($join)->where($where)->order($order)->select();
    }
}
?>