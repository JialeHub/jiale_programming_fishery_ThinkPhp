<?php
declare (strict_types=1);

namespace app\controller;

use fileDownload\index as fileDownload;
use OpenApi\Annotations as OA;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use think\Request;
use \app\model\Menu as MenuModel;
use think\Response;
use utils\index as utils;

class Menu
{
  public $allowKeys = ['pid', 'type', 'model', 'title', 'path', 'remark', 'auth', 'name', 'component', 'redirect', 'activeMenu', 'icon', 'addRoutes', 'affix', 'alwaysShow', 'cache', 'breadcrumb', 'hidden', 'sort', 'status',];

  /**
   * 显示资源列表
   *
   * @return \think\Response
   */
  public function index()
  {
    //
  }

  /**
   * 获取 | 菜单列表
   * @param Request $request
   * @return Response
   * @OA\Get(
   *   path="/menu/list",summary="获取 | 菜单列表（不分页）",tags={"Menu | 菜单"},
   *   security={{"Authorization":{}}},
   *   @OA\Parameter(name="X-Requested-With",in="header",@OA\Schema(type="string",default="XMLHttpRequest"),description="请以Ajax方式请求"),
   *   @OA\Parameter(name="tree",in="query",required=false,@OA\Schema(type="boolean",default="true"),
   *    description="树结构："),
   *   @OA\Parameter(name="sortList",in="query",required=false,@OA\Schema(type="array",@OA\Items(type="object"),default={{"sort":"asc"}}),
   *    description="排序列表：asc顺序 desc倒序"),
   *   @OA\Response(response=200, description="OK", @OA\JsonContent()),
   * )
   */
  public function list(Request $request)
  {
    //分页(非树形模式)
    $page = (int)$request->param('page', 1);//当前页
    $limit = (int)$request->param('limit', 20);//每页大小，-1不分页

    //排序方式
    $sortListOri = $request->param('sortList', [['sort' => 'asc']]);//排序方式
    $utils = new utils();
    $sortList = [];
    foreach ($sortListOri as $item) {
      $tmpArray = explode('.', array_keys($item)[0]);
      if (count($tmpArray) <= 1) {
        $key = 'menu.' . array_keys($item)[0];
        $key = $utils->uncamelize($key);
        $sortList[$key] = array_values($item)[0];
      } else {
        $sortList[array_keys($item)[0]] = array_values($item)[0];
      }
    }

    // 搜索列表
    $searchListOri = $request->param('searchList', []);//搜索列表
    $searchList = [];
    foreach ($searchListOri as $item) {
      $tmpArray = explode('.', $item[0]);
      if (count($tmpArray) <= 1) {
        $item[0] = 'menu.' . $item[0];
        $item[0] = $utils->uncamelize($item[0]);
        array_push($searchList, $item);
      } else {
        array_push($searchList, $item);
      }
    }

    $menuModel = new MenuModel;
    $userShowField = ['id', 'nickname', 'avatar', 'username', 'email'];
    /* 树状数据 */
    if ($request->has('tree')) {
      /* 输出数据<全部> */
      $resAll = $menuModel->withJOIN([
        'createUser' => $userShowField,
        'updateUser' => $userShowField
      ], 'left')->order($sortList)->select();
      // 输出筛选后ID
      if (empty($searchListOri)) {
        $res = $resAll;
      } else {
        $res2 = $menuModel->withJOIN([
          'createUser' => $userShowField,
          'updateUser' => $userShowField
        ], 'left')->where($searchList)->order($sortList)->select();
        $idArr = array_map(function ($item) {
          return $item['id'];
        }, $res2->toArray());
        $pidArr = array_map(function ($item) {
          return $item['pid'];
        }, $res2->toArray());
        function movePid(&$idArr, &$pidArr, &$resAll, $i = 0)
        {
          foreach ($pidArr as $k => $pid) {
            if (!in_array($pid, $idArr)) {
              $ppid = null;
              foreach ($resAll as $item) {
                if ($item['id'] === $pid) {
                  $ppid = $item['pid'];
                  break;
                }
              }
              if (!is_null($ppid)) array_push($pidArr, $ppid);/*把父节点的pid也放进来*/
              if (!is_null($pid)) array_push($idArr, $pid);
            }
            unset($pidArr[$k]);
            $pidArr = array_values($pidArr);
          }
          $i++;
          if (!empty($pidArr)) movePid($idArr, $pidArr, $resAll, $i);
        }

        movePid($idArr, $pidArr, $resAll);
        $res = array_filter($resAll->toArray(), function ($item) use ($idArr) {
          return in_array($item['id'], $idArr);
        });
      }
      /* 整理数据 */
      $utils = new utils();
      if (($request->param('tree') === '1' || strtolower($request->param('tree')) === 'true')) {
        $list = $utils->arr2tree($res, 0, 1, array_values($sortListOri[0])[0] === 'desc', array_keys($sortListOri[0])[0]);
      } else if (($request->param('tree') === '0' || strtolower($request->param('tree')) === 'false')) {
        $list = $utils->array2level($res, 0, 1, array_values($sortListOri[0])[0] === 'desc', array_keys($sortListOri[0])[0]);
      }
      $count = count($res);
    } else {
      if ($limit == -1) {
        $list = $menuModel->withJOIN([
          'createUser' => $userShowField,
          'updateUser' => $userShowField
        ], 'left')->where($searchList)->order($sortList)->select();
        $count = count($list);
      } else {
        $count = $menuModel->withJOIN([
          'createUser' => $userShowField,
          'updateUser' => $userShowField
        ], 'left')->where($searchList)->count();
        $list = $menuModel->withJOIN([
          'createUser' => $userShowField,
          'updateUser' => $userShowField
        ], 'left')->where($searchList)->page($page, $limit)->order($sortList)->select();
      }
    }

    $status = 200;
    $msg = '获取成功';
    $data = [
      'status' => $status,
      'msg' => $msg,
      'list' => $list,
      'count' => $count,
      'param' => [
        'page' => $page,
        'limit' => $limit,
        'sortList' => $sortListOri,
        'searchList' => $searchListOri,
      ],
    ];
    return json($data, intval($status));
  }

  /**
   * 新建菜单
   *
   * @param \think\Request $request
   * @return \think\Response
   */
  public function save(Request $request): Response
  {
    $item = [];
    $item['createId'] = $request->userId;
    $allowKeys = $this->allowKeys;
    foreach ($allowKeys as $key) {
      if ($request->has($key, 'post')) $item[$key] = $request->param($key);
    }
    $menuModel = new MenuModel;
    $menuModel->save($item);

    $status = 201;
    $data['status'] = $status;
    $data['msg'] = '添加成功';
    $data['result'] = $menuModel;
    return json($data, intval($status));
  }

  /**
   * 显示指定的资源
   *
   * @param int $id
   * @return \think\Response
   */
  public function read($id)
  {
    //
  }

  /**
   * 导出菜单
   *
   * @param int $id
   * @return \think\Response
   */
  public function export(Request $request)
  {
    //分页(非树形模式)
    $page = (int)$request->param('page', 1);//当前页
    $limit = (int)$request->param('limit', -1);//每页大小，-1不分页

    //排序方式
    $sortListOri = $request->param('sortList', [['sort' => 'asc']]);//排序方式
    $utils = new utils();
    $sortList = [];
    foreach ($sortListOri as $item) {
      $tmpArray = explode('.', array_keys($item)[0]);
      if (count($tmpArray) <= 1) {
        $key = 'menu.' . array_keys($item)[0];
        $key = $utils->uncamelize($key);
        $sortList[$key] = array_values($item)[0];
      } else {
        $sortList[array_keys($item)[0]] = array_values($item)[0];
      }
    }

    // 搜索列表
    $searchListOri = $request->param('searchList', []);//搜索列表
    $searchList = [];
    foreach ($searchListOri as $item) {
      $tmpArray = explode('.', $item[0]);
      if (count($tmpArray) <= 1) {
        $item[0] = 'menu.' . $item[0];
        $item[0] = $utils->uncamelize($item[0]);
        array_push($searchList, $item);
      } else {
        array_push($searchList, $item);
      }
    }

    $menuModel = new MenuModel;
    $userShowField = ['id', 'nickname', 'avatar', 'username', 'email'];
    /* 树状数据 */
    if ($request->has('tree')) {
      /* 输出数据<全部> */
      $resAll = $menuModel->withJOIN([
        'createUser' => $userShowField,
        'updateUser' => $userShowField
      ], 'left')->order($sortList)->select()->toArray();

      // 输出筛选后ID
      if (empty($searchListOri)) {
        $res = $resAll;
      } else {
        $res2 = $menuModel->withJOIN([
          'createUser' => $userShowField,
          'updateUser' => $userShowField
        ], 'left')->where($searchList)->order($sortList)->select()->toArray();
        $idArr = array_map(function ($item) {
          return $item['id'];
        }, $res2->toArray());
        $pidArr = array_map(function ($item) {
          return $item['pid'];
        }, $res2->toArray());
        function movePid(&$idArr, &$pidArr, &$resAll, $i = 0)
        {
          foreach ($pidArr as $k => $pid) {
            if (!in_array($pid, $idArr)) {
              $ppid = null;
              foreach ($resAll as $item) {
                if ($item['id'] === $pid) {
                  $ppid = $item['pid'];
                  break;
                }
              }
              if (!is_null($ppid)) array_push($pidArr, $ppid);/*把父节点的pid也放进来*/
              if (!is_null($pid)) array_push($idArr, $pid);
            }
            unset($pidArr[$k]);
            $pidArr = array_values($pidArr);
          }
          $i++;
          if (!empty($pidArr)) movePid($idArr, $pidArr, $resAll, $i);
        }

        movePid($idArr, $pidArr, $resAll);
        $res = array_filter($resAll->toArray(), function ($item) use ($idArr) {
          return in_array($item['id'], $idArr);
        });
      }
      /* 整理数据 */
      $utils = new utils();
      if (($request->param('tree') === '1' || strtolower($request->param('tree')) === 'true')) {
        $list = $utils->arr2tree($res, 0, 1, array_values($sortListOri[0])[0] === 'desc', array_keys($sortListOri[0])[0]);
      } else if (($request->param('tree') === '0' || strtolower($request->param('tree')) === 'false')) {
        $list = $utils->array2level($res, 0, 1, array_values($sortListOri[0])[0] === 'desc', array_keys($sortListOri[0])[0]);
      }
    } else {
      if ($limit == -1) {
        $list = $menuModel->withJOIN([
          'createUser' => $userShowField,
          'updateUser' => $userShowField
        ], 'left')->where($searchList)->order($sortList)->select()->toArray();
      } else {
        $list = $menuModel->withJOIN([
          'createUser' => $userShowField,
          'updateUser' => $userShowField
        ], 'left')->where($searchList)->page($page, $limit)->order($sortList)->select()->toArray();
      }
    }

    //表格
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    if (!empty($list) && !empty($list[0])) {


      $firstItem = $list[0];
      $col = 0;
      // 表头字段Key
      $debug = [];

      foreach ($firstItem as $k => $v) {
        if (is_array($v)) {
          foreach ($v as $k2 => $v2) {
            $sheet->getCellByColumnAndRow(++$col, 1)->setValue($k . '.' . $k2);
          }
        } else {
          $sheet->getCellByColumnAndRow(++$col, 1)->setValue($k);
        }
      }

      // 列表数据Value
      foreach ($list as $row => $item) {
        $col = 0;
        foreach ($item as $k => $v) {
          if (is_array($v)) {
            foreach ($v as $k2 => $v2) {
              $sheet->getCellByColumnAndRow(++$col, $row + 2)->setValue($v2);
            }
          } else {
            $sheet->getCellByColumnAndRow(++$col, $row + 2)->setValue($v);
          }
        }
      }
    }


    $excelFileName = '菜单数据导出_' . md5(json_encode($list, JSON_UNESCAPED_UNICODE)) . '.xlsx';
    $fileRoot = config('filesystem')['disks']['tempDownload']['root'] . '/';
    $baseUrl = config('filesystem')['disks']['tempDownload']['url'] . '/';
    $tempExcelSrc = $utils->dirPathFormat($fileRoot . $excelFileName);
    $tempExcelPath = $utils->dirPathFormat($baseUrl . $excelFileName);
    $writer = new Xlsx($spreadsheet);
    $writer->save($tempExcelSrc);

    $FileDownload = new FileDownload();
    $flag = $FileDownload->download($tempExcelSrc, $excelFileName, true);
    if (!$flag) {
      abort(404, '文件不存在或已被删除');
    }
    return download($tempExcelSrc, $excelFileName);
  }

  /**
   * 修改菜单
   *
   * @param \think\Request $request
   * @return \think\Response
   */
  public function update(Request $request): Response
  {
    $id = $request->param('id');
    $menuModel = new MenuModel;
    $menu = $menuModel->where('id', $id)->findOrEmpty();

    if ($menu->isEmpty()) {
      $status = 202;
      $msg = '找不到此记录';
    } else {
      $menu['updateId'] = $request->userId;
      $allowKeys = $this->allowKeys;
      $param = $request->param();
      foreach ($allowKeys as $key) {
//        if ($request->has($key, 'post',true)) $menu[$key] = $request->param($key); //检测不到null
        if (array_key_exists($key,$param)) $menu[$key] = $request->param($key);
      }

      $menu->save();
      $status = 200;
      $msg = '更新成功';
    }
    $data['status'] = $status;
    $data['result'] = $menu;
    $data['msg'] = $msg;
    return json($data, intval($status));
  }

  /**
   * 删除菜单
   *
   * @param Request $request
   * @return \think\Response
   */
  public function delete(Request $request): Response
  {
    //$id单个删除,ids批量删除
    $deleteId = $request->userId;
    $id = $request->param('id');
    if (!is_null($id)) {
      $MenuModel = new MenuModel;
      $item = $MenuModel->findOrEmpty($id);
      if ($item->isExists()) {
        // 手动软删除
        $item->save([
          'delete_id' => $deleteId,
          'delete_time' => date('Y-m-d H:i:s'),
        ]);
        $status = 200;
        $data['msg'] = '删除成功';
      } else {
        $status = 202;
        $data['msg'] = '找不到该条记录';
      }
    } elseif (request()->has('ids') && is_array(request()->param('ids'))) {
      $MenuModel = new MenuModel;
      $delList = $MenuModel->where('id', 'in', request()->param('ids'))->select();
      // 手动软删除
      $delList->update([
        'delete_id' => $deleteId,
        'delete_time' => date('Y-m-d H:i:s'),
      ]);
      $status = 200;
      $data['msg'] = '删除成功';
    } else {
      $status = 202;
      $data['msg'] = '删除失败';
    }
    $data['status'] = $status;
    return json($data, intval($status));
  }
}
