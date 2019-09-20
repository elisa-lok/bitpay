<?php

namespace app\admin\controller;
use app\admin\model\ConfigModel;
use think\Db;

class Config extends Base{


    /**
     * 获取配置参数
     * @author [田建龙] [864491238@qq.com]
     */
    public function index() {
        $configModel = new ConfigModel();
        $list = $configModel->getAllConfig();
        $config = [];
        foreach ($list as $k => $v) {
            $config[trim($v['name'])] = $v['value'];
        }
        $this->assign('config',$config);
        return $this->fetch();
    }



    /**
     * 批量保存配置
     * @author [田建龙] [864491238@qq.com]
     */
    public function save($config){
        // $this->error('演示系统不能更改配置!');
        $configModel = new ConfigModel();
        if($config && is_array($config)){
            foreach ($config as $name => $value) {
                $map = array('name' => $name);
                $configModel->SaveConfig($map,$value);
            }
        }
      writelog(session('adminuid'),session('username'),'用户【'.session('username').'】更改设置:成功',1);
        cache('db_config_data',null);
        $this->success('保存成功！');
    }

}
