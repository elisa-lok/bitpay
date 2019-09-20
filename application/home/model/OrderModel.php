<?php 
namespace app\home\model;
use think\Model;
use think\db;
use think\request;
class OrderModel extends Model{
    protected $name = 'order_buy';
    public function getOrder($where, $order){
        return $this->where($where)->order($order)->paginate(20, false, ['query'=>Request::instance()->param()]);
    }
    public function getOne($where){
        return $this->where($where)->find();
    }
    public function insertOne($param){
        try{            
            $result = $this->allowField(true)->save($param);
            if(false === $result){
                return ['code' => -1, 'data' => '', 'msg' => $this->getError()];
            }else{
                return ['code' => 1, 'data' => '', 'msg' => '恭喜你，发布成功'];
            }
        }catch( \PDOException $e){
            return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
        }
    }
    /**
     * [updateOne 编辑用户]
     * @author [Max] [1004991278@qq.com]
     */
    public function updateOne($param)
    {
        try{
            $result = $this->allowField(true)->save($param, ['id' => $param['id']]);
            if(false === $result){
                return ['code' => 0, 'data' => '', 'msg' => $this->getError()];
            }else{
                return ['code' => 1, 'data' => '', 'msg' => '修改成功'];
            }
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }
}
?>