<?php
namespace app\home\model;
use think\Db;
use think\Model;

class DetailModel extends Model {
	/**
	 * [getAllArticle 获取文章详情]
	 */
	public function getDetail($id) {
		return Db::name('article')->where('id', $id)->find();
	}
}