<?php

namespace BITPAY\Service;

use BITPAY\Model\SysSetting;

/**
 * 系统设置
 * Class SysService
 * @package BITPAY\Service\
 */
class SysService extends BaseService {
	const SysWebSiteUrl = 'SYS';


	private $sysCfgKey    = 'system_setting_key';
	private $sysCfgExpire = 3600;

	public function getAllSysSetting($force = FALSE) {
		$set = $this->cache->get($this->sysCfgKey);
		if(!$set || $force){
			$set = SysSetting::find()->toArray();
			$this->cache->save($this->sysCfgKey, $set,$this->sysCfgExpire);
		}
		return $set;
	}

	public function getMainCfg(){

	}

	public function setWebSiteInfo(){

	}
}