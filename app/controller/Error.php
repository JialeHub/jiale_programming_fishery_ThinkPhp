<?php


namespace app\controller;

use OpenApi\Annotations as OA;
use think\response\Json;


class Error
{
  public function index()
  {
    $status = 501;
    $data['msg'] = '错误';
    $data['status'] = 501;
    return json($data, intval($status));
  }

  public function notFound()
  {
    $data['msg'] = '无法找到当前请求';
    $data['status'] = $status = 404;
    return json($data, intval($status));
  }

  public function forbid()
  {
    $data['msg'] = '权限不足，拒绝访问';
    $data['status'] = $status = 403;
    return json($data, intval($status));
  }

  public function expire()
  {
    $data['msg'] = '登陆状态已过期，请重新登录';
    $data['status'] = $status = 401;
    return json($data, intval($status));
  }

  public function methodError($allow = '')
  {
    $data['msg'] = '不支持该请求方法';
    $data['allow'] = $allow;
    $data['status'] = $status = 405;
    return json($data, intval($status), ['Allow' => $allow]);
  }

  public function errorContentType($allowContentType,$output='json')
  {
    $dealStr = function($v){
      return str_replace(';','',strtolower(preg_replace('# #','',$v)));
    };
    $currentContentType =$dealStr(request()->contentType());
    $data['msg'] = '请求内容格式错误，当前格式为"'.$currentContentType.'"，请以指定格式请求=>【"Content-Type": "'.$allowContentType.'"】';
    $data['status'] = $status = 422;
    if ($output=='json'){
      return json($data, intval($status));
    }else{
      return $data;
    }
  }

  /**
   * @param string $allowMethod
   * @return Json
   */
  public function checkJsonAjaxMethod($allowMethod = ''): Json
  {
    $allow = strtoupper(preg_replace('# #','',$allowMethod));
    $allowList = explode("|",$allow);
    $msg = [];
    $data = [];
    $info = [
      'isJson' => request()->isJson(),
      'accept' => 'application/json',
      'isAjax' => request()->isAjax(),
      'method' => strtoupper(preg_replace('# #','',request()->method())),
      'allow' => $allow,
      'argument' => [
        'request' => Request()->param(),
        '$_GET' => $_GET,
        '$_POST' => $_POST,
        '$_FILES' => $_FILES,
        '$_REQUEST' => $_REQUEST,
        '$_COOKIE' => $_COOKIE,
        'headers' => getallheaders(),
      ]
    ];
    if (!request()->isJson()){
      array_push($msg,"接收格式无效：请以JSON类型接收【'Accept':'application/json'】");
    }
    if (!request()->isAjax()){
      array_push($msg,"请求方式错误：请以Ajax方式请求【'X-Requested-With':'XMLHttpRequest'】");
    }
    if (!in_array($info['method'],$allowList)){
      array_push($msg,"不支持当前请求方法【Allow:".$allow."】");
    }
    $data['status'] = $status = 405;
    $data['msg'] = $msg;
//    $data['info'] = $info;
    return json($data, intval($status), ['Allow' => $allow]);
  }
}
