<?php
declare (strict_types=1);

namespace app\model;

use app\controller\UserExpire;
use think\Model;
use think\model\concern\SoftDelete;

/**
 * @mixin \think\Model
 */
class Menu extends Model
{
  // 数据转换为驼峰命名
  protected $convertNameToCamel = true;
  use SoftDelete;

  public function roles()
  {
    return $this->belongsToMany(Role::class);
  }

  public function createUser()
  {
    return $this->hasOne(User::class, 'id', 'create_id');
  }

  public function updateUser()
  {
    return $this->hasOne(User::class, 'id', 'update_id');
  }

  public static function onAfterInsert($menu)
  {
    $userExpire = new UserExpire();
    $userExpire->menuChange($menu);
  }

  public static function onAfterWrite($menu)
  {
    $userExpire = new UserExpire();
    $userExpire->menuChange($menu);
  }

  public static function onBeforeUpdate($menu)
  {
    $userExpire = new UserExpire();
    $userExpire->menuChange($menu);
  }

  public static function onBeforeDelete($menu)
  {
    $userExpire = new UserExpire();
    $userExpire->menuChange($menu);
  }


}
