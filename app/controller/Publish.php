<?php
declare (strict_types=1);

namespace app\controller;
//require 'vendor/autoload.php';

use OpenApi\Annotations as OA;

use api\index as Api;
use app\model\PublishFile as PublishFileModel;
use app\model\UserDept;
use \app\model\User as UserModel;
use app\model\UserTokenAuth;
use think\Request;
use \app\model\Publish as PublishModel;
use think\Response;
use utils\index as utils;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use fileDownload\index as fileDownload;

class Publish
{
  public $allowKeys = ['score', 'evaluate', 'status', 'title', 'content', 'ip', 'ipInfo', 'ipAddr', 'location', 'locationRes', 'locationResAddr', 'workDays', 'operatingType', 'fishingBoats', 'machinePower'];

  /**
   * 获取 | 发布列表
   * @param Request $request
   * @return Response
   * @throws exception\DataNotFoundException
   * @throws exception\DbException
   * @throws exception\ModelNotFoundException
   * @OA\Get(
   *   path="/publish/list",summary="获取 | 发布列表",tags={"Publish | 发布"},
   *   security={{"Authorization":{}}},
   *   @OA\Parameter(name="X-Requested-With",in="header",@OA\Schema(type="string",default="XMLHttpRequest"),description="请以Ajax方式请求"),
   *   @OA\Parameter(name="page",in="query",required=false,@OA\Schema(type="integer",default="1"),
   *    description="页数：显示全部为-1"),
   *   @OA\Parameter(name="size",in="query",required=false,@OA\Schema(type="integer",default="10"),
   *    description="每页大小："),
   *   @OA\Parameter(name="sortList",in="query",required=false,@OA\Schema(type="array",@OA\Items(type="object"),default={{"id":"desc"}}),
   *    description="排序列表：asc顺序 desc倒序 按数组顺序依次排序</br>【Axios传参例子：{sortList:[{id: &quot;desc&quot;},{title: &quot;asc&quot;}]}  => ?sortList[0][id]=desc&sortList[1][title]=asc】"),
   *   @OA\Parameter(name="searchList",in="query",required=false,@OA\Schema(type="array",@OA\Items(type="array",@OA\Items(type="string"))),
   *    description="搜索列表：[ ['字段名','查询表达式','查询条件'] , ['id','>','0'] ] 有顺序,查询规则：
  <a target='_blank' href='https://www.kancloud.cn/manual/thinkphp6_0/1037537'>https://www.kancloud.cn/manual/thinkphp6_0/1037537</a>"),
   *   @OA\Response(response=200, description="OK", @OA\JsonContent()),
   * )
   */
  public function list(Request $request)
  {
    //页数
    $page = (int)$request->param('page', 1);//当前页
    $limit = (int)$request->param('limit', 10);//每页大小
    if ($limit === -1) $limit = 999999999;

    //排序方式
    $sortListOri = $request->param('sortList', [['id' => 'desc']]);//排序方式
    $utils = new utils();
    $sortList = [];
    foreach ($sortListOri as $item) {
      $tmpArray = explode('.', array_keys($item)[0]);
      if (count($tmpArray) <= 1) {
        $key = 'publish.' . array_keys($item)[0];
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
        $item[0] = 'publish.' . $item[0];
        $item[0] = $utils->uncamelize($item[0]);
        array_push($searchList, $item);
      } else {
        array_push($searchList, $item);
      }
    }

    // 数据库查询
    // 不登录(scene=0)：只显示公开的(status=1)
    // 普通登录(scene=1)：只显示公开的(status=1)和自己的(createId=userId)
    // 管理员登录(scene=2)：显示全部
    $scene = is_null($request->userId) ? 0 : 1;
    if(UserModel::find($request->userId)){
      $roles = UserModel::find($request->userId)->roles;
      foreach($roles as $role){
        if($role->name=='Root' || $role->name=='Admin'){
          $scene=2;
        }
      }
    }
    $map = [['publish.status', '=', 1]]; //默认不登录(scene=0)
    if ($scene==1){
      $map = [['publish.status', '=', 1],['publish.create_id', '=', $request->userId]];
    }else if($scene==2){
      $map = [['publish.id', '>', '0']];
    }
    $PublishModel = new PublishModel;
    $userShowField = ['id', 'nickname', 'avatar', 'username', 'email'];

    if ($limit == -1) {
      $list = $PublishModel->with(['file'])->withJOIN([
        'createUser' => $userShowField,
        'updateUser' => $userShowField
      ], 'left')->whereOr($map)->where($searchList)->order($sortList)->select();
      $count = count($list);
    } else {
      $count = $PublishModel->withJOIN([
        'createUser' => $userShowField,
        'updateUser' => $userShowField
      ], 'left')->whereOr($map)->where($searchList)->count();
      $list = $PublishModel->with(['file'])->withJOIN([
        'createUser' => $userShowField,
        'updateUser' => $userShowField
      ], 'left')->whereOr($map)->where($searchList)->page($page, $limit)
        ->order($sortList)->select();
    }

    // 输出
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
   * 获取 | 指定发布
   * @param Request $request
   * @return Json
   * @OA\Get(
   *   path="/publish/read",summary="获取 | 指定发布",tags={"Publish | 发布"},
   *   security={{"Authorization":{}}},
   *   @OA\Parameter(name="X-Requested-With",in="header",@OA\Schema(type="string",default="XMLHttpRequest"),description="请以Ajax方式请求"),
   *   @OA\Parameter(name="Authorization",in="header",required=false,description="Token：登录显示任意数据 | 不登陆只显示 status==1",@OA\Schema(type="bearerAuth")),
   *   @OA\Parameter(name="id",in="query",required=true,@OA\Schema(type="integer"),example=1,description="页数：显示全部为-1"),
   *   @OA\Response(response=200, description="OK", @OA\JsonContent()),
   * )
   */
  public function read(Request $request)
  {
    // 数据库查询
    $id = $request->param('id');
    $map1 = is_null($request->userId) ? ['status', '=', 1] : ['id', '>', '0'];//登录显示全部，否则只显示启用
    $map = empty($map1) ? [] : [$map1];

    $result = PublishModel::whereOr($map)->where('id', '=', $id)->with(['file'])->findOrEmpty();

    if (!$result->isEmpty()) {
      $status = 200;
      $data['info'] = $result;
      $data['msg'] = '获取成功';
    } else {
      $status = 202;
      $data['msg'] = '没有数据';
    }
    $data['status'] = $status;
    return json($data, intval($status));
  }

  /**
   * 保存新建的资源
   *
   * @param \think\Request $request
   * @return \think\Response
   */
  public function save(Request $request)
  {
    $item = [];
    $item['createId'] = $request->userId;
    $allowKeys = $this->allowKeys;
    foreach ($allowKeys as $key) {
      if ($request->has($key, 'post')) $item[$key] = $request->param($key);
    }

    //获取IP信息
    $api = new Api();
    $server = $request->server();
    $ip = isset($server['HTTP_X_FORWARDED_FOR']) ? $server['HTTP_X_FORWARDED_FOR'] :
      (isset($server['REMOTE_ADDR']) ? $server['REMOTE_ADDR'] : '');
    $ipInfo = $api->getIPInfo($ip);
    $item['ip'] = $ip;
    if (!is_null($ipInfo)) $item['ip_info'] = $ipInfo;
    if (!is_null($ipInfo) && is_array($ipInfo) && array_key_exists('addr', $ipInfo)) $item['ip_addr'] = $ipInfo['addr'];
    if (!is_null($ipInfo) && isset($ipInfo->addr)) $item['ip_addr'] = $ipInfo->addr;

    $publishModel = new PublishModel;
    $publishModel->save($item);

    //记录上传文件
    if ($request->has('fileIds', 'post')) {
      //优先级：fileIds(逗号隔开,string) > fileIds[](array)
      if (is_string($request->param('fileIds'))) {
        $fileIdsArr = explode(",", $request->param('fileIds'));
      } elseif (is_array($request->param('fileIds'))) {
        $fileIdsArr = $request->param('fileIds');
      } else {
        $fileIdsArr = [];
      }
      $publishFiles = [];
      foreach ($fileIdsArr as $fileId) {
        if (preg_match("/^[1-9][0-9]*$/", $fileId)) {
          array_push($publishFiles, [
            'publish_id' => $publishModel->id,
            'file_id' => $fileId
          ]);
        }
      }

      if (count($publishFiles) > 0) {
        $publishFilesModel = new PublishFileModel;
        $publishFilesModel->saveAll($publishFiles);
      }

    }

    $status = 201;
    $data['ip'] = $ipInfo;
    $data['status'] = $status;
    $data['msg'] = '添加成功';
    $data['result'] = $publishModel;
    return json($data, intval($status));
  }

  /**
   * 删除指定资源
   *
   * @param Request $request
   * @return Response
   */
  public function delete(Request $request)
  {
    //$id单个删除,ids批量删除
    $deleteId = $request->userId;
    $id = $request->param('id');
    if (!is_null($id)) {
      $item = PublishModel::findOrEmpty($id);
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
      $delList = PublishModel::where('id', 'in', request()->param('ids'))->select();
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

  /**
   * 保存更新的资源
   *
   * @param Request $request
   * @return Response
   */
  public function update(Request $request)
  {
    $publish['updateId'] = $request->userId;
    $id = $request->param('id');
    $publish = PublishModel::where('id', $id)->findOrEmpty();

    if ($publish->isEmpty()) {
      $status = 202;
      $msg = '找不到此记录';
    } else {
      $allowKeys = $this->allowKeys;
      foreach ($allowKeys as $key) {
        if ($request->has($key, 'post')) $publish[$key] = $request->param($key);
      }
      $publish->save();
//      $this->downloadFiles($id, 'C:\localuser\gdoupg\gdoupg_img\\');
    }

    $status = 200;
    $msg = '更新成功';
    $data['status'] = $status;
    $data['msg'] = $msg;
    return json($data, intval($status));

  }

  /**
   * 导出汇总用户分数：用户->发表
   *
   * @param \think\Request $request
   * @return \think\Response
   */
  public function export(Request $request)
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
        $key = 'publish.' . array_keys($item)[0];
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
        $item[0] = 'publish.' . $item[0];
        $item[0] = $utils->uncamelize($item[0]);
        array_push($searchList, $item);
      } else {
        array_push($searchList, $item);
      }
    }

    $publishModel = new PublishModel;
    $userShowField = ['id', 'nickname', 'avatar', 'username', 'email'];

    if ($limit == -1) {
      $list = $publishModel->with(['file'])->withJOIN([
        'createUser' => $userShowField,
        'updateUser' => $userShowField
      ], 'left')->where($searchList)->order($sortList)->hidden(['password'])->select()->toArray();
    } else {
      $list = $publishModel->with(['file'])->withJOIN([
        'createUser' => $userShowField,
        'updateUser' => $userShowField
      ], 'left')->where($searchList)->page($page, $limit)->order($sortList)->hidden(['password'])->select()->toArray();
    }

    //表格
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->getCellByColumnAndRow(1, 1)->setValue('ID');
    $sheet->getCellByColumnAndRow(2, 1)->setValue('标题');
    $sheet->getCellByColumnAndRow(3, 1)->setValue('内容');
    $sheet->getCellByColumnAndRow(4, 1)->setValue('分数');
    $sheet->getCellByColumnAndRow(5, 1)->setValue('评价');
    $sheet->getCellByColumnAndRow(6, 1)->setValue('IP');
    $sheet->getCellByColumnAndRow(7, 1)->setValue('经纬度');
    $sheet->getCellByColumnAndRow(8, 1)->setValue('发表类型 0本船 1其他');
    $sheet->getCellByColumnAndRow(9, 1)->setValue('状态');
    $sheet->getCellByColumnAndRow(10, 1)->setValue('ip地址');
    $sheet->getCellByColumnAndRow(11, 1)->setValue('经纬度地址');
    $sheet->getCellByColumnAndRow(12, 1)->setValue('作业天数');
    $sheet->getCellByColumnAndRow(13, 1)->setValue('作业类型');
    $sheet->getCellByColumnAndRow(14, 1)->setValue('渔船号');
    $sheet->getCellByColumnAndRow(15, 1)->setValue('主机功率');
    $sheet->getCellByColumnAndRow(16, 1)->setValue('评价时间');
    $sheet->getCellByColumnAndRow(17, 1)->setValue('发表时间');

    $sheet->getCellByColumnAndRow(19, 1)->setValue('发表者ID');
    $sheet->getCellByColumnAndRow(20, 1)->setValue('发表者昵称');
    $sheet->getCellByColumnAndRow(21, 1)->setValue('发表者头像');
    $sheet->getCellByColumnAndRow(22, 1)->setValue('发表者用户名');
    $sheet->getCellByColumnAndRow(23, 1)->setValue('发表者邮箱');

    $sheet->getCellByColumnAndRow(25, 1)->setValue('图片文件信息');

    foreach ($list as $k => $v) {
      $sheet->getCellByColumnAndRow(1, $k + 2)->setValue($v['id']);
      $sheet->getCellByColumnAndRow(2, $k + 2)->setValue($v['title']);
      $sheet->getCellByColumnAndRow(3, $k + 2)->setValue($v['content']);
      $sheet->getCellByColumnAndRow(4, $k + 2)->setValue($v['score']);
      $sheet->getCellByColumnAndRow(5, $k + 2)->setValue($v['evaluate']);
      $sheet->getCellByColumnAndRow(6, $k + 2)->setValue($v['ip']);
      $sheet->getCellByColumnAndRow(7, $k + 2)->setValue($v['location']);
      $sheet->getCellByColumnAndRow(8, $k + 2)->setValue($v['type']);
      $sheet->getCellByColumnAndRow(9, $k + 2)->setValue($v['status']);
      $sheet->getCellByColumnAndRow(10, $k + 2)->setValue($v['ipAddr']);
      $sheet->getCellByColumnAndRow(11, $k + 2)->setValue($v['locationResAddr']);
      $sheet->getCellByColumnAndRow(12, $k + 2)->setValue($v['workDays']);
      $sheet->getCellByColumnAndRow(13, $k + 2)->setValue($v['operatingType']);
      $sheet->getCellByColumnAndRow(14, $k + 2)->setValue($v['fishingBoats']);
      $sheet->getCellByColumnAndRow(15, $k + 2)->setValue($v['machinePower']);
      $sheet->getCellByColumnAndRow(16, $k + 2)->setValue($v['updateTime']);
      $sheet->getCellByColumnAndRow(17, $k + 2)->setValue($v['createTime']);

      $sheet->getCellByColumnAndRow(19, $k + 2)->setValue($v['createUser']['id']);
      $sheet->getCellByColumnAndRow(20, $k + 2)->setValue($v['createUser']['nickname']);
      $sheet->getCellByColumnAndRow(21, $k + 2)->setValue($v['createUser']['avatar']);
      $sheet->getCellByColumnAndRow(22, $k + 2)->setValue($v['createUser']['username']);
      $sheet->getCellByColumnAndRow(23, $k + 2)->setValue($v['createUser']['email']);

      $sheet->getCellByColumnAndRow(25, $k + 2)->setValue(json_encode($v['file']));
    }

    $excelFileName = '照片记录_' . md5(json_encode($list, JSON_UNESCAPED_UNICODE)) . '.xlsx';
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
   * 批量下载图片生成压缩包
   * @param null $id
   * @param null $downloadPath
   * @return string
   * @throws \think\db\exception\DataNotFoundException
   * @throws \think\db\exception\DbException
   * @throws \think\db\exception\ModelNotFoundException
   */
  public function downloadFiles($id = null, $downloadPath = null)
  {
    //$id单个记录下载,ids批量下载
    $publishFile = null;
    $utils = new utils();
    $files = [];
    $zipFileName = '';
    $selectorType = ['单拖', '双拖', '拖虾', '围网', '罩网', '刺网', '钓具', '笼壶', '张网', '杂渔具', '潜捕', '拖贝'];
    // 服务器绝对路径
    $publicRoot = config('filesystem')['disks']['public']['root'];
    $publicURL = config('filesystem')['disks']['public']['url'];
    $fileRoot = $publicRoot;
    if (substr($publicRoot, -strlen($publicURL)) === $publicURL) {
      $fileRoot = substr($publicRoot, 0, strlen($publicRoot) - strlen($publicURL));
    }
    if (!is_null($id)) {
      $publish = PublishModel::find($id);
      $publishFile = $publish->file;
      $username = $publish->createUser->username;
      $realname = $publish->createUser->realname;
      $createTime = str_replace(":", "-", $publish->createTime);
      $createTime = str_replace(" ", "_", $createTime);
      $location = str_replace(".", "D", $publish->location);
      $location = str_replace(",", "_", $location);
      $type = ($publish->type === 1 ? '_QT' : '');
      $fishingBoats = $publish->fishingBoats;//船号
      $operatingType = $publish->operatingType;//作业类型
      $operatingType = $selectorType[$operatingType];
      $machinePower = $publish->machinePower;//主机功率(千瓦)
      $id = $publish->id;
      foreach ($publishFile as $item) {
        $src = $utils->dirPathFormat($fileRoot . $item['path']);
        $fileName = $realname . $type . '_' . $fishingBoats . '_' . $operatingType . '_' . $machinePower . '_' . $createTime . '_' . $location . '_' . $id . '_' . $item['id'] . '.' . $item['suffix'];
//        $fileName = $realname . $type . '_' . $fishingBoats . '_' . $operatingType . '_' . $machinePower . '_' . $createTime . '_' . $location . '.' . $item['suffix'];
        array_push($files, ['src' => $src, 'name' => $fileName]);
      }
      $zipFileName = $realname . $type . '_' . $fishingBoats . '_' . $operatingType . '_' . $machinePower . '_' . $createTime . '_' . $location . '_' . count($publishFile) . '.zip';
    } elseif (request()->has('ids')) {
      if (is_array(request()->param('ids'))){
        $ids = request()->param('ids');
      }else if(is_string(request()->param('ids'))){
        $ids = explode(',', request()->param('ids'));
      }
      $publishs = PublishModel::where('id', 'in', $ids)->with('file')->select();
      foreach ($publishs as $publish) {
        $publishFile = $publish->file;
        $username = $publish->createUser->username;
        $realname = $publish->createUser->realname;
        $createTime = str_replace(":", "-", $publish->createTime);
        $createTime = str_replace(" ", "_", $createTime);
        $location = str_replace(".", "D", $publish->location);
        $location = str_replace(",", "_", $location);
        $type = ($publish->type === 1 ? '_QT' : '');
        $fishingBoats = $publish->fishingBoats;//船号
        $operatingType = $publish->operatingType;//作业类型
        $operatingType = $selectorType[$operatingType];
        $machinePower = $publish->machinePower;//主机功率(千瓦)
        $id = $publish->id;
        foreach ($publishFile as $item) {
          $src = $utils->dirPathFormat($fileRoot . $item['path']);
          $fileName = $realname . $type . '_' . $fishingBoats . '_' . $operatingType . '_' . $machinePower . '_' . $createTime . '_' . $location . '_' . $id . '_' . $item['id'] . '.' . $item['suffix'];
          array_push($files, ['src' => $src, 'name' => $fileName]);
        }
      }
      $zipFileName = 'pictureFiles_' . md5(json_encode(request()->param('ids'), JSON_UNESCAPED_UNICODE)) . '.zip';
    }

    $zip = new \ZipArchive;
    if (is_null($downloadPath)) {
      $baseRoot = config('filesystem')['disks']['tempDownload']['root'] . '/';
      $baseUrl = config('filesystem')['disks']['tempDownload']['url'] . '/';
      $tempZipSrc = $utils->dirPathFormat($baseRoot . $zipFileName);
      $tempZipPath = $utils->urlPathFormat($baseUrl . $zipFileName);
    } else {
      $tempZipSrc = $utils->dirPathFormat($downloadPath . $zipFileName);
      $tempZipPath = $tempZipSrc;
    }
    if ($zip->open($tempZipSrc, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
      foreach ($files as $file) {
        if (!file_exists($file['src'])) {
          continue;
        }
        $zip->addFile($file['src'], $file['name']);
      }
      $zip->close();

      $FileDownload = new FileDownload();
      $flag = $FileDownload->download($tempZipSrc, $zipFileName, true);
      if (!$flag) {
        abort(404, '文件不存在或已被删除');
      }
      return $FileDownload->download($tempZipSrc, $zipFileName, true);
    } else {
      abort(404, '文件不存在或已被删除');
    }
  }


  /**
   * 获取用户本人分数(日、周、月、年、总)
   *
   * @param \think\Request $request
   * @return \think\Response
   */
  public function collectMe(Request $request)
  {
    //筛选
    $userId = $request->userId;
    $score = [];
    if (!$userId) {
      $status = 404.1;
      $data['msg'] = '未找到用户信息';
    } else {
      //总
      $count = PublishModel::where('create_id', '=', $userId)->count();//总发表量
      $scoreNull = PublishModel::where('create_id', '=', $userId)->where('score', '=', '-1')->count();//未评数
      $resultScore = PublishModel::where('create_id', '=', $userId)->where('score', '>=', 0);
      $score = [
        'all' => ['min' => $resultScore->min('score'), 'max' => $resultScore->max('score'), 'sum' => $resultScore->sum('score'), 'avg' => $resultScore->avg('score')],
        'year' => [
          'min' => $resultScore->whereYear('create_time')->min('score'),
          'max' => $resultScore->whereYear('create_time')->max('score'),
          'sum' => $resultScore->whereYear('create_time')->sum('score'),
          'avg' => $resultScore->whereYear('create_time')->avg('score')
        ],
        'month' => [
          'min' => $resultScore->whereMonth('create_time')->min('score'),
          'max' => $resultScore->whereMonth('create_time')->max('score'),
          'sum' => $resultScore->whereMonth('create_time')->sum('score'),
          'avg' => $resultScore->whereMonth('create_time')->avg('score')
        ],
        'week' => [
          'min' => $resultScore->whereWeek('create_time')->min('score'),
          'max' => $resultScore->whereWeek('create_time')->max('score'),
          'sum' => $resultScore->whereWeek('create_time')->sum('score'),
          'avg' => $resultScore->whereWeek('create_time')->avg('score')
        ],
        'day' => [
          'min' => $resultScore->whereDay('create_time')->min('score'),
          'max' => $resultScore->whereDay('create_time')->max('score'),
          'sum' => $resultScore->whereDay('create_time')->sum('score'),
          'avg' => $resultScore->whereDay('create_time')->avg('score')
        ],
      ];
      $data['score'] = $score;
      $data['count'] = $count;
      $data['scoreNull'] = $scoreNull;
      $status = 200;
      $data['msg'] = '获取成功';
    }
    $data['status'] = $status;
    return json($data, intval($status));
  }

  /**
   * 汇总用户分数：用户->发表
   *
   * @param \think\Request $request
   * @return \think\Response
   */
  public function collectList(Request $request)
  {
    //页数
    $page = (int)$request->param('page', 1);//当前页
    $limit = (int)$request->param('limit', 10);//每页大小
    if ($limit === -1) $limit = 999999999;

    //排序方式
    $sortListOri = $request->param('sortList', [['id' => 'desc']]);//排序方式
    $utils = new utils();
    $sortList = [];
    foreach ($sortListOri as $item) {
      $tmpArray = explode('.', array_keys($item)[0]);
      if (count($tmpArray) <= 1) {
        $key = 'user.' . array_keys($item)[0];
        $key = $utils->uncamelize($key);
        $sortList[$key] = array_values($item)[0];
      } else {
        $sortList[array_keys($item)[0]] = array_values($item)[0];
      }
    }

    // 搜索列表【特殊化处理】
    $searchListOri = $request->param('searchList', []);//搜索列表
    $searchList = [];
    $searchListPublish = [];
    foreach ($searchListOri as $item) {
      $tmpArray = explode('.', $item[0]);
      if (count($tmpArray) <= 1) {
        $item[0] = 'user.' . $item[0];
        $item[0] = $utils->uncamelize($item[0]);
        array_push($searchList, $item);
      } else if( $tmpArray[0]=='publish') {
        $item[0] = $utils->uncamelize($tmpArray[1]);
        array_push($searchListPublish, $item);
      } else {
        array_push($searchList, $item);
      }
    }

    $count = UserModel::where($searchList)->count();
    $users = UserModel::where($searchList)->hidden(['password', 'deleteTime', 'deleteId'])
      ->withSum(['publish' => function ($query, &$alias) use ($searchListPublish) {
        $alias = 'score_sum';
        $query->field('id,create_id,score,create_time,update_time')->where($searchListPublish)->where([['score', '>=', 0]])->select();
      }], 'score')->withMax(['publish' => function ($query, &$alias) use ($searchListPublish) {
        $alias = 'score_max';
        $query->field('id,create_id,score,create_time,update_time')->where($searchListPublish)->where([['score', '>=', 0]])->select();
      }], 'score')->withMin(['publish' => function ($query, &$alias) use ($searchListPublish) {
        $alias = 'score_min';
        $query->field('id,create_id,score,create_time,update_time')->where($searchListPublish)->where([['score', '>=', 0]])->select();
      }], 'score')->withAvg(['publish' => function ($query, &$alias) use ($searchListPublish) {
        $alias = 'score_avg';
        $query->field('id,create_id,score,create_time,update_time')->where($searchListPublish)->where([['score', '>=', 0]])->select();
      }], 'score')->withCount(['publish' => function ($query, &$alias) use ($searchListPublish) {
        $alias = 'score_count';
        $query->field('id,create_id,score,create_time,update_time')->where($searchListPublish)->where([['score', '>=', 0]])->select();
      }])->withCount(['publish' => function ($query, &$alias) use ($searchListPublish) {
        $alias = 'score_none';
        $query->field('id,create_id,score,create_time,update_time')->where($searchListPublish)->where([['score', '=', -1]])->select();
      }])->withCount(['publish' => function ($query, &$alias) use ($searchListPublish) {
        $alias = 'publish_count';
        $query->field('id,create_id,score,create_time,update_time')->where($searchListPublish)->select();
      }])->page($page, $limit)->order($sortList)->select();
    $list = $users;

    // 输出
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
   * 显示发布内容
   * 公开接口(无dataScope)：只显示公开(status===1;)及仅自己可见数据(create_id===userId;)
   * 权限接口(有dataScope)：显示全部数据(dataScope===0)，显示dataScope内
   * 公开接口及权限接口并联OR
   * 简化：若有权限
   *
   * @return \think\Response
   */
  public function collectExport(Request $request)
  {
    //页数
    $page = (int)$request->param('page', 1);//当前页
    $limit = (int)$request->param('limit', 10);//每页大小
    if ($limit === -1) $limit = 999999999;

    //排序方式
    $sortListOri = $request->param('sortList', [['id' => 'desc']]);//排序方式
    $utils = new utils();
    $sortList = [];
    foreach ($sortListOri as $item) {
      $tmpArray = explode('.', array_keys($item)[0]);
      if (count($tmpArray) <= 1) {
        $key = 'user.' . array_keys($item)[0];
        $key = $utils->uncamelize($key);
        $sortList[$key] = array_values($item)[0];
      } else {
        $sortList[array_keys($item)[0]] = array_values($item)[0];
      }
    }

    // 搜索列表【特殊化处理】
    $searchListOri = $request->param('searchList', []);//搜索列表
    $searchList = [];
    $searchListPublish = [];
    foreach ($searchListOri as $item) {
      $tmpArray = explode('.', $item[0]);
      if (count($tmpArray) <= 1) {
        $item[0] = 'user.' . $item[0];
        $item[0] = $utils->uncamelize($item[0]);
        array_push($searchList, $item);
      } else if( $tmpArray[0]=='publish') {
        $item[0] = $utils->uncamelize($tmpArray[1]);
        array_push($searchListPublish, $item);
      } else {
        array_push($searchList, $item);
      }
    }

    $count = UserModel::where($searchList)->count();
    $users = UserModel::where($searchList)->hidden(['password', 'deleteTime', 'deleteId'])
      ->withSum(['publish' => function ($query, &$alias) use ($searchListPublish) {
        $alias = 'score_sum';
        $query->field('id,create_id,score,create_time,update_time')->where($searchListPublish)->where([['score', '>=', 0]])->select();
      }], 'score')->withMax(['publish' => function ($query, &$alias) use ($searchListPublish) {
        $alias = 'score_max';
        $query->field('id,create_id,score,create_time,update_time')->where($searchListPublish)->where([['score', '>=', 0]])->select();
      }], 'score')->withMin(['publish' => function ($query, &$alias) use ($searchListPublish) {
        $alias = 'score_min';
        $query->field('id,create_id,score,create_time,update_time')->where($searchListPublish)->where([['score', '>=', 0]])->select();
      }], 'score')->withAvg(['publish' => function ($query, &$alias) use ($searchListPublish) {
        $alias = 'score_avg';
        $query->field('id,create_id,score,create_time,update_time')->where($searchListPublish)->where([['score', '>=', 0]])->select();
      }], 'score')->withCount(['publish' => function ($query, &$alias) use ($searchListPublish) {
        $alias = 'score_count';
        $query->field('id,create_id,score,create_time,update_time')->where($searchListPublish)->where([['score', '>=', 0]])->select();
      }])->withCount(['publish' => function ($query, &$alias) use ($searchListPublish) {
        $alias = 'score_none';
        $query->field('id,create_id,score,create_time,update_time')->where($searchListPublish)->where([['score', '=', -1]])->select();
      }])->withCount(['publish' => function ($query, &$alias) use ($searchListPublish) {
        $alias = 'publish_count';
        $query->field('id,create_id,score,create_time,update_time')->where($searchListPublish)->select();
      }])->page($page, $limit)->order($sortList)->select();
    $list = $users;

    //表格
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->getCellByColumnAndRow(1,1)->setValue('ID');
    $sheet->getCellByColumnAndRow(2,1)->setValue('账号');
    $sheet->getCellByColumnAndRow(3,1)->setValue('总分');
    $sheet->getCellByColumnAndRow(4,1)->setValue('最高分');
    $sheet->getCellByColumnAndRow(5,1)->setValue('最低分');
    $sheet->getCellByColumnAndRow(6,1)->setValue('平均分');
    $sheet->getCellByColumnAndRow(7,1)->setValue('已评数');
    $sheet->getCellByColumnAndRow(8,1)->setValue('未评数');
    $sheet->getCellByColumnAndRow(9,1)->setValue('发表数');
    $sheet->getCellByColumnAndRow(10,1)->setValue('昵称');
    $sheet->getCellByColumnAndRow(11,1)->setValue('真实姓名');
    $sheet->getCellByColumnAndRow(12,1)->setValue('电子邮箱');
    $sheet->getCellByColumnAndRow(13,1)->setValue('手机');
    $sheet->getCellByColumnAndRow(14,1)->setValue('性别');
    $sheet->getCellByColumnAndRow(15,1)->setValue('生日');
    $sheet->getCellByColumnAndRow(16,1)->setValue('简介');
    $sheet->getCellByColumnAndRow(17,1)->setValue('头像');
    $sheet->getCellByColumnAndRow(18,1)->setValue('注册时间');
    $sheet->getCellByColumnAndRow(19,1)->setValue('更新时间');

    foreach ($list as $k=>$v){
      $sheet->getCellByColumnAndRow(1,$k+2)->setValue($v['id']);
      $sheet->getCellByColumnAndRow(2,$k+2)->setValue($v['username']);
      $sheet->getCellByColumnAndRow(3,$k+2)->setValue($v['scoreSum']);
      $sheet->getCellByColumnAndRow(4,$k+2)->setValue($v['scoreMax']);
      $sheet->getCellByColumnAndRow(5,$k+2)->setValue($v['scoreMin']);
      $sheet->getCellByColumnAndRow(6,$k+2)->setValue($v['scoreAvg']);
      $sheet->getCellByColumnAndRow(7,$k+2)->setValue($v['scoreCount']);
      $sheet->getCellByColumnAndRow(8,$k+2)->setValue($v['scoreNone']);
      $sheet->getCellByColumnAndRow(9,$k+2)->setValue($v['publishCount']);
      $sheet->getCellByColumnAndRow(10,$k+2)->setValue($v['nickname']);
      $sheet->getCellByColumnAndRow(11,$k+2)->setValue($v['realname']);
      $sheet->getCellByColumnAndRow(12,$k+2)->setValue($v['email']);
      $sheet->getCellByColumnAndRow(13,$k+2)->setValue($v['phone']);
      $sheet->getCellByColumnAndRow(14,$k+2)->setValue($v['gender']);
      $sheet->getCellByColumnAndRow(15,$k+2)->setValue($v['birthday']);
      $sheet->getCellByColumnAndRow(16,$k+2)->setValue($v['profile']);
      $sheet->getCellByColumnAndRow(17,$k+2)->setValue($v['avatar']);
      $sheet->getCellByColumnAndRow(18,$k+2)->setValue($v['createTime']);
      $sheet->getCellByColumnAndRow(19,$k+2)->setValue($v['updateTime']);
    }

    $excelFileName = '评分汇总_'.md5(json_encode($list,JSON_UNESCAPED_UNICODE) ).'.xlsx';

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

}
