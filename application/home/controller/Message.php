<?php
namespace app\home\controller;
use think\db;
use app\home\model\MessageModel;
class Message extends Base{

    public function index(){
        if(!session('uid')){
            $this->error('请登录操作');
        }
        $model = new MessageModel();
        $where['merchant_id'] = session('uid');
        $order = 'id desc';
        $this->assign('list', $model->getQuestion($where, $order));
        $where['reply'] = 'not null';
        $this->assign('replylist', $model->getQuestion($where, $order));
        $where['reply'] = null;//dump($where);
        $this->assign('notreplylist', $model->getQuestion($where, $order));
        return $this->fetch();
    }
    public function add(){
        if(!session('uid')){
            $this->error('请登陆操作');
        }
        if(request()->isPost()){
            $type = input('post.type');
            if(!in_array($type, array(1, 2, 3))){
                $this->error('请选择问题类型');
            }
            $content = input('post.content');
            if(empty($content) || strlen($content) < 10){
                $this->error('请填写问题内容，不低于10个字符');
            }
            $model = new MessageModel();
            $return = $model->insertQuestion(['type'=>$type, 'content'=>$content, 'merchant_id'=>session('uid'), 'addtime'=>time()]);
            if($return['code'] == 1){
                $this->success($return['msg'], url('home/message/index'));
            }else{
                $this->error($return['msg']);
            }
        }else{
            return $this->fetch();
        }
    }
}
?>