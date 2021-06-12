<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use app\middleware\Check;
use think\facade\Route;
use app\controller\Error;
use \app\middleware\Auth;
use \app\validate\User as UserValidate;
use \app\validate\Article as ArticleValidate;
use \app\validate\Publish as PublishValidate;


$tip405 = true;
Route::pattern([
//    'name' => '\w+', 暂不兼容Auth
  'id' => '\d+',
]);

//Route::any('/test', '/test')->append(['allowMethod' => 'POST'])->name('测试');
Route::any('/', '/')->name('欢迎');
//Apk
Route::get('/apk/download', '/apk/download')->name('下载安装包');
Route::get('/apk', '/apk/index')->name('检查更新');

Route::get('/user/export', 'user/export')->middleware(Auth::class,$TokenFromHeader=false)->name('导出用户信息');
Route::get('/dept/export', 'dept/export')->middleware(Auth::class,$TokenFromHeader=false)->name('导出部门信息');
Route::get('/menu/export', 'menu/export')->middleware(Auth::class,$TokenFromHeader=false)->name('导出菜单信息');
Route::get('/role/export', 'role/export')->middleware(Auth::class,$TokenFromHeader=false)->name('导出角色信息');

Route::get('/publish/export', 'publish/export')->middleware(Auth::class,$TokenFromHeader=false)->name('导出发布信息');
Route::get('/publish/collectExport', 'publish/collectExport')->middleware(Auth::class,$TokenFromHeader=false)->name('导出用户评分汇总');
Route::get('/publish/downloadFiles', 'publish/downloadFiles')->middleware(Auth::class,$TokenFromHeader=false)->name('导出发布信息');

Route::get('/article/export', 'article/export')->middleware(Auth::class,$TokenFromHeader=false)->name('导出文章');

Route::any('/test', '/test')->name('测试')->allowCrossDomain();

Route::post('/file/upload', 'file/save')->name('上传文件'); #add


// 鉴权接口(Ajax && JSON)
Route::group(function () {

// User | Login
  Route::group('user', function () {
    // 登录 注销
    Route::post('login', 'user/login')->validate(UserValidate::class, 'login')->name('登录');
    Route::get('getLoginCaptcha', 'user/getLoginCaptcha')->validate(UserValidate::class, 'getLoginCaptcha')->name('获取登录验证码');
    Route::get('initMenu', 'user/initMenu')->validate(UserValidate::class, 'initMenu')->name('获取菜单更新权限');
    Route::get('logout', 'user/logout')->validate(UserValidate::class, 'logoutGet')->name('注销登录');

    // 个人信息
    Route::get('info', 'user/info')->validate(UserValidate::class, 'info')->name('获取个人用户信息');
    Route::put('updateInfo', 'user/updateInfo')->validate(UserValidate::class, 'updateInfo')->name('修改个人信息');
    // 绑定邮箱

    // 注册 找回密码
    Route::post('register', 'user/register')->validate(UserValidate::class, 'register')->name('注册');
    Route::get('sendForgetEmail', 'user/sendForgetEmail')->validate(UserValidate::class, 'sendForgetEmail')->name('发送找回密码验证码邮件');
    Route::get('getForgetPassToken', 'user/getForgetPassToken')->validate(UserValidate::class, 'getForgetPassToken')->name('邮件验证并返回重置密码临时Token');
    Route::post('updatePassBySessionToken', 'user/updatePassBySessionToken')->validate(UserValidate::class, 'updatePassBySessionToken')->name('sessionToken验证并设置新密码');

    // 管理用户 关联：部门(数据域)-角色(权限)
    Route::get('list', 'user/list')->validate(UserValidate::class, 'list')->name('获取用户列表');
    Route::get('read', 'user/read')->validate(UserValidate::class, 'read')->name('获取指定用户');
    Route::post('add', 'user/save')->validate(UserValidate::class, 'add')->name('添加用户');
    Route::delete('del', 'user/delete')->validate(UserValidate::class, 'del')->name('删除用户');
    Route::put('edit', 'user/update')->validate(UserValidate::class, 'edit')->name('修改用户信息');

    // 状态
    Route::get('online/list', 'user/onlineList')->validate(UserValidate::class, 'onlineList')->name('在线列表');
    Route::get('online/export', 'user/onlineExport')->validate(UserValidate::class, 'onlineExport')->name('导出在线信息');
    Route::get('online/read', 'user/onlineRead')->validate(UserValidate::class, 'onlineRead')->name('指定用户在线状态');
    Route::delete('logout', 'user/logout')->validate(UserValidate::class, 'logoutDel')->name('强制下线');

  })->name('用户');

// Dept
  Route::group('dept', function () {
    // 管理部门
    Route::get('list', 'dept/list')->name('获取部门列表');
    Route::get('read', 'dept/read')->name('获取指定部门');
    Route::post('add', 'dept/save')->name('添加部门');
    Route::delete('del', 'dept/delete')->name('删除部门');
    Route::put('edit', 'dept/update')->name('修改部门信息');
  })->name('部门');

// Menu | Auth
  Route::group('menu', function () {
    // 管理菜单/权限
    Route::get('list', 'menu/list')->name('获取菜单列表');
    Route::get('read', 'menu/read')->name('获取指定菜单');
    Route::post('add', 'menu/save')->name('添加菜单');
    Route::delete('del', 'menu/delete')->name('删除菜单');
    Route::put('edit', 'menu/update')->name('修改菜单信息');
  })->name('菜单权限');

// Role
  Route::group('role', function () {
    // 管理角色 关联：菜单
    Route::get('list', 'role/list')->name('获取角色列表');
    Route::get('read', 'role/read')->name('获取指定角色');
    Route::post('add', 'role/save')->name('添加角色');
    Route::delete('del', 'role/delete')->name('删除角色');
    Route::put('edit', 'role/update')->name('修改角色信息');
  })->name('角色');

// File
  Route::group('file', function () {
    // 文件储存系统
    Route::get('list', 'file/index')->name('获取文件列表');
    Route::get('export', 'file/export')->name('导出文件信息');
    Route::get('read', 'file/read')->name('获取指定文件');
    Route::get('download', 'file/download')->name('通用下载通道');
    Route::post('upload', 'file/save')->name('上传文件'); #add
    Route::delete('del', 'file/del')->name('删除文件');
    Route::put('edit', 'file/update')->name('修改文件信息');
  })->name('文件');

// Log
  Route::group('log', function () {
    Route::get('list', 'log/index')->name('获取日志列表');
    Route::get('export', 'log/export')->name('导出日志');
    Route::get('read', 'log/read')->name('获取指定日志');
    Route::post('del', 'log/del')->name('删除日志');
  })->name('日志');

// System
  Route::group('system', function () {
    Route::get('error/list', 'system/errorList')->name('查看异常信息');
    Route::get('error/read', 'system/errorRead')->name('获取指定日志');
    Route::get('error/export', 'system/errorExport')->name('导出异常信息');
    Route::get('server', 'system/server')->name('获取服务器运行状态');
    //...其他工具
  })->name('系统');

// Article
  Route::group('article', function () {
    Route::get('list', 'article/list')->validate(ArticleValidate::class, 'list')->name('获取 | 文章列表');
    Route::get('read', 'article/read')->validate(ArticleValidate::class, 'read')->name('获取 | 指定文章');
    Route::post('add', 'article/save')->validate(ArticleValidate::class, 'add')->name('添加 | 文章');
    Route::delete('del', 'article/delete')->validate(ArticleValidate::class, 'del')->name('删除 | 文章');
    Route::put('edit', 'article/update')->validate(ArticleValidate::class, 'edit')->name('修改 | 文章');
  })->name('文章');

// Publish
  Route::group('publish', function () {
    Route::get('list', 'publish/list')->validate(PublishValidate::class, 'list')->name('获取 | 发表列表');
    Route::get('read', 'publish/read')->validate(PublishValidate::class, 'read')->name('获取 | 指定发表');
    Route::post('add', 'publish/save')->validate(PublishValidate::class, 'add')->name('添加 | 发表');
    Route::delete('del', 'publish/delete')->validate(PublishValidate::class, 'del')->name('删除 | 发表');
    Route::put('edit', 'publish/update')->validate(PublishValidate::class, 'edit')->name('修改 | 发表');

    Route::get('collectMe', 'publish/collectMe')->name('获取自己的分数 | 发表');
    Route::get('collectList', 'publish/collectList')->name('获取用户评分汇总 | 发表');
  })->name('发表');
})->ajax()->json()->middleware(Auth::class);


//请求方法错误提示
$tip405 ? Route::group(function () {
//  Route::any('/test', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'get|post|delete|put|patch|head|options'])->name('405错误');
  Route::group('user', function () {
    Route::any('getLoginCaptcha', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'post'])->name('405错误');
    Route::any('initMenu', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'get'])->name('405错误');
    Route::any('logout', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'get|delete'])->name('405错误');
    Route::any('info', 'error/initMenu')->append(['allowMethod' => 'get'])->name('405错误');
    Route::any('updateInfo', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'put'])->name('405错误');
    Route::any('register', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'post'])->name('405错误');
    Route::any('sendForgetEmail', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'get'])->name('405错误');
    Route::any('getForgetPassToken', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'get'])->name('405错误');
    Route::any('updatePassBySessionToken', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'post'])->name('405错误');
    Route::any('list', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'get'])->name('405错误');
    Route::any('export', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'get'])->name('405错误');
    Route::any('read', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'get'])->name('405错误');
    Route::any('add', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'post'])->name('405错误');
    Route::any('del', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'post'])->name('405错误');
    Route::any('edit', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'put'])->name('405错误');
    Route::any('online/list', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'get'])->name('405错误');
    Route::any('online/export', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'get'])->name('405错误');
    Route::any('online/read', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'get'])->name('405错误');
  });

  Route::group('dept', function () {
    Route::any('list', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'get'])->name('405错误');
    Route::any('export', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'get'])->name('405错误');
    Route::any('read', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'get'])->name('405错误');
    Route::any('add', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'post'])->name('405错误');
    Route::any('del', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'delete'])->name('405错误');
    Route::any('edit', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'put'])->name('405错误');
  });

  Route::group('role', function () {
    Route::any('list', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'get'])->name('405错误');
    Route::any('export', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'get'])->name('405错误');
    Route::any('read', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'get'])->name('405错误');
    Route::any('add', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'post'])->name('405错误');
    Route::any('del', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'delete'])->name('405错误');
    Route::any('edit', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'put'])->name('405错误');
  });

  Route::group('menu', function () {
    Route::any('list', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'get'])->name('405错误');
    Route::any('export', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'get'])->name('405错误');
    Route::any('read', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'get'])->name('405错误');
    Route::any('add', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'post'])->name('405错误');
    Route::any('del', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'delete'])->name('405错误');
    Route::any('edit', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'put'])->name('405错误');
  });

  Route::group('file', function () {
    Route::any('list', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'get'])->name('405错误');
    Route::any('export', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'get'])->name('405错误');
    Route::any('read', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'get'])->name('405错误');
    Route::any('download', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'get'])->name('405错误');
    Route::any('upload', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'post'])->name('405错误');
    Route::any('del', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'delete'])->name('405错误');
    Route::any('edit', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'put'])->name('405错误');
  });

  Route::group('log', function () {
    Route::any('list', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'get'])->name('405错误');
    Route::any('export', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'get'])->name('405错误');
    Route::any('read', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'get'])->name('405错误');
    Route::any('del', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'post'])->name('405错误');
  });

  Route::group('system', function () {
    Route::any('error/list', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'get'])->name('405错误');
    Route::any('error/read', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'get'])->name('405错误');
    Route::any('error/export', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'get'])->name('405错误');
    Route::any('server', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'get'])->name('405错误');
  });

  Route::group('article', function () {
    Route::any('add', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'POST'])->name('405错误');
    Route::any('del', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'DELETE'])->name('405错误');
    Route::any('edit', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'PUT'])->name('405错误');
    Route::any('list', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'GET'])->name('405错误');
    Route::any('read', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'GET'])->name('405错误');
  });

  Route::group('publish', function () {
    Route::any('add', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'POST'])->name('405错误');
    Route::any('del', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'DELETE'])->name('405错误');
    Route::any('edit', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'PUT'])->name('405错误');
    Route::any('list', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'GET'])->name('405错误');
    Route::any('read', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'GET'])->name('405错误');

    Route::any('collectMe', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'GET'])->name('405错误');
    Route::any('collectList', 'error/checkJsonAjaxMethod')->append(['allowMethod' => 'GET'])->name('405错误');
  });

})->allowCrossDomain() : null;

//Route::any('/', '/')->name('访问后台');
Route::any('/swagger/explore', '/swagger/explore')->name('更新Swagger文档');


//404
Route::miss(function () {
  $error = new Error();
  return $error->notFound();
});
