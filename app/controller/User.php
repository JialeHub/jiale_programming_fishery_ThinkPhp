<?php
declare (strict_types=1);

namespace app\controller;

use OpenApi\Annotations as OA;

use api\index as Api;
use app\BaseController;
use app\model\Dept;
use app\model\UserToken;
use app\model\UserTokenAuth;
use cryptology\RSA;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Session;
use think\Request;
use think\Response;
use \app\model\User as UserModel;
use fileDownload\index as fileDownload;
use utils\index as utils;

class User extends BaseController
{
  public $allowKeys = [ 'username', 'realname', 'nickname', 'email', 'phone', 'url', 'status', 'gender', 'birthday', 'avatar', 'areaCode', 'profile', 'fishingBoats', 'operatingType', 'machinePower', 'emergencyCall'];

  /**
   * 显示用户列表
   *
   * @param Request $request
   * @return Response
   */
  public function index(Request $request){
    //
  }

  /**
   * 获取 | 用户列表
   * @param Request $request
   * @return Response
   * @OA\Get(
   *   path="/user/list",summary="获取 | 用户列表",tags={"User | 用户"},
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
        $key = 'user.' . array_keys($item)[0];
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
        $item[0] = 'user.' . $item[0];
        $item[0] = $utils->uncamelize($item[0]);
        array_push($searchList, $item);
      } else {
        array_push($searchList, $item);
      }
    }

    $userModel = new UserModel;
    $userShowField = ['id', 'nickname', 'avatar', 'username', 'email'];
    if ($limit == -1){
      $list = $userModel->with(['depts','roles'])->withJOIN([
        'createUser' => $userShowField,
        'updateUser' => $userShowField
      ], 'left')->where($searchList)->order($sortList)->hidden(['password'])->select();
      $count = count($list);
    }else{
      $count = $userModel->withJOIN([
        'createUser' => $userShowField,
        'updateUser' => $userShowField
      ], 'left')->where($searchList)->count();
      $list = $userModel->with(['depts','roles'])->withJOIN([
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
   * 导出用户
   *
   * @param int $id
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
        $key = 'user.' . array_keys($item)[0];
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
        $item[0] = 'user.' . $item[0];
        $item[0] = $utils->uncamelize($item[0]);
        array_push($searchList, $item);
      } else {
        array_push($searchList, $item);
      }
    }

    $userModel = new UserModel;
    $userShowField = ['id', 'nickname', 'avatar', 'username', 'email'];

    if ($limit == -1){
      $list = $userModel->withJOIN([
        'createUser' => $userShowField,
        'updateUser' => $userShowField
      ], 'left')->where($searchList)->order($sortList)->hidden(['password'])->select()->toArray();
    }else{
      $list = $userModel->withJOIN([
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
        if (is_array($v))  {
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
   * 注册新用户
   *
   * @param Request $request
   * @return Response
   */
  public function register(Request $request): Response
  {
    $username = $request->param('username');
    $userByLogin = UserModel::where('username', $username)->findOrEmpty();
    $userByEmail = UserModel::where('email', $username)->findOrEmpty();
    if ($userByLogin->isEmpty() && $userByEmail->isEmpty()) {
      $password = $request->param('password');
      $rsa = new RSA();
      $passwordDecrypt = hash('sha3-512', $rsa->decrypt($password)); //des->sha3-512解密
      $form = [
        'username' => $username,
      ];
      $user = UserModel::create($form);
      $sqlPass = hash('sha3-512', ($user->id) . $passwordDecrypt);
      $user->save([
        'password' => $sqlPass,
        'nickname' => '未设置昵称',
        'status' => 1,
      ]);
      $user->roles()->save(4);
      $status = 201;
      $data['status'] = $status;
      $data['msg'] = '注册成功';
      return json($data, intval($status));
    } else {
      $status = 202;
      $data['status'] = $status;
      $data['msg'] = '注册失败，该账号已存在';
      return json($data, intval($status));
    }
  }

  /**
   * 新增用户
   *
   * @param Request $request
   * @return Response
   */
  public function save(Request $request): Response
  {
    $item = [];
    $item['createId'] = $request->userId;
    $allowKeys = $this->allowKeys;
    foreach ($allowKeys as $key) {
      if ($request->has($key, 'post')) $item[$key] = $request->param($key);
    }
    $userModel = new UserModel;
    $userModel->save($item);
    $user = $userModel;
    $id = $user->id;

    // 自定义部门权限关联
    if ($request->has('depts', 'post')) {
      $addDepts = $request->param('depts');
      if (!empty($addDepts)) {
        foreach ($addDepts as $depts){
          $user->depts()->attach($depts,[
            'create_id'=>$request->userId,
            'create_time'=>date('Y-m-d H:i:s')
          ]);
        }
      }
    }
    // 角色关联
    if ($request->has('roles', 'post')) {
      $addMenus =$request->param('roles');
      if (!empty($addMenus)) {
        foreach ($addMenus as $menu){
          $user->roles()->attach($menu,[
            'create_id'=>$request->userId,
            'create_time'=>date('Y-m-d H:i:s')
          ]);
        }
      }
    }
    $user = UserModel::with(['depts','roles'])->findOrEmpty($id);
    $user->visible(['depts' => ['id','name'],'roles' => ['id','name']]);

    $status = 201;
    $data['status'] = $status;
    $data['msg'] = '添加成功';
    $data['result'] = $user->hidden(['password']);
    return json($data, intval($status));
  }

  /**
   * 显示指定用户
   *
   * @param Request $request
   * @return Response
   */
  public function read(Request $request): Response
  {
    $user = UserModel::with(['depts','roles'])->findOrEmpty($request->param('id'));
    $user->visible(['roles' => ['id','name'],'depts' => ['id','name']]);
    if (!$user->isEmpty()) {
      $status = 200;
      $data['info'] = $user;
      $data['msg'] = '获取成功';
    } else {
      $status = 202;
      $data['msg'] = '未找到该用户信息';
    }
    $data['status'] = $status;
    return json($data, intval($status));
  }

  /**
   * 获取个人信息
   *
   * @param Request $request
   * @return Response
   */
  public function info(Request $request): Response
  {
    $data = [];
    $user = UserModel::where(['id' => $request->userId])->with(['depts' => function ($q) {
      $q->field(['name'])->hidden(['pivot']);
    }, 'roles' => function ($q) {
      $q->field(['name', 'level', 'data_scope_model'])->hidden(['pivot']);
    }])->hidden(['password', 'delete_time', 'delete_id', 'pivot'])->findOrEmpty();
    if ($user->status === 0) {
      $status = 403;
      $data['msg'] = '获取失败，该用户处于禁用状态';
    } else if (!$user->isEmpty()) {
      $data['info'] = $user;
      $status = 200;
      $data['msg'] = '获取成功';
    } else {
      $status = 202;
      $data['msg'] = '获取失败，无法找到该用户';
    }
    $data['status'] = $status;
    return json($data, intval($status));
  }

  /**
   * 获取登录验证码
   *
   * @param Request $request
   * @return Response
   */
  public function getLoginCaptcha(Request $request): Response
  {
    $prefix = 'login_';
    //废除上一个验证码
    if ($request->has('lastUuid')) {
      $lastUuid = $request->param('lastUuid');
      if (is_string($lastUuid) && strlen($lastUuid) > 0) {
        Session('Captcha_' . $prefix . $request->param('lastUuid'), null);
      }
    }
    $res = Common::newCaptcha($prefix);
    $status = 200;
    $data['img'] = $res['img'];
    $data['uuid'] = $res['uuid'];
    $data['status'] = $status;
    $data['msg'] = '获取成功';
    return json($data, intval($status));
  }

  /**
   * 登录
   *
   * @param Request $request
   * @return Response
   */
  public function login(Request $request)
  {
    $status = $request->status;
    $data['status'] = $status;

    $username = $request->param('username');
    $password = $request->param('password');
    $rememberMe = $request->param('rememberMe');
    $rememberMeB = $rememberMe === 'true' || $rememberMe == 1;

    //防止暴力破解密码
    $loginSession = Session('Login_' . $username);
    $loginSessionArr = $loginSession ? explode(',', $loginSession) : [];
    if (count($loginSessionArr) > 5 && time() - $loginSessionArr[count($loginSessionArr) - 1] < 10) {
      $status = 406;
      $data['status'] = $status;
      $data['msg'] = '您的操作过于频繁，请10秒后重试';
      return json($data, intval($status));
    } elseif (count($loginSessionArr) > 10 && time() - $loginSessionArr[count($loginSessionArr) - 1] < 30) {
      $status = 406;
      $data['status'] = $status;
      $data['msg'] = '您的操作过于频繁，请30秒后重试';
      return json($data, intval($status));
    }
    Session('Login_' . $username, $loginSession . ',' . time());

    //验证码
    $app = '';//应用模式
    $appParam = $request->param('app');
    //进入后台管理系统需要验证码验证
    if ($appParam === 'admin') {
      $captchaUuid = $request->param('uuid');
      $captchaPhrase = $request->param('phrase');
      $sessionPhrase = Session('Captcha_login_' . $captchaUuid);
      Session('Captcha_login_' . $captchaUuid, null);//销毁验证码
      if (!($sessionPhrase && $captchaPhrase === $sessionPhrase)) {
        $status = 403.1;
        $data['status'] = $status;
        $data['msg'] = '验证码错误';
        return json($data, intval($status));
      } else {
        $app = $appParam;
      }
    }

    $rsa = new RSA();
    $userByLogin = UserModel::where('username', $username)->findOrEmpty();
    $userByEmail = UserModel::where('email', $username)->findOrEmpty();

    if (
      (!$userByLogin->isEmpty() && $userByLogin->password==='') ||
      (!$userByEmail->isEmpty() && $userByEmail->password==='')
    ){
      $status = 401.1;
      $data['status'] = $status;
      $data['msg'] = '该用户暂未设置密码，请重置密码';
      return json($data, intval($status));
    }else{
      $passwordDecrypt = hash('sha3-512', $rsa->decrypt($password)); //des->sha3-512解密

      // 检验账号密码(加入id二次ha3-512解密解密)
      $usernameLogin = !$userByLogin->isEmpty() && $userByLogin->password === hash('sha3-512', ($userByLogin->id) . $passwordDecrypt);
      $emailLogin = !$userByEmail->isEmpty() && $userByEmail->password === hash('sha3-512', ($userByEmail->id) . $passwordDecrypt);
      if ($usernameLogin || $emailLogin) {
        $user = $usernameLogin ? $userByLogin : $userByEmail;
        if  ($user->status===0){
          $status = 403;
          $data['status'] = $status;
          $data['msg'] = '登陆失败，该用户已被禁用';
          return json($data, intval($status));
        }else{
          Session('Login_' . $username, null);
          $status = 200;
          //计算Token：原始唯一标识+应用模式
          $oriToken = hash('sha3-512', $user->id . uniqid(microtime(true) . $user->password));
          $data['token'] = $rsa->encrypt($oriToken . '.' . $app);
          $data['rememberMe'] = $rememberMeB;
          $data['status'] = $status;
          $data['msg'] = $usernameLogin ? '用户登陆成功' : '邮箱登陆成功';
          // 1.判断用户当前设备是否已有token,有则删除
          $cookiesSessionId = cookie('session_id');
          if (is_string($cookiesSessionId) && strlen($cookiesSessionId) > 0) UserToken::where('session_id', $cookiesSessionId)->delete();
          // 2.存进数据库
          $query = [
            'user_id' => $user->id,
            'remember_me' => $rememberMeB,
            'token' => $data['token'],
            'expires' => $rememberMeB ? (60 * 60 * 24 * 14) : (60 * 60 * 24 * 1),
            'x_forwarded_for' => isset($request->server()['HTTP_X_FORWARDED_FOR']) ? $request->server()['HTTP_X_FORWARDED_FOR'] : '',// 多重转发的地址(真实地址)
            'remote_address' => isset($request->server()['REMOTE_ADDR']) ? $request->server()['REMOTE_ADDR'] : '',// 请求/代理地址
            'request_user_agent' => isset($request->server()['HTTP_USER_AGENT']) ? $request->server()['HTTP_USER_AGENT'] : '',// 当前请求的设备信息
            'session_id' => $request->session_id
          ];
          UserToken::create($query);
          return json($data, intval($status));
        }
      } elseif ($userByLogin->isEmpty() && $userByEmail->isEmpty()) {
        $status = 401.1;
        $data['status'] = $status;
        $data['msg'] = '账号不存在';
        return json($data, intval($status));
      } else {
        $status = 401.1;
        $data['status'] = $status;
        $data['msg'] = '账号或密码错误';
        return json($data, intval($status));
      }
    }
  }

  /**
   * 拉取初始化菜单
   *
   * @param Request $request
   * @return Response
   * @throws DataNotFoundException
   * @throws DbException
   * @throws ModelNotFoundException
   */
  public function initMenu(Request $request)
  {
    $data = [];
    $user = UserModel::with(['roles' => function ($q) {
      $q->where('status', 1)->with(['menus' => function ($q) {
        $q->where('status', 1);
      }]);
    }, 'depts' => function ($q) {
      $q->where('status', 1);
    }])->findOrEmpty($request->userId);
    if  ($user->status===0){
      $status = 403;
      $data['msg'] = '获取失败，该用户已被禁用';
    }else if (!$user->isEmpty()) {
      $roles = $user->roles;
      $utils = new Utils();
      $menuList = [];
      $DSM1 = [];//本级数据范围（每个角色都一样）
      // 列出用户所有角色，计算出每个角色的路由信息、权限、数据范围
      foreach ($roles as $role) {
        $menus = $role->menus;
        $DSM2 = [];//自定义数据范围（角色里每个菜单都一样）
        foreach ($menus as $menu) {
          $menuListIds = array_map(function ($v) {
            return ($v['id']);
          }, $menuList);
          $exists = array_search($menu['id'], $menuListIds);
          //若还没添加
          if ($exists === false) {
            $RDSM = $role->data_scope_model;
            $menu->dataScopeModel = [$RDSM];
            $menu->dataScope = [];
            if ($RDSM === 0) {
              $menu->dataScope = [0];
              array_push($menuList, $menu);
            } elseif ($RDSM === 1) {
              if (count($DSM1) > 0) {
                $menu->dataScope = $DSM1;
                array_push($menuList, $menu);
              } else {
                $res = [];
                $depts = $user->depts;//获取用户所属部门
                $allDepts = Dept::select();
                foreach ($depts as $dept) {
                  $subDept = $utils->array2level($allDepts, $dept['pid']);//递归获取下属部门
                  $subDeptIds = array_map(function ($v) {
                    return $v['id'];
                  }, $subDept);
                  $res = array_merge($res, $subDeptIds);//合并
                }
                $res = array_unique($res);//去重
                $menu->dataScope = $DSM1 = $res;
                array_push($menuList, $menu);
              }
            } elseif ($RDSM === 2) {
              if (count($DSM2) > 0) {
                $menu->dataScope = $DSM2;
                array_push($menuList, $menu);
              } else {
                $res = [];
                $depts = $role->depts;//获取部门
                foreach ($depts as $dept) {
                  array_push($res, $dept->id);
                }
                $res = array_unique($res);//去重
                $menu->dataScope = $DSM2 = $res;
                array_push($menuList, $menu);
              }
            }
          } else {
            //若已经添加
            $lastMenu = $menuList[$exists];//已存在的菜单
            $lastDataScopeModel = $lastMenu->dataScopeModel;
            $RDSM = $role->data_scope_model;
            if (in_array(0, $lastDataScopeModel) || (in_array(1, $lastDataScopeModel) && $RDSM === 1)) {
              break;
            } else {
              $lastMenu['dataScopeModel'] = array_unique(array_merge($lastMenu['dataScopeModel'], [$RDSM]));
              if ($RDSM === 1) {
                if (count($DSM1) > 0) {
                  $lastMenu['dataScope'] = array_unique(array_merge($DSM1, $lastMenu['dataScope']));
                } else {
                  $res = [];
                  $depts = $user->depts;//获取用户所属部门
                  $allDepts = Dept::select();
                  foreach ($depts as $dept) {
                    $subDept = $utils->array2level($allDepts, $dept['pid']);//递归获取下属部门
                    $subDeptIds = array_map(function ($v) {
                      return $v['id'];
                    }, $subDept);
                    $res = array_merge($res, $subDeptIds);//合并
                  }
                  $res = array_unique($res);//去重
                  $DSM1 = $res;
                  $lastMenu['dataScope'] = array_unique(array_merge($res, $lastMenu['dataScope']));
                }
              } elseif ($RDSM === 2) {
                if (count($DSM2) > 0) {
                  $lastMenu['dataScope'] = array_unique(array_merge($DSM2, $lastMenu['dataScope']));
                } else {
                  $res = [];
                  $depts = $role->depts;//获取用户
                  foreach ($depts as $dept) {
                    array_push($res, $dept->id);
                  }
                  $res = array_unique($res);//去重
                  $DSM2 = $res;
                  $lastMenu['dataScope'] = array_unique(array_merge($res, $lastMenu['dataScope']));
                }
              }
            }
          }
        }
      }
      //排序
      usort($menuList, function ($a, $b) {
        if ($a['sort'] == $b['sort']) return 0;
        return ($a['sort'] < $b['sort']) ? -1 : 1;
      });
      //批量删除权限
      UserTokenAuth::where('user_token_id', $request->userToken->id)->delete();
      //缓存权限
      $userTokenAuth = new UserTokenAuth();
      $list = [];
      foreach ($menuList as $menu) {
        if (!(is_string($menu['auth']) && $menu['auth'] !== '')) continue;
        $res = [
          'user_token_id' => $request->userToken->id,
          'auth' => $menu['auth'],
          'data_scope' => implode(',', $menu['dataScope']),
          'data_scope_model' => implode(',', $menu['dataScopeModel'])
        ];
        array_push($list, $res);
      }
      $userTokenAuth->saveAll($list);
      //输出路由菜单树
      $tree = $utils->arr2tree($menuList);
      //return0为不返回菜单，节流
      $rsa = new RSA();
      $tokenDecrypt = $rsa->decrypt($request->token);
      $tokenArr = $tokenDecrypt ? explode('.', $tokenDecrypt) : [];
      if (count($tokenArr) >= 1 && $tokenArr[1] === 'admin') {
        $data['menu'] = $tree;
        $status = 200;
        $data['msg'] = '菜单获取成功；后台权限更新成功；';
      } else {
        $status = 201;
        $data['msg'] = '后台权限更新成功；';
      }
    } else {
      $status = 202;
      $data['msg'] = '获取失败，无法找到该用户';
    }
    $data['status'] = $status;
    return json($data, intval($status));
  }

  /**
   * 注销登录
   *
   * @param Request $request
   * @return Response
   */
  public function logout(Request $request): Response
  {
    if (!$request->param('id') && $request->userToken) {
      $request->userToken->delete();
      $status = 200;
      $data['msg'] = '您已退出登录';
    } elseif ($request->param('id')) {
      $flag = UserToken::where('id', $request->param('id'))->delete();
      if ($flag) {
        $data['msg'] = '该设备已成功退出';
        $status = 200;
      } else {
        $data['msg'] = '未找到登录信息';
        $status = 202;
      }
    } else {
      $status = 202;
      $data['msg'] = '未找到登录信息';
    }
    $data['status'] = $status;
    return json($data, intval($status));
  }

  /**
   * 修改用户信息（用户端）
   *
   * @param Request $request
   * @param int $id
   * @return Response
   */
  public function updateInfo(Request $request): Response
  {
    $userId = $request->userId;
    if ($userId) {
      $user = UserModel::where('id', $userId)->where('status', 1)->findOrEmpty();
      if  ($user->status===0){
        $status = 403;
        $data['msg'] = '修改失败，该用户已被禁用';
      }else{
        $msg = [];

        $allowKeys = $this->allowKeys;;
        foreach ($allowKeys as $key) {
          if ($request->has($key, 'post')) $user[$key] = $request->param($key);
        }
        if ($request->has('password')) {
          $rsa = new RSA();
          if ($request->has('passwordOld')) {
            $passwordDecrypt = hash('sha3-512', $rsa->decrypt($request->param('passwordOld'))); //des->sha3-512解密
            $oldPass = hash('sha3-512', $userId . $passwordDecrypt);
            if ($user['password'] === $oldPass) {
              $passwordDecrypt = hash('sha3-512', $rsa->decrypt($request->param('password'))); //des->sha3-512解密
              $sqlPass = hash('sha3-512', $userId . $passwordDecrypt);
              $msg['password'] = $sqlPass;
              $user->save($msg);
              $status = 200;
              $data['msg'] = '信息保存成功';
            } else {
              $status = 401.2;
              $data['msg'] = '旧密码验证失败';
            }
          } else {
            $status = 401.2;
            $data['msg'] = '请输入旧密码';
          }
        } else {
          $user->save($msg);
          $status = 200;
          $data['msg'] = '信息保存成功';
        }
      }
    } else {
      $status = 202;
      $data['msg'] = '未找到用户信息';
    }
    $data['status'] = $status;
    return json($data, intval($status));
  }

  /**
   * 修改用户信息（后台）
   *
   * @param Request $request
   * @param int $id
   * @return Response
   */
  public function update(Request $request): Response
  {
    $id = $request->param('id');
    $userModel = new UserModel;
    $user = $userModel->where('id', $id)->findOrEmpty();

    if ($user->isEmpty()) {
      $status = 202;
      $msg = '找不到此记录';
    } else {
      $user['updateId'] = $request->userId;
      $allowKeys = $this->allowKeys;;
      foreach ($allowKeys as $key) {
        if ($request->has($key, 'post')) $user[$key] = $request->param($key);
      }
      $user->save();

      // 自定义部门权限关联
      if ($request->has('depts', 'post')) {
        // 旧dept
        $oldDepts = array_map(function($item){return $item['id'];},$user->depts->toArray());
        // 新dept
        $depts = $request->param('depts');

        $delDepts = array_values(array_diff($oldDepts,$depts));
        $addDepts = array_values(array_diff($depts,$oldDepts));
        $user->depts()->detach($delDepts);

        if (!empty($addDepts)) {
          foreach ($addDepts as $depts){
            $user->depts()->attach($depts,[
              'create_id'=>$user['updateId'],
              'create_time'=>date('Y-m-d H:i:s')
            ]);
          }
        }
      }
      // 角色关联
      if ($request->has('roles', 'post')) {
        // 旧menus
        $oldMenus = array_map(function($item){return $item['id'];},$user->roles->toArray());
        // 新menus
        $menus =$request->param('roles');

        $delMenus = array_values(array_diff($oldMenus,$menus));
        $addMenus = array_values(array_diff($menus,$oldMenus));

        $user->roles()->detach($delMenus);

        if (!empty($addMenus)) {
          foreach ($addMenus as $menu){
            $user->roles()->attach($menu,[
              'create_id'=>$user['updateId'],
              'create_time'=>date('Y-m-d H:i:s')
            ]);
          }
        }
      }
      $user = UserModel::with(['depts','roles'])->findOrEmpty($id);
      $user->visible(['depts' => ['id','name'],'roles' => ['id','name']]);

      $status = 200;
      $msg = '更新成功';
    }
    $data['status'] = $status;
    $data['result'] = $user->hidden(['password']);
    $data['msg'] = $msg;
    return json($data, intval($status));
//    $hasPermission = false;
//    $user = UserModel::where('id', $id)->findOrEmpty();
//    if ($user->isEmpty()) {
//      $status = 401.1;
//      $msg = '找不到此用户';
//    } else {
//      if (isset($request->authMenu->dataScope)) {
//        //带权限
//        $dataScope = $request->authMenu['data_scope'];
//        $dataScopeArr = explode(',', $dataScope);
//        $dataScopeArr = array_map(function ($v) {
//          return (int)$v;
//        }, $dataScopeArr);
//        if ($dataScopeArr[0] === 0) {
//          //允许操作所有CreateId
//          $hasPermission = true;
//        } else {
//          $createId = $user->createId;
//          $userDepts = UserDept::where('user_id', $createId)->select();
//          foreach ($userDepts as $userDept) {
//            if (in_array($userDept->dept_id, $dataScopeArr)) {
//              $hasPermission = true;
//              break;
//            }
//          }
//        }
//      }
//      if ($hasPermission) {
//        $userId = $request->userId;
//        if ($request->has('status', 'post')) $user['status'] = $request->param('status');
//        if ($request->has('password', 'post')) {
//          $rsa = new RSA();
//          $passwordDecrypt = hash('sha3-512', $rsa->decrypt($request->param('password'))); //des->sha3-512解密
//          $sqlPass = hash('sha3-512', $id . $passwordDecrypt);
//          $user['password'] = $sqlPass;
//        }
//        $user['update_id'] = $userId;
//        $user->save();
//        $status = 200;
//        $msg = '修改成功';
//      } else {
//        $status = 403.1;
//        $msg = '权限不足，无法操作该记录';
//      }
//    }
//
//    $data['status'] = $status;
//    $data['msg'] = $msg;
//    return json($data, intval($status));
  }

  /**
   * 发送找回密码验证码邮件
   *
   * @param Request $request
   * @return Response
   */
  public function sendForgetEmail(Request $request): Response
  {
    $data = [];
    if ($request->has('username')) {
      $username = $request->param('username');
      $userByEmail = UserModel::where('email', $username)->findOrEmpty();
      $sendFlag = False;
      $emailSql = '';
      $usernameSql = '';
      if  ($userByEmail->status===0){
        $status = 403;
        $msg = '发送失败，该用户已被禁用';
      }else if ($userByEmail->isExists()) {
        //直接发送邮件
        $emailSql = $username;
        $usernameSql = $userByEmail->username;
        $sendFlag = True;
      } else {
        $userByLogin = UserModel::where('username', $username)->findOrEmpty();
        //判断是否已经绑定
        if  ($userByLogin->status===0){
          $status = 403;
          $msg = '发送失败，该用户已被禁用';
        }else if ($userByLogin->isExists()) {
          $emailSql = $userByLogin->email;
          $usernameSql = $userByLogin->username;
          if ($emailSql && is_string($emailSql) && strlen($emailSql) > 1) {
            //直接发送邮件
            $sendFlag = True;
          } else {
            $status = 404.2;
            $msg = '您暂未绑定绑定邮箱';
          }
        } else {
          $status = 202;
          $msg = '找不到该账号或电子邮件';
        }
      }
      //发送信号
      if ($sendFlag) {
        //获取IP信息
        $server = $request->server();
        $ip = isset($server['HTTP_X_FORWARDED_FOR']) ? $server['HTTP_X_FORWARDED_FOR'] :
          (isset($server['REMOTE_ADDR']) ? $server['REMOTE_ADDR'] : '');
        $api = new Api();
        $ipInfo = $api->getIPInfo($ip);
        $ip_addr = "";
        if (is_string($ipInfo)) $ipInfo = json_decode($ipInfo);
        if (!is_null($ipInfo) && is_array($ipInfo) && array_key_exists('addr', $ipInfo)) $ip_addr = trim($ipInfo['addr']);
        if (!is_null($ipInfo) && isset($ipInfo->addr)) $ip_addr = trim($ipInfo->addr);
        $code = rand(100000, 999999);
        Session('forgetPassCode_' . $emailSql, $code . ',' . time());
        $res = Common::sendEmail($emailSql, $usernameSql, '密码重置验证码——嘉乐的程序设计 | 渔业信息采集系统',
          '<p>您正在尝试重置密码【IP:' . $ip . '(' . $ip_addr . ')' . '】，以下验证码将成为邮箱验证的凭证，有效期为10分钟，切勿泄露验证码。</p>
                            <p>验证码：<strong>' . $code . '</strong></p>
                            <p>若非本人操作，您的账号可能存在被盗取的风险。</p>
                            <p>——嘉乐的程序设计 | 渔业信息采集系统</p>'
        );
        if ($res) {
          $status = 200;
          $msg = '验证码发送成功';
        } else {
          $status = 500.1;
          $msg = '验证码发送失败';
        }
      }
    } else {
      $status = 400;
      $msg = '请输入用户账号或电子邮件';
    }
    $data['status'] = $status;
    $data['msg'] = $msg;
    return json($data, intval($status));
  }

  /**
   * 邮件验证并返回重置密码临时Token
   *
   * @param Request $request
   * @return Response
   */
  public function getForgetPassToken(Request $request)
  {
    $data = [];
    if (!$request->has('username')) {
      $status = 400;
      $msg = '请输入用户账号或电子邮件';
    } elseif (!$request->has('code')) {
      $status = 400;
      $msg = '请输入邮箱验证码';
    } else {
      $username = $request->param('username');
      $code = $request->param('code');
      $emailSql = "";
      $flag = false;
      $userByEmail = UserModel::where('email', $username)->findOrEmpty();
      if  ($userByEmail->status===0){
        $status = 403;
        $msg = '验证失败，该用户已被禁用';
      }else if ($userByEmail->isExists()) {
        $emailSql = $username;
        $flag = true;
      } else {
        $userByLogin = UserModel::where('username', $username)->findOrEmpty();
        //判断是否已经绑定
        if  ($userByEmail->status===0){
          $status = 403;
          $msg = '验证失败，该用户已被禁用';
        }else if ($userByLogin->isExists()) {
          $emailSql = $userByLogin->email;
          if ($emailSql && is_string($emailSql) && strlen($emailSql) > 1) {
            $flag = True;
          } else {
            $status = 404.2;
            $msg = '您暂未绑定绑定邮箱';
          }
        } else {
          $status = 202;
          $msg = '找不到该账号或电子邮件';
        }
      }

      if ($flag) {
        $session = Session('forgetPassCode_' . $emailSql);
        $sessionArr = ($session && is_string($session)) ? explode(",", $session) : [];
        if (count($sessionArr) > 4 && time() - $sessionArr[count($sessionArr) - 1] < 10) {
          $status = 406;
          $msg = '您的操作过于频繁，请10秒后重试';
        } elseif (count($sessionArr) > 10 && time() - $sessionArr[count($sessionArr) - 1] < 30) {
          $status = 406;
          $msg = '您的操作过于频繁，请30秒后重试';
        } else {
          if (time() - $sessionArr[1] > 600) {
            session('forgetPassCode_' . $emailSql, null);//作废邮箱验证码
            $status = 202;
            $msg = '验证码已过期，请重新获取';
          } elseif ($sessionArr[0] === $code) {
            session('forgetPassCode_' . $emailSql, null);//作废邮箱验证码
            $token = hash('sha3-256', time() . uniqid());
            Session('forgetPassToken_' . $emailSql, $token . ',' . time());
            $data['sessionToken'] = $token;
            $status = 200;
            $msg = '验证成功';
          } else {
            Session('forgetPassCode_' . $emailSql, $session . ',' . time());
            $status = 401.2;
            $msg = '验证码错误，请重新输入';
          }
        }
      }
    }
    $data['status'] = $status;
    $data['msg'] = $msg;
    return json($data, intval($status));
  }

  /**
   * sessionToken验证并设置新密码
   *
   * @param Request $request
   * @return Response
   */
  public function updatePassBySessionToken(Request $request): Response
  {
    $data = [];
    if (!$request->has('username')) {
      $status = 400;
      $msg = '请输入用户账号或电子邮件';
    } elseif (!$request->has('token')) {
      $status = 400;
      $msg = '请输入sessionToken';
    } elseif (!$request->has('password')) {
      $status = 400;
      $msg = '请输入新密码';
    } else {
      $username = $request->param('username');
      $sessionToken = $request->param('token');
      $newPassword = $request->param('password');
      $flag = false;
      $userByEmail = UserModel::where('email', $username)->where('status', 1)->findOrEmpty();
      if  ($userByEmail->status===0){
        $status = 403;
        $msg = '重置失败，该用户已被禁用';
      }else if ($userByEmail->isExists()) {
        $emailSql = $username;
        $user = $userByEmail;
        $flag = true;
      } else {
        $userByLogin = UserModel::where('username', $username)->where('status', 1)->findOrEmpty();
        //判断是否已经绑定
        if  ($userByEmail->status===0){
          $status = 403;
          $msg = '重置失败，该用户已被禁用';
        }else if ($userByLogin->isExists()) {
          $emailSql = $userByLogin->email;
          $user = $userByLogin;
          if ($emailSql && is_string($emailSql) && strlen($emailSql) > 1) {
            $flag = True;
          } else {
            $status = 404.2;
            $msg = '您暂未绑定绑定邮箱';
          }
        } else {
          $status = 202;
          $msg = '找不到该账号或电子邮件';
        }
      }
      if ($flag) {
        $session = Session('forgetPassToken_' . $emailSql);
        $sessionArr = explode(",", $session);
        if (time() - $sessionArr[1] > 600) {
          $status = 202;
          $msg = '会话已过期，请重新获取验证码';
        } elseif ($sessionArr[0] === $sessionToken) {
          $rsa = new RSA();
          $passwordDecrypt = hash('sha3-512', $rsa->decrypt($newPassword)); //des->sha3-512解密
          $sqlPass = hash('sha3-512', $user->id . $passwordDecrypt);
          $res = $user->save(['password' => $sqlPass]);
          if ($res) {
            $status = 200;
            $msg = '修改成功';
          } else {
            $status = 500;
            $msg = '验证成功，修改失败，请联系管理员处理';
          }
        } else {
          $status = 401.2;
          $msg = '验证失败，请重新获取验证码';
        }
        session('forgetPassToken_' . $emailSql, null);//作废临时Token
      }
    }
    $data['status'] = $status;
    $data['msg'] = $msg;
    return json($data, intval($status));
  }

  /**
   * 删除用户
   *
   * @param Request $request
   * @return Response
   */
  public function delete(Request $request): Response
  {
    //$id单个删除,ids批量删除
    $deleteId = $request->userId;
    $id = $request->param('id');
    if (!is_null($id)) {
      $UserModel = new UserModel;
      $item = $UserModel->findOrEmpty($id);
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
      $UserModel = new UserModel;
      $delList = $UserModel->where('id', 'in', request()->param('ids'))->select();
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
