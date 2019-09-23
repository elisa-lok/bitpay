<?php

namespace BITPAY\Service;

use BITPAY\Model\Bulletin;
use BITPAY\Model\Msg;

/**
 * 用户注册，用户重置密码
 */
class ArticleService extends BaseService
{
	/**
	 * 获取列表
	 * @param int $type 0是文章, 1是公告
	 * @param int  $page
	 * @param int  $limit
	 * @param string $orderBy
	 * @return array
	 */
	public function getList($type = null, $page = 0, $limit = 15, $orderBy = '') {
		$sql = ' status = 1 ' . ($type === null ? '' : ' AND type = ' . (int)$type);
		return Bulletin::find([
			$sql,
			'offset' => $page * $limit,
			'limit'  => $limit,
			'order'  => $orderBy ? $orderBy : ' ctime DESC '
		])->toArray();
	}

	/**
	 * 获得文章详情
	 * @param int $bulletinId
	 * @return array|bool
	 */
	public function getDetail($bulletinId = 0){

	}
}