<?php

namespace app\admin\validate;

use think\Validate;

class BannerValidate extends Validate
{
    protected $rule = [
       'title|标题'  => 'require',
       'orderby|排序'  => 'require',

    ];

}