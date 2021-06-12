<?php
declare (strict_types=1);

namespace app\validate;

use app\controller\Error;
use think\response\Json;
use think\Validate;

class Publish extends Validate
{

  /**
   * 定义验证规则
   * 格式：'字段名' =>  ['规则1','规则2'...]
   *
   * @var array
   */
  protected $rule = [
    'page' => 'integer|>=:-1|different:0',
    'size' => 'integer|>=:0',
    'sortList' => 'array',
    'searchList' => 'array',
    'id' => 'require|integer|>:0',
    'ids' => 'checkIds:1',
    'title' => 'min:0',
    'content' => 'min:0',
    'cover' => 'min:0',
    'file' => 'file',
    'files' => 'array',
    'type' => 'require|integer',
    'remark' => 'min:0',
    'status' => 'integer',
  ];

  /**
   * 定义错误信息
   * 格式：'字段名.规则名' =>  '错误信息'
   *
   * @var array
   */
  protected $message = [
    'page.egt' => 'page需大于0或为-1显示全部',
    'page.different' => 'page需大于0或为-1显示全部',
    'ids.requireWithout' => "请传入id:number或者ids:number[]",
    'id.requireWithout' => "请传入id:number或者ids:number[]",
  ];


  protected $scene = [
    'list' => ['page', 'size', 'sortList', 'searchList'],
    'read' => ['id'],
    'add' => ['type', 'status'],
    'edit' => ['id', 'score', 'evaluate'],
  ];

  public function __construct()
  {
    $jsonMethod = ['POST','PUT'];    // 检查是否为json格式
    if (in_array(request()->method(),$jsonMethod)){
//      $this->checkContentType(['application/json']);
    }
  }

  public function sceneDel(): Publish
  {
    return $this->only(['id', 'ids'])
      ->remove('id', 'require')
      ->append('id', 'requireWithout:ids')
      ->append('ids', 'requireWithout:id');
  }

  // 自定义验证规则

  /**
   * @param $value
   * @param $rule
   * @param array $data
   * @return bool|string
   */
  protected function checkIds($value, $rule, $data = [])
  {
    function checkInt($v)
    {
      $int_options = ["options" => ["min_range" => 0]];
      if (!filter_var($v, FILTER_VALIDATE_INT, $int_options)) return 'id需为大于0的整数';
      else return true;
    }

    if ($rule == 1) {
      //场景1 优先接收id，再接收ids
      //优先接收id
      $id = isset($data['id']) ? $data['id'] : null;
      if (!empty($id) || $id == '0') {//非空类型检查
        $res = checkInt($data['id']);
        if ($res !== true) return $res;
      }
      //再接收ids
      if (empty($value)) return "请传入id:number或者ids:Array<number>";//空检查
      if (!is_array($value)) return "ids需为数组格式(ids:Array<number>)";//类型检查
      foreach ($value as $v) {
        $res = checkInt($v);
        if ($res !== true) return $res;//内部类型检查
      }
      return true;
    } elseif ($rule == 2) {
      //场景2 优先接收ids，再接收id
      //优先接收ids
      if (!empty($v) && is_array($v)) {//非空类型检查
        if (!is_array($value)) return "ids需为数组格式(ids:Array<number>)";//类型检查
        foreach ($value as $v) {
          $res = checkInt($v);
          if ($res !== true) return $res;//内部类型检查
        }
        return true;
      } else {
        //再接收id
        $id = isset($data['id']) ? $data['id'] : null;
        if (!empty($id) || $id == '0') {//非空类型检查
          $res = checkInt($data['id']);
          return $res;
        }
        return "请传入id:number或者ids:Array<number>";//空检查
      }
    } elseif ($rule == 2) {
      //场景3 只接收ids
      if (empty($value)) return "请传入ids:Array<number>";//空检查
      if (!is_array($value)) return "ids需为数组格式(ids:Array<number>)";//类型检查
      foreach ($value as $v) {
        $res = checkInt($v);
        if ($res !== true) return $res;//内部类型检查
      }
      return true;
    }
  }

  public function checkContentType($allowContentType='application/json'): bool
  {
    $dealStr = function($v){
      return str_replace(';','',strtolower(preg_replace('# #','',$v)));
    };
    if (is_string($allowContentType)){
      $allowContentType = [$allowContentType];
    }
    $allowContentType=array_map($dealStr,$allowContentType);
    $currentContentType =$dealStr(request()->contentType());
    if (!in_array($currentContentType,$allowContentType)){
      $error = new Error();
      $data = $error->errorContentType(implode("; 或 ",$allowContentType),'object');
      abort(intval($data['status']),$data['msg']);
    }else{
      return true;
    }
  }

}
