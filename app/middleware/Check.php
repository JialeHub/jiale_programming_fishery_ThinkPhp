<?php
declare (strict_types=1);

namespace app\middleware;

use app\controller\Error;
use Closure;
use think\Request;
use think\response\Json;

class Check
{
  /**
   * 处理请求
   *
   * @param Request $request
   * @param Closure $next
   * @param $emit
   * @return mixed|Json
   */
  public function handle(Request $request, Closure $next): Json
  {
    $res = $next($request);  //分界
    return $res;
  }
}
