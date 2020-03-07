<?php
namespace app\home\model;
use think\Db;
use think\Model;

class BaseModel extends Model {
	/**
	 * [getAllCate 获取文章分类]
	 */
	public function getAllCate() {
		return Db::name('article_cate')->field('id,name,status')->select();
	}
}