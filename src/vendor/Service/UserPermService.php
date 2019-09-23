<?php

namespace BITPAY\Service;

/**
 * 用户权限管理
 * Class UserPermService
 * @package BITPAY\Service
 */
class UserPermService extends BaseService {
	var $PermCacheKey = 'user_perm_';
	var $RoleCacheKey = 'user_role_perm_';
	//****************************************** 用户 ******************************************
	public function getPermsByUser($uid){

	}

	public function getPermsByRole($roleId){

	}

	public function getRoleByUser($uid){

	}


	//****************************************** 权限 ******************************************
	// 获取用户权限
	public function getPermsList() {

	}

	// 校验用户权限
	public function checkPerms($uid, $permId) {

	}

	public function setPerms($perms){
		//设置后,马上清除所有的权限

	}

	public function createPerm(){

	}

	public function deletePerm($permId){

	}
	//****************************************** 角色 ******************************************
	// 创建用户角色
	public function createRole($roleId){

	}

	public function getRoleList(){

	}

	public function deleteRole($roleId){

	}

	public function setRole($roleId){

	}

	//****************************************** 其他 ******************************************

	public function cleanOneRoleCache($roleId){
		$this->redis->del($this->redis->keys($this->RoleCacheKey.'*'));
	}

	public function cleanAllPermCache(){
		$this->redis->del($this->redis->keys($this->PermCacheKey.'*'));
	}

	public function cleanAllRoleCache(){
		$this->redis->del($this->redis->keys($this->RoleCacheKey.'*'));
	}
}