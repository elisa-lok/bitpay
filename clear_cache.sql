TRUNCATE TABLE `think_login_log`;
TRUNCATE TABLE `think_log`;
TRUNCATE TABLE `think_merchant_log`;
TRUNCATE TABLE `think_order_buy`;
TRUNCATE TABLE `think_order_sell`;
TRUNCATE TABLE `think_statistics`;
UPDATE `think_merchant` SET `usdt` = 1000,`usdtd`=0, `order_sell_success_num` = 0, `order_buy_success_num` =0,`ad_on_sell`=0,`ad_on_buy`=0;
TRUNCATE TABLE `think_statistics`;
TRUNCATE TABLE `think_agent_reward`;
TRUNCATE TABLE `think_trader_reward`;
TRUNCATE TABLE `think_ad_buy`;
TRUNCATE TABLE `think_ad_sell`;
TRUNCATE TABLE `think_address`;
TRUNCATE TABLE `think_financelog`;


ALTER TABLE `pay`.`think_merchant`
  DROP COLUMN `c_bank`,
  DROP COLUMN `c_bank_detail`,
  DROP COLUMN `c_bank_card`,
  DROP COLUMN `c_wechat_account`,
  DROP COLUMN `c_wechat_img`,
  DROP COLUMN `c_alipay_account`,
  DROP COLUMN `c_alipay_img`,
  CHANGE `pid` `pid` INT (11) DEFAULT 0 NULL COMMENT '邀请者ID' AFTER `id`,

  CHANGE `reg_type` `reg_type` TINYINT (4) NULL COMMENT '1,商户,2,承兑商,3,代理商' AFTER `status`,
  CHANGE `key` `key` CHAR (32) CHARSET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'MD5Key' AFTER `usdtd`,
  CHANGE `idcard` `idcard` VARCHAR (50) CHARSET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '身份证号' AFTER `ga`,
  CHANGE `idcard_fan` `idcard_fan` VARCHAR (255) CHARSET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '反面照' AFTER `idcard`,
  CHANGE `idcard_zheng` `idcard_zheng` VARCHAR (255) CHARSET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '正面照' AFTER `idcard_fan`,
  CHANGE `invite` `invite` VARCHAR (15) CHARSET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '邀请码' AFTER `device`,
  CHANGE `name` `name` VARCHAR (32) CHARSET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '姓名';
  CHANGE `agent_check` `agent_check` TINYINT (4) DEFAULT 3 NULL COMMENT '0:未提交，1：通过，2：拒绝，3：已提交申请',
  ADD COLUMN `tariff` DECIMAL (8, 6) DEFAULT 0.6 NOT NULL COMMENT '费率一级代理0.012, 商户默认0.006' AFTER `addtime`
  ;