<?php
namespace app\admin\controller;
use app\admin\model\LogModel;
use com\IpLocation;
use think\Db;

class Log extends Base {
	/**
	 * [operate_log 操作日志]
	 * @return [type] [description]
	 * @author [田建龙] [864491238@qq.com]
	 */
	public function operate_log() {
		$key = input('key');
		$map = [];
		if ($key && $key !== "") {
			$map['admin_id'] = $key;
		}
		$arr     = Db::name("admin")->column("id,username"); //获取用户列表
		$Nowpage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');                  // 获取总条数
		$count   = Db::name('log')->where($map)->count();//计算总页面
		$allpage = intval(ceil($count / $limits));
		$lists   = Db::name('log')->where($map)->page($Nowpage, $limits)->order('add_time desc')->select();
		$Ip      = new IpLocation('UTFWry.dat'); // 实例化类 参数表示IP地址库文件
		foreach ($lists as $k => $v) {
			$lists[$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
			$lists[$k]['ipaddr']   = $Ip->getlocation($lists[$k]['ip']);
		}
		$this->assign('Nowpage', $Nowpage); //当前页
		$this->assign('allpage', $allpage); //总页数
		$this->assign('count', $count);
		$this->assign("search_user", $arr);
		$this->assign('val', $key);
		if (input('get.page')) {
			return json($lists);
		}
		return $this->fetch();
	}

	/**
	 * [del_log 删除日志]
	 * @return [type] [description]
	 * @author [田建龙] [864491238@qq.com]
	 */
	public function del_log() {
		$id   = input('param.id');
		$log  = new LogModel();
		$flag = $log->delLog($id);
		return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
	}

	public function financelog() {//资金日志
		$key = input('key');
		$map = [];
		if ($key && $key !== "") {
			$map['uid|user'] = ['like', '%' . $key . '%'];
		}
		$arr     = Db::name("merchant")->column("id,name"); //获取用户列表
		$Nowpage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');                         // 获取总条数
		$count   = Db::name('financelog')->where($map)->count();//计算总页面
		$allpage = intval(ceil($count / $limits));
		$lists   = Db::name('financelog')->where($map)->page($Nowpage, $limits)->order('add_time desc')->select();
		foreach ($lists as $k => $v) {
			$lists[$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
		}
		$this->assign('Nowpage', $Nowpage); //当前页
		$this->assign('allpage', $allpage); //总页数
		$this->assign('count', $count);
		$this->assign("search_user", $arr);
		$this->assign('val', $key);
		if (input('get.page')) {
			return json($lists);
		}
		return $this->fetch();
	}

	public function Capitalflow() {
		$key     = input('key');
		$arr     = Db::name("merchant")->column("name,id"); //获取用户列表
		$Nowpage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');// 获取总条数
		$count   = Db::name('merchant_balance_log');
		// 计算总页面
		$key && $count->where('merchant_id', $arr[$key]);
		$count   = $count->count();
		$allpage = intval(ceil($count / $limits));
		$list    = Db::name('merchant_balance_log');
		$key && $list->where('merchant_id', $arr[$key]);
		$list = $list->page($Nowpage, $limits)->order('bal_log_id desc')->select();
		foreach ($list as $k => $v) {
			$list[$k]['merchant_name'] = Db::name('merchant')->where('id', $v['merchant_id'])->value('name');
			$list[$k]['action_type']   = BAL_REC[$v['action_type']];
		}
		$this->assign('Nowpage', $Nowpage); //当前页
		$this->assign('allpage', $allpage); //总页数
		$this->assign('count', $count);
		$this->assign('val', $key);
		$this->assign("search_user", $arr);
		if (input('get.page')) {
			return json($list);
		}
		return $this->fetch();
	}
}
