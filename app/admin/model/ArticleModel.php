<?php
namespace app\admin\model;
use think\Model;

class ArticleModel extends Model {
	protected $name = 'article';
	// 开启自动写入时间戳字段
	protected $autoWriteTimestamp = TRUE;

	/**
	 * 根据搜索条件获取用户列表信息
	 */
	public function getArticleByWhere($map, $nowPage, $limits) {
		return $this->field('think_article.*,name')->join('think_article_cate', 'think_article.cate_id = think_article_cate.id')->where($map)->page($nowPage, $limits)->order('id DESC')->select();
	}

	/**
	 * [insertArticle 添加文章]
	 */
	public function insertArticle($param) {
		try {
			$result = $this->allowField(TRUE)->save($param);
			if (FALSE === $result) {
				return ['code' => -1, 'data' => '', 'msg' => $this->getError()];
			}
			return ['code' => 1, 'data' => '', 'msg' => '文章添加成功'];
		} catch (PDOException $e) {
			return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
		}
	}

	/**
	 * [updateArticle 编辑文章]
	 */
	public function updateArticle($param) {
		try {
			$result = $this->allowField(TRUE)->save($param, ['id' => $param['id']]);
			if (FALSE === $result) {
				return ['code' => 0, 'data' => '', 'msg' => $this->getError()];
			}
			return ['code' => 1, 'data' => '', 'msg' => '文章编辑成功'];
		} catch (PDOException $e) {
			return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
		}
	}

	/**
	 * [getOneArticle 根据文章id获取一条信息]
	 */
	public function getOneArticle($id) {
		return $this->where('id', $id)->find();
	}

	/**
	 * [delArticle 删除文章]
	 */
	public function delArticle($id) {
		try {
			$where['id'] = ['in', $id];
			$this->where($where)->delete();
			return ['code' => 1, 'data' => '', 'msg' => '文章删除成功'];
		} catch (PDOException $e) {
			return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
		}
	}
}