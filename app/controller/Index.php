<?php

namespace app\controller;
use fileDownload\index as fileDownload;
use OpenApi\Annotations as OA;

use app\BaseController;
use app\model\Log;
use app\model\Menu;
use \api\index as Api;
use Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use think\facade\Request;
use think\Response;
use think\Session;
use utils\index as utils;

class Index extends BaseController
{

    public function index()
    {
        $status = request()->status === 501 ? 200 : request()->status;
        $data['middle'] = request()->middleMsg;
        $data['msg'] = '嘉乐的程序设计 | 渔业信息采集系统 · ThinkPHP后台接口';
        $data['status'] = $status;
        return json($data, intval($status));
    }

    public function test()
    {
      $list = Request()->param('rows');

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


      $utils = new utils();
      $excelFileName = '数据导出_' . md5(json_encode($list, JSON_UNESCAPED_UNICODE)) . '.xlsx';
      $fileRoot = config('filesystem')['disks']['tempDownload']['root'] . '/';
      $baseUrl = config('filesystem')['disks']['tempDownload']['url'] . '/';
      $tempExcelSrc = $utils->dirPathFormat($fileRoot . $excelFileName);
      $tempExcelPath = $utils->dirPathFormat($baseUrl . $excelFileName);
      $writer = new Xlsx($spreadsheet);
      $writer->save($tempExcelSrc);

      $FileDownload = new FileDownload();
//      $flag = $FileDownload->download($tempExcelSrc, $excelFileName, true);

//      if (!$flag) {
//        abort(404, '文件不存在或已被删除');
//      }

//      return download($tempExcelSrc, $excelFileName);
//      return $tempExcelPath;
      $url = "https://".$_SERVER['HTTP_HOST']."\\".$tempExcelPath;
      return "<a target='_blank ' href='".$url."'> ".$url." </a>";
    }

    public function myTest()
    {
        $data['test'] = Request::isAjax();
        $status = 200;
        $data['$_SERVER'] = $_SERVER;
        $data['$_GET'] = $_GET;
        $data['$_POST'] = $_POST;
        $data['$_FILES'] = $_FILES;
        $data['$_COOKIE'] = $_COOKIE;
        $data['$_REQUEST'] = $_REQUEST;
//        $data['$_ENV'] = $_ENV;
        $data['getallheaders'] = getallheaders();
        $data['request'] = Request::param();
        $data['status'] = $status;
        return $data;
    }

    public function log()
    {
        return json(Log::page(1,10)->order('id','desc')->select());
    }

}
