<?php
declare (strict_types=1);

namespace app\middleware;

use app\controller\Error;
use app\model\Role;
use app\model\User;
use app\model\UserToken;
use app\model\UserTokenAuth;

class Auth
{
  /**
   * 处理请求
   *
   * @param \think\Request $request
   * @param \Closure $next
   * @param bool $TokenFromHeader
   * @return mixed
   */
  public function handle($request, \Closure $next, $TokenFromHeader = true)
  {
    $t1 = microtime(true);
    $request->middleMsg = '欢迎';
    $error = new Error();

    if (cookie('session_id')) {
      $request->session_id = cookie('session_id');
    } else {
      $request->session_id = hash('sha3-256', $t1 . uniqid());
      cookie('session_id', $request->session_id, ['expire' => 60 * 60 * 24 * 14, 'httponly' => True]);
    }

    $token = $TokenFromHeader ? $request->header('Authorization') : cookie('token');
    if (is_string($token) && strlen($token) > 0) {
      if (substr($token, 0, 7) === 'Bearer ') $token = substr($token, 7);
      $request->token = $token;
    } else {
      $request->token = null;
    }
    $userToken = $request->token ? UserToken::where('token', $request->token)->find() : null;


    //鉴权
    $url = $request->baseUrl();
    $testStr = str_replace("\\", "/", preg_match("/\/$/", $url) ? $url : $url . '/');
    $testStr = $testStr = ($testStr === '/') ? '//' : $testStr;;

    function authCompare($auths, $testStr): bool
    {
      $Flag = false;
      foreach ($auths as $menu) {
        $reg = $menu['auth'];//匹配判断是否为普通访客开放接口
        if (!(is_string($reg) && $reg !== '')) continue;
        $reg = ($reg === '/') ? '//' : $reg;
        $reg = preg_match("/\/$/", $reg) ? $reg : $reg . '/';
        $reg = preg_replace('/\/:.*?\//', "/.[0-9]?/", $reg);//允许匹配ID
        $reg = str_replace("/", "\/", $reg);
        $result = [];
        if (preg_match("/^" . $reg . "/", $testStr, $result)) {
          $Flag = $result[0] === $testStr;
          if ($Flag) {
            request()->authMenu = $menu;//记录符合的菜单权限记录
            break;
          };
        }
      }
      return $Flag;
    }


    //开放接口判断
    $publicAuth = Role::findOrEmpty(1)->menus->hidden(['pivot'])->visible(['id', 'auth'])->where('status', 1);
    $publicFlag = authCompare($publicAuth, $testStr);


    //权限接口判断
    if ($userToken && strtotime($userToken->create_time) + $userToken->expires <= $t1) {
      $userToken->delete();//过期token删掉
      return $error->expire();//过期
    }
    $privateFlag = false;
    if ($userToken) {
      //重新获取菜单和权限采用绿色通道获取最新信息
      if ($url === '/user/initMenu') {
        //用户->Roles=>menu
        $user = User::where('status', 1)->with(['roles' => function ($q) {
          $q->where('status', 1)->with(['menus' => function ($q) {
            $q->where('status', 1);
          }]);
        }])->findOrEmpty($userToken->user_id);
        $roles = $user->isEmpty() ? [] : $user->roles;
        foreach ($roles as $role) {
          $initMenuAuth = $role->menus->hidden(['pivot'])->visible(['id', 'auth']);
          $privateFlag = authCompare($initMenuAuth, $testStr);
          if ($privateFlag) break;
        }
      } else {
        //否则从缓存中获取
        $userAuths = UserTokenAuth::where('user_token_id', $userToken->id)->select();//用户的权限表
        $privateFlag = authCompare($userAuths, $testStr);
      }
    }

    //无权访问
    if (!$publicFlag && !$privateFlag) return $error->forbid();//拒绝访问403
    $request->userToken = $userToken;
    $request->userId = $userToken ? $userToken->user_id : null;


    $res = $next($request);  //分界

    //更新token最新请求信息
    $server = $request->server();
    $updateIPFlag = $request->userToken && $request->userToken->isExists() && (
        (isset($server['REMOTE_ADDR']) && $request->userToken->remote_address !== $server['REMOTE_ADDR']) ||
        (isset($server['HTTP_X_FORWARDED_FOR']) && $request->userToken->x_forwarded_for !== $server['HTTP_X_FORWARDED_FOR']) ||
        (isset($server['HTTP_USER_AGENT']) && $request->userToken->request_user_agent !== $server['HTTP_USER_AGENT'])
      );

    if ($updateIPFlag) {
      $newUserToken = $request->token ? UserToken::where('token', $request->token)->findOrEmpty() : null;
      if ($newUserToken && !$newUserToken->isEmpty()) {
        if (isset($server['REMOTE_ADDR'])) $newUserToken->remote_address = $server['REMOTE_ADDR'];
        if (isset($server['HTTP_X_FORWARDED_FOR'])) $newUserToken->x_forwarded_for = $server['HTTP_X_FORWARDED_FOR'];
        if (isset($server['HTTP_USER_AGENT'])) $newUserToken->request_user_agent = $server['HTTP_USER_AGENT'];
        $newUserToken->save();
      }
    }

    return $res;
  }
}
