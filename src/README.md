# __在所有doc文件夹下面都必须保留一份sql__

#分开三层

### 应用层

包括view和controller
使用json作为

###服务层
>对外提供服务

service 服务， 对外提供数据， 可直接进行数据库操作, 对于业务的逻辑处理。直接返回json格式的数据
lib， 库， 包括一些常用功能, 这里是一些自己写的常用库和常用方法, 供应service使用
data 数据层 , 所有查询数据行为
utils 网上下载下来的库
model层 数据表的映射， 反映出表的结构, 不作为查询数据所用

数据层




https://github.com/liexusong/atom UKEY唯一ID生成器