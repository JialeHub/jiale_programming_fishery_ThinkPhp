<?php


namespace app\controller;

use OpenApi\Annotations as OA;

use app\model\Role as RoleModel;
use fileDownload\index as fileDownload;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use think\Request;
use think\Response;
use utils\index as utils;

class Role
{
  public $allowKeys = ['name', 'dataScopeModel', 'status', 'level', 'remark'];

  /**
   * 获取 | 角色列表
   * @param Request $request
   * @return Response
   * @OA\Get(
   *   path="/role/list",summary="获取 | 用户列表",tags={"Role | 角色"},
   *   security={{"Authorization":{}}},
   *   @OA\Parameter(name="X-Requested-With",in="header",@OA\Schema(type="string",default="XMLHttpRequest"),description="请以Ajax方式请求"),
   *   @OA\Parameter(name="tree",in="query",required=false,@OA\Schema(type="boolean",default="true"),
   *    description="树结构："),
   *   @OA\Parameter(name="sortList",in="query",required=false,@OA\Schema(type="array",@OA\Items(type="object"),default={{"id":"desc"}}),
   *    description="排序列表：asc顺序 desc倒序"),
   *   @OA\Response(response=200, description="OK", @OA\JsonContent()),
   * )
   */
  public function list(Request $request): Response
  {
    $page = (int)$request->param('page', 1);//当前页
    $limit = (int)$request->param('limit', 20);//每页大小，-1不分页

    //排序方式
    $sortListOri = $request->param('sortList', [['id' => 'desc']]);//排序方式
    $utils = new utils();
    $sortList = [];
    foreach ($sortListOri as $item) {
      $tmpArray = explode('.', array_keys($item)[0]);
      if (count($tmpArray) <= 1) {
        $key = 'role.' . array_keys($item)[0];
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
        $item[0] = 'role.' . $item[0];
        $item[0] = $utils->uncamelize($item[0]);
        array_push($searchList, $item);
      } else {
        array_push($searchList, $item);
      }
    }

    $roleModel = new RoleModel;
    $userShowField = ['id', 'nickname', 'avatar', 'username', 'email'];
    if ($limit == -1) {
      $list = $roleModel->withJOIN([
        'createUser' => $userShowField,
        'updateUser' => $userShowField
      ], 'left')->where($searchList)->order($sortList)->select();
      $count = count($list);
    } else {
      $count = $roleModel->withJOIN([
        'createUser' => $userShowField,
        'updateUser' => $userShowField
      ], 'left')->where($searchList)->count();
      $list = $roleModel->withJOIN([
        'createUser' => $userShowField,
        'updateUser' => $userShowField
      ], 'left')->where($searchList)->page($page, $limit)
        ->order($sortList)->hidden(['password'])->select();
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
   * 导出角色
   *
   * @param Request $request
   * @return \think\Response
   */
  public function export(Request $request): Response
  {
    $page = (int)$request->param('page', 1);//当前页
    $limit = (int)$request->param('limit', -1);//每页大小，-1不分页

    //排序方式
    $sortListOri = $request->param('sortList', [['id' => 'desc']]);//排序方式
    $utils = new utils();
    $sortList = [];
    foreach ($sortListOri as $item) {
      $tmpArray = explode('.', array_keys($item)[0]);
      if (count($tmpArray) <= 1) {
        $key = 'role.' . array_keys($item)[0];
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
        $item[0] = 'role.' . $item[0];
        $item[0] = $utils->uncamelize($item[0]);
        array_push($searchList, $item);
      } else {
        array_push($searchList, $item);
      }
    }

    $roleModel = new RoleModel;
    $userShowField = ['id', 'nickname', 'avatar', 'username', 'email'];

    if ($limit == -1) {
      $list = $roleModel->withJOIN([
        'createUser' => $userShowField,
        'updateUser' => $userShowField
      ], 'left')->where($searchList)->order($sortList)->hidden(['password'])->select()->toArray();
    } else {
      $list = $roleModel->withJOIN([
        'createUser' => $userShowField,
        'updateUser' => $userShowField
      ], 'left')->where($searchList)->page($page, $limit)->order($sortList)->hidden(['password'])->select()->toArray();
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

    $excelFileName = '用户数据导出_' . md5(json_encode($list, JSON_UNESCAPED_UNICODE)) . '.xlsx';
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
   * 显示指定的角色
   *
   * @param Request $request
   * @return \think\Response
   */
  public function read(Request $request): Response
  {
    $role = RoleModel::with(['depts','menus'])->findOrEmpty($request->param('id'));
    $role->visible(['menus' => ['id','name','title'],'depts' => ['id','name']]);
    if (!$role->isEmpty()) {
      $status = 200;
      $data['info'] = $role;
      $data['msg'] = '获取成功';
    } else {
      $status = 202;
      $data['msg'] = '未找到该角色信息';
    }
    $data['status'] = $status;
    return json($data, intval($status));
  }

  /**
   * 新建角色
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
    $roleModel = new RoleModel;
    $roleModel->save($item);
    $role = $roleModel;
    $id = $role->id;
    // 自定义部门权限关联
    if ($request->has('depts', 'post')) {
      $addDepts =$request->param('depts');
      if (!empty($addDepts)) {
        foreach ($addDepts as $depts){
          $role->depts()->attach($depts,[
            'create_id'=>$request->userId,
            'create_time'=>date('Y-m-d H:i:s')
          ]);
        }
      }
    }
    // 菜单关联
    if ($request->has('menus', 'post')) {
      $addMenus =$request->param('menus');
      if (!empty($addMenus)) {
        foreach ($addMenus as $menu){
          $role->menus()->attach($menu,[
            'create_id'=>$request->userId,
            'create_time'=>date('Y-m-d H:i:s')
          ]);
        }
      }
    }
    $role = RoleModel::with(['depts','menus'])->findOrEmpty($id);
    $role->visible(['depts' => ['id','name'],'menus' => ['id','name','title']]);

    $status = 201;
    $data['status'] = $status;
    $data['msg'] = '添加成功';
    $data['result'] = $role;
    return json($data, intval($status));
  }

  /**
   * 修改角色
   *
   * @param \think\Request $request
   * @return \think\Response
   */
  public function update(Request $request): Response
  {
    $id = $request->param('id');
    $roleModel = new RoleModel;
    $role = $roleModel->where('id', $id)->findOrEmpty();

    if ($role->isEmpty()) {
      $status = 202;
      $msg = '找不到此记录';
    } else {
      $role['updateId'] = $request->userId;
      $allowKeys = $this->allowKeys;
      foreach ($allowKeys as $key) {
        if ($request->has($key, 'post')) $role[$key] = $request->param($key);
      }
      $role->save();

      // 自定义部门权限关联
      if ($request->has('depts', 'post')) {
        // 旧dept
        $oldDepts = array_map(function($item){return $item['id'];},$role->depts->toArray());
        // 新dept
        $depts =$request->param('depts');

        $delDepts = array_values(array_diff($oldDepts,$depts));
        $addDepts = array_values(array_diff($depts,$oldDepts));
        $role->depts()->detach($delDepts);

        if (!empty($addDepts)) {
          foreach ($addDepts as $depts){
            $role->depts()->attach($depts,[
              'create_id'=>$role['updateId'],
              'create_time'=>date('Y-m-d H:i:s')
            ]);
          }
        }
      }
      // 菜单关联
      if ($request->has('menus', 'post')) {
        // 旧menus
        $oldMenus = array_map(function($item){return $item['id'];},$role->menus->toArray());
        // 新menus
        $menus =$request->param('menus');

        $delMenus = array_values(array_diff($oldMenus,$menus));
        $addMenus = array_values(array_diff($menus,$oldMenus));

        $role->menus()->detach($delMenus);

        if (!empty($addMenus)) {
          foreach ($addMenus as $menu){
            $role->menus()->attach($menu,[
              'create_id'=>$role['updateId'],
              'create_time'=>date('Y-m-d H:i:s')
            ]);
          }
        }
      }
      $role = RoleModel::with(['depts','menus'])->findOrEmpty($id);
      $role->visible(['depts' => ['id','name'],'menus' => ['id','name','title']]);
      $status = 200;
      $msg = '更新成功';
    }
    $data['status'] = $status;
    $data['result'] = $role;
    $data['msg'] = $msg;
    return json($data, intval($status));
  }

  /**
   * 删除角色
   *
   * @param Request $request
   * @return Response
   */
  public function delete(Request $request): Response
  {
    //$id单个删除,ids批量删除
    $deleteId = $request->userId;
    $id = $request->param('id');
    if($id == 1 || (is_array(request()->param('ids')) && in_array(1,request()->param('ids'))) ){
      $status = 202;
      $data['msg'] = '删除失败：禁止删除普通访客角色';
    }else if (!is_null($id)) {
      $RoleModel = new RoleModel;
      $item = $RoleModel->findOrEmpty($id);
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
    } elseif (request()->has('ids')) {
      $RoleModel = new RoleModel;
      $delList = $RoleModel->where('id', 'in', request()->param('ids'))->select();
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