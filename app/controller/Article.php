<?php
declare (strict_types=1);

namespace app\controller;

use OpenApi\Annotations as OA;

use think\Request;
use think\Response;
use \think\db\exception;
use \think\response\Json;
use utils\index as utils;
use \app\model\Article as ArticleModel;
use fileDownload\index as fileDownload;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use \app\model\ArticleFile as ArticleFileModel;

class Article
{
  public $allowKeys = ['title', 'keyword', 'description', 'content', 'cover', 'type', 'status', 'remark'];

  /**
   * 获取 | 文章列表
   * @param Request $request
   * @return Response
   * @throws exception\DataNotFoundException
   * @throws exception\DbException
   * @throws exception\ModelNotFoundException
   * @OA\Get(
   *   path="/article/list",summary="获取 | 文章列表",tags={"Article | 文章"},
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
  public function list(Request $request): Response
  {
    //页数
    $page = (int)$request->param('page', 1);//当前页
    $limit = (int)$request->param('limit', 10);//每页大小
    $type = (int)$request->param('type');//每页大小
    if ($limit === -1) $limit = 999999999;

    //排序方式
    $sortListOri = $request->param('sortList', [['id' => 'desc']]);//排序方式
    $utils = new utils();
    $sortList = [];
    foreach ($sortListOri as $item) {
      $tmpArray = explode('.', array_keys($item)[0]);
      if (count($tmpArray) <= 1) {
        $key = 'article.' . array_keys($item)[0];
        $key = $utils->uncamelize($key);
        $sortList[$key] = array_values($item)[0];
      } else {
        $sortList[array_keys($item)[0]] = array_values($item)[0];
      }
    }

    // 搜索列表
    $searchListOri = $request->param('searchList', []);//搜索列表
    if ($request->has('type')) array_push($searchListOri,['type','=',$type]);
    $searchList = [];
    foreach ($searchListOri as $item) {
      $tmpArray = explode('.', $item[0]);
      if (count($tmpArray) <= 1) {
        $item[0] = 'article.' . $item[0];
        $item[0] = $utils->uncamelize($item[0]);
        array_push($searchList, $item);
      } else {
        array_push($searchList, $item);
      }
    }

    // 数据库查询
    $map1 = is_null($request->userId) ? ['article.status', '=', 1] : ['article.id', '>', '0'];//登录显示全部，否则只显示启用
    $map = empty($map1) ? [] : [$map1];
    $ArticleModel = new ArticleModel;
    $userShowField = ['id', 'nickname', 'avatar', 'username', 'email'];

    if ($limit == -1){
      $list = $ArticleModel->with(['file'])->withJOIN([
        'createUser' => $userShowField,
        'updateUser' => $userShowField
      ], 'left')->whereOr($map)->where($searchList)->order($sortList)->select();
      $count = count($list);
    }else{
      $count = $ArticleModel->withJOIN([
        'createUser' => $userShowField,
        'updateUser' => $userShowField
      ], 'left')->whereOr($map)->where($searchList)->count();
      $list = $ArticleModel->with(['file'])->withJOIN([
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
        'type' => $type,
        'sortList' => $sortListOri,
        'searchList' => $searchListOri,
      ],
    ];
    return json($data, intval($status));
  }

  /**
   * 获取 | 指定文章
   * @param Request $request
   * @return Json
   * @OA\Get(
   *   path="/article/read",summary="获取 | 指定文章",tags={"Article | 文章"},
   *   security={{"Authorization":{}}},
   *   @OA\Parameter(name="X-Requested-With",in="header",@OA\Schema(type="string",default="XMLHttpRequest"),description="请以Ajax方式请求"),
   *   @OA\Parameter(name="Authorization",in="header",required=false,description="Token：登录显示任意数据 | 不登陆只显示 status==1",@OA\Schema(type="bearerAuth")),
   *   @OA\Parameter(name="id",in="query",required=true,@OA\Schema(type="integer"),example=1,description="页数：显示全部为-1"),
   *   @OA\Response(response=200, description="OK", @OA\JsonContent()),
   * )
   */
  public function read(Request $request): Json
  {
    // 数据库查询
    $id = $request->param('id');
    $map1 = is_null($request->userId) ? ['status', '=', 1] : ['id', '>', '0'];//登录显示全部，否则只显示启用
    $map = empty($map1) ? [] : [$map1];

    $result = ArticleModel::whereOr($map)->where('id', '=', $id)->with(['file'])->findOrEmpty();

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
   *  保存 | 新增文章
   * @param Request $request
   * @return Response
   * @OA\Post(
   *   path="/article/add",summary="保存 | 新增文章",tags={"Article | 文章"},
   *   security={{"Authorization":{}}},
   *   @OA\Parameter(name="X-Requested-With",in="header",@OA\Schema(type="string",default="XMLHttpRequest"),description="请以Ajax方式请求"),
   *   @OA\Response(response=200, description="OK", @OA\JsonContent()),
   *   @OA\RequestBody(
   *   required=true,
   *    @OA\MediaType(
   *      mediaType="application/json",
   *      @OA\Schema(
   *        required={"type"},
   *        @OA\Property(property="title",type="string"),
   *        @OA\Property(property="content",type="string"),
   *        @OA\Property(property="cover",type="string"),
   *        @OA\Property(property="type",type="integer"),
   *        @OA\Property(property="remark",type="string"),
   *        @OA\Property(property="status",type="integer",default=1),
   *        example={"title": "", "content": "", "cover": "", "type": 1, "remark": "", "status": 1}
   *      )
   *    )
   *   )
   * )
   */
  public function save(Request $request)
  {
    $item = [];
    $item['createId'] = $request->userId;
    $allowKeys = $this->allowKeys;
    foreach ($allowKeys as $key) {
      if ($request->has($key, 'post')) $item[$key] = $request->param($key);
    }
    $articleModel = new ArticleModel;
    $articleModel->save($item);

    //记录上传文件
    if ($request->has('fileIds', 'post')) {
      //两种传参方式，优先级：fileIds(逗号隔开,string) > fileIds[](array)
      if (is_string($request->param('fileIds'))) {
        $fileIdsArr = explode(",", $request->param('fileIds'));
      } elseif (is_array($request->param('fileIds'))) {
        $fileIdsArr = $request->param('fileIds');
      } else {
        $fileIdsArr = [];
      }

      $articleFile = $articleModel->file;
      $articleFile()->attach($fileIdsArr, ['create_id' => $item['createId']]);
      /*$articleFiles = [];
      foreach ($fileIdsArr as $fileId) {
          if (preg_match("/^[1-9][0-9]*$/", $fileId)) {
              array_push($articleFiles, [
                  'article_id' => $articleModel->id,
                  'file_id' => $fileId
              ]);
          }
      }

      if (count($articleFiles) > 0) {
          $articleFilesModel = new ArticleFileModel;
          $articleFilesModel->saveAll($articleFiles);
      }*/
    }

    $status = 201;
    $data['status'] = $status;
    $data['msg'] = '添加成功';
    $data['result'] = $articleModel;
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
      $item = ArticleModel::findOrEmpty($id);
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
      $delList = ArticleModel::where('id', 'in', request()->param('ids'))->select();
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
    $article['updateId'] = $request->userId;
    $id = $request->param('id');
    $article = ArticleModel::where('id', $id)->findOrEmpty();

    if ($article->isEmpty()) {
      $status = 202;
      $msg = '找不到此记录';
    } else {
      $allowKeys = $this->allowKeys;
      foreach ($allowKeys as $key) {
        if ($request->has($key, 'post')) $article[$key] = $request->param($key);
      }
      $article->save();

      //记录上传文件
      if ($request->has('fileIds', 'post')) {
        //两种传参方式，优先级：fileIds(逗号隔开,string) > fileIds[](array)
        if (is_string($request->param('fileIds'))) {
          $fileIdsArr = explode(",", $request->param('fileIds'));
        } elseif (is_array($request->param('fileIds'))) {
          $fileIdsArr = $request->param('fileIds');
        } else {
          $fileIdsArr = [];
        }

        $articleFile = $article->file;
        $fileIds = array_map(function ($v) {
          return $v['id'];
        }, $articleFile);

        $delIds = array_diff($fileIds, $fileIdsArr);//需要删除的旧ID，差集
        $addIds = array_diff($fileIdsArr, $fileIds);//需要新增的新ID，差集

        $articleFile()->detach($delIds, ['deleteId' => $article['updateId']]);
        $articleFile()->attach($addIds, ['createId' => $article['updateId']]);

      }

    }

    $status = 200;
    $msg = '更新成功';
    $data['status'] = $status;
    $data['msg'] = $msg;
    return json($data, intval($status));
  }

  /**
   * 导出文章
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
        $key = 'article.' . array_keys($item)[0];
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
        $item[0] = 'article.' . $item[0];
        $item[0] = $utils->uncamelize($item[0]);
        array_push($searchList, $item);
      } else {
        array_push($searchList, $item);
      }
    }

    $articleModel = new ArticleModel;
    $userShowField = ['id', 'nickname', 'avatar', 'username', 'email'];

    if ($limit == -1) {
      $list = $articleModel->withJOIN([
        'createUser' => $userShowField,
        'updateUser' => $userShowField
      ], 'left')->where($searchList)->order($sortList)->hidden(['password'])->select()->toArray();
    } else {
      $list = $articleModel->withJOIN([
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

    $excelFileName = '文章数据导出_' . md5(json_encode($list, JSON_UNESCAPED_UNICODE)) . '.xlsx';
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
