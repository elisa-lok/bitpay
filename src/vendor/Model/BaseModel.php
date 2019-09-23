<?php

namespace BITPAY\Model;

class BaseModel extends \Phalcon\Mvc\Model {
	protected function onConstruct() {
		//读写分离,自动选择主从数据库
		$this->setWriteConnectionService('db');
		$this->setReadConnectionService('db');

		/*
		$metaData = $this->getModelsMetaData();
		foreach ($metaData->getAttributes($this) as $attr) {
			$this->$attr = NULL;
		}
		*/
	}
}
