<?php


namespace app\controller;

use Exception;
use Gregwar\Captcha\CaptchaBuilder;
use PHPMailer\PHPMailer\PHPMailer;


class Common
{

    /*
     * 获取验证码
     * */
    public static function newCaptcha($prefix=''){
        $builder = new CaptchaBuilder();
        $builder->build($width = 110, $height = 40);
        $phrase = strtolower($builder->getPhrase());
        $img = $builder->inline();
        $uuid=hash('sha256',$img.uniqid());
        Session('Captcha_'.$prefix.$uuid,$phrase);
        $data['img'] = $img;
        $data['uuid'] = $uuid;
        return $data;
    }

    /*
     * 发送验证邮件
     * */
    public static function sendEmail($addressee='',$addresseeName='',$subject='',$body='')
    {
        $mail = new PHPMailer(true);//传递' true '会启用异常
        try {
            //服务器配置
            $mail->CharSet = "UTF-8";                     //设定邮件编码
            $mail->SMTPDebug = 0;                        // 调试模式输出
            $mail->isSMTP();                             // 使用SMTP
            $mail->Host = 'smtp.office365.com';          // SMTP服务器
            $mail->SMTPAuth = true;                      // 允许 SMTP 认证
            $mail->Username = 'jiale_example@outlook.com'; // SMTP 用户名  即邮箱的用户名
            $mail->Password = 'jiale_example';                // SMTP 密码  部分邮箱是授权码(例如163邮箱)
            $mail->SMTPSecure = 'tls';                    // 允许 TLS 或者ssl协议
            $mail->Port = 587;                            // 服务器端口 25 或者465 具体要看邮箱服务器支持

            $mail->setFrom('jialeFunction@outlook.com', '嘉乐的程序设计 | 渔业信息采集系统');  //发件人
            $mail->addAddress($addressee, $addresseeName);  // 收件人
            //$mail->addAddress('ellen@example.com');  // 可添加多个收件人
            $mail->addReplyTo('jialeFunction@outlook.com', 'info'); //回复的时候回复给哪个邮箱 建议和发件人一致
            //$mail->addCC('cc@example.com');                    //抄送
            //$mail->addBCC('bcc@example.com');                    //密送

            //发送附件
            // $mail->addAttachment('../xy.zip');         // 添加附件
            // $mail->addAttachment('../thumb-1.jpg', 'new.jpg');    // 发送附件并且重命名

            //Content
            $mail->isHTML(true);                                  // 是否以HTML文档格式发送  发送后客户端可直接显示对应HTML内容
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = '您的设备不支持显示HTML';
            $mail->send();
            return True;
        } catch (Exception $e) {
            return False;
        }
    }
}
