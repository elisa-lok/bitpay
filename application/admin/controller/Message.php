<?php
namespace app\admin\controller;
use app\admin\model\MessageModel;
use think\db;

class Message extends Base {
	public function index() {
		$key                      = input('key');
		$map['think_question.id'] = ['egt', 0];
		if ($key && $key !== "") {
			$where['name|mobile'] = $key;
			$id                   = Db::name('merchant')->where($where)->value('id');
			$map['merchant_id']   = $id;
		}
		$member  = new MessageModel();
		$Nowpage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');       // 获取总条数
		$count   = $member->getAllCount($map);//计算总页面
		$allpage = intval(ceil($count / $limits));
		$lists   = $member->getMessageByWhere($map, $Nowpage, $limits);
		foreach ($lists as $k => $v) {
			$lists[$k]['addtime'] = date("Y/m/d H:i:s", $v['addtime']);
		}
		$this->assign('Nowpage', $Nowpage); //当前页
		$this->assign('allpage', $allpage); //总页数
		$this->assign('val', $key);
		if (input('get.page')) {
			return json($lists);
		}
		return $this->fetch();
	}

	public function delMessage() {
		$id     = input('param.id');
		$model  = new MessageModel();
		$return = $model->delMessage($id);
		return json($return);
	}

	public function editMessage() {
		$model = new MessageModel();
		if (request()->isPost()) {
			$param  = input('post.');
			$return = $model->editMessage(['id' => $param['id'], 'reply' => $param['reply']]);
			return json($return);
		}
		$id = input('param.id');
		$this->assign('message', $model->getOneMessage($id));
		return $this->fetch('editMessage');
	}
}

?>