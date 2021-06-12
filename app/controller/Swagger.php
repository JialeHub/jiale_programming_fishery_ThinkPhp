<?php


namespace app\controller;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *   title="嘉乐的程序设计 | 渔业信息采集系统",
 *   description="
 *    获取列表list【Get】,获取某一资源read【Get】,添加add 【Post】,删除del【Delete】,修改edit【Put】；
 *    get | delete 方法采用序列化URL(Axios为:params)传参 Axios:【paramsSerializer:params=>qs.stringify(params, { strictNullHandling: true }),】，
 *    post | put 方法采用请求体(Axios为:data)传参，Axios一般采用默认的JSON即可【'Content-Type': 'application/json'】,
 *    注意：接口只允许AJAX请求,手动改为AJAX请求请在请求头加上【'X-Requested-With':'XMLHttpRequest'】
 *    注意：接口以JSON形式返回响应，请以JSON格式接收【'Accept: application/json'】
 *    提示：由于Swagger无法正常序列化多层嵌套数组/对象,因此该文档的get/delete方法的嵌套字段无法正常传递测试!",
 *   version="1.0"
 * )
 */
class Swagger
{

  /**
   *  更新Swagger文档
   * @OA\SecurityScheme(
   *   type="http",
   *   in="header",
   *   name="Authorization",
   *   securityScheme="Authorization",
   *   scheme="Bearer",
   * ),
   * @OA\Get(
   *   path="/swagger/explore",summary="更新Swagger文档",tags={"Swagger | API文档"},
   *   @OA\Response(response="200", description="OK")
   * )
   */
  public function explore(): \think\response\Json
  {
    $path = '../app/controller'; //你想要哪个文件夹下面的注释生成对应的API文档
    $swagger = \OpenApi\scan($path);
    header('Content-Type: application/json');
    // var_dump($swagger);
    $swagger_json_path = './swagger/json/swagger.json';

    $res = file_put_contents($swagger_json_path, $swagger->toJson());
    if ($res == true) {
      $status = 200;
      $msg = 'swagger.json刷新成功';
    } else {
      $status = 500;
      $msg = 'error';
    }
    $data['msg'] = $msg;
    $data['status'] = $status;
    return json($data, intval($status));
  }
}