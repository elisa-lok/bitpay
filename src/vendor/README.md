# Service的用法
Service 详细使用方法
1. 创建service ,如果需要使用到Phalcon内部的di还有方法, 需要继承BaseService, 否则可以直接忽略
2. 在src目录下的Services.php, use 你刚才写的class ,
```
 //Services.php头部
  use LC\Service\Activity;
  //
   public static function Activity()
        {
            return new ActivityService();
        }
   在控制器可以这样使用
   // 我明天会在ControllerBase把Services.php里面的抽象类 New一次
    在控制器就可以这样使用了
    \LC\Services::Activity()->XXX($url);
    如果你想设计链式返回也行.
    LC\Services::Activity()->AAA($url)->BBB($url)->CCC($url);
```