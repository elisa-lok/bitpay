# 每天执行指令

#### 重启电脑
> 30 5 * * * root /sbin/reboot

#### 清理缓存
> php /www/wwwroot/pay/public/index.php cli/cache/clean

#### 统计
> php /www/wwwroot/pay/public/index.php cli/state/profit

#### 充币
> php /www/wwwroot/pay/public/index.php cli/blockchain/autoEthTrader

#### ERC充值
> php /www/wwwroot/pay/public/index.php cli/blockchain/autoErc

#### 广告 -更新卖价
> php /www/wwwroot/pay/public/index.php cli/ad/updateAdSellPrice

#### 广告 -更新买价
> php /www/wwwroot/pay/public/index.php cli/ad/updateAdBuyPrice

#### 订单 - 卖单倒计时
> php /www/wwwroot/pay/public/index.php cli/order/sellCountDown

#### 订单 - 买单倒计时
> php /www/wwwroot/pay/public/index.php cli/order/buyCountDown

