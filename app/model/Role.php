<?php
declare (strict_types=1);

namespace app\model;

use app\controller\UserExpire;
use think\Model;
use think\model\concern\SoftDelete;

/**
 * @mixin \think\Model
 */
class Role extends Model
{
  // 数据转换为驼峰命名
  protected $convertNameToCamel = true;
  protected $hidden = ['pivot'];
  use SoftDelete;

  public function createUser()
  {
    return $this->hasOne(User::class, 'id', 'create_id');
  }

  public function updateUser()
  {
    return $this->hasOne(User::class, 'id', 'update_id');
  }

  public function depts()
  {
    return $this->belongsToMany(Dept::class);
  }

  public function menus()
  {
    return $this->belongsToMany(Menu::class,RoleMenu::class);
  }

  public function users()
  {
    return $this->belongsToMany(User::class);
  }

  public static function onAfterWrite($role)
  {
    $userExpire = new UserExpire();
    $userExpire->roleChange($role);
  }

  public static function onAfterInsert($role)
  {
    $userExpire = new UserExpire();
    $userExpire->roleChange($role);
  }

  public static function onBeforeUpdate($role)
  {
    $userExpire = new UserExpire();
    $userExpire->roleChange($role);
  }

  public static function onBeforeDelete($role)
  {
    $userExpire = new UserExpire();
    $userExpire->roleChange($role);
  }
}
