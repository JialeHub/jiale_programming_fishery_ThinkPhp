<?php
declare (strict_types=1);

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * @mixin \think\Model
 */
class Publish extends Model
{
  // 数据转换为驼峰命名
  protected $convertNameToCamel = true;
  use SoftDelete;

  protected $json = ['ip_info', 'location_res'];

  public function file()
  {
    return $this->belongsToMany(File::class);
  }

  public function createUser()
  {
    return $this->hasOne(User::class, 'id', 'create_id');
  }

  public function updateUser()
  {
    return $this->hasOne(User::class, 'id', 'update_id');
  }
}
