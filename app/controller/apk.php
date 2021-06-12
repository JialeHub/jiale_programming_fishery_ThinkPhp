<?php


namespace app\controller;


use app\BaseController;
use fileDownload\index as fileDownload;
use think\Request;
use utils\index as utils;

class apk extends BaseController
{
  public $latest = "1.0.0";

  public function index(Request $request){
    $latest = $this->latest;
    $data=[];
    $url = 'http://' . $_SERVER['HTTP_HOST'] . "/apk/download";
    $appidCheck = "__UNI__1127D4B";
    $note =
      "<strong>* ".$latest."版本更新内容：</strong>
            1.渔业信息采集系统;
            2.该APP隶属于嘉乐的程序设计;

            <strong>* 重要须知：</strong>
            新版本已更新证书信息，若当前版本低于1.0.0，安装前请手动卸载当前版本。
            ";

    $data["code"] = 0;
    if ($request->has('appid') && $request->has('version')) {
      $appid =  $request->param('appid');
      $version = $request->param('version'); //客户端版本号
      if ($appid === $appidCheck) { //校验appid
        if ($version !== $latest) { //这里是示例代码，真实业务上，最新版本号及relase notes可以存储在数据库或文件中
          $data["code"] = 1;
        }
      }
    }
    $status = 200;
    $data["latest"] = $latest;
    $data["note"] = $note;
    $data["url"] = $url; //应用升级包下载地址
    $data['status'] = $status;
    $data['msg'] = '获取成功';
    return json($data, intval($status));
  }

  public function download(){
    $FileDownload=new FileDownload();
    $latest = $this->latest;
    //$file = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'.apk';
    $utils = new utils();
    $fileRoot = config('filesystem')['disks']['public']['root'].'/';
    $file = $utils->dirPathFormat($fileRoot . 'apk/GDOUPG_'.$latest.'.apk');
    $name = '';

    $flag = $FileDownload->download($file, $name, true);

    if (!$flag) {
      abort(404, '文件不存在或已被删除');
    }

  }
}
