<?php
namespace app\home\model;
use think\Cache;
use think\Db;
use think\Model;

class BaseModel extends Model {
	/**
	 * [getAllCate 获取文章分类]
	 */
	public function getAllCate() {
		$res = Cache::get('category');
		if(!$res){
			$res = Db::name('article_cate')->field('id,name,status')->select();
			Cache::set('category',$res, 86400);
		}
		return $res;
	}
}