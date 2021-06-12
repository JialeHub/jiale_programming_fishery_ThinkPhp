<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;
use think\model\Pivot;

/**
 * @mixin \think\Model
 */
class RoleDept extends Pivot
{
    protected $autoWriteTimestamp = true;
//    use SoftDelete;
}
