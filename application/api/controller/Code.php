<?php

namespace app\api\controller;

class Code extends Common
{

    public function get_code()
    {
        $username = $this->params['username'];
        $exist = $this->params['is_exist'];
        $username_type = $this->check_username($username);
        switch ($username_type) {
            case 'phone':
                $this->get_code_by_username($username, 'phone', $exist);
                break;
            case 'email':
                $this->get_code_by_username($username, 'email', $exist);
                break;
        }
    }

    /**
     * 通过手机/邮箱获取验证码
     * @param [string] $username [手机/邮箱号]
     * @param [int]    $exist [手机/邮箱号是否应该存在于数据库中 1：是 0：否]
     * @return [json]         [api返回的json数据]
     */
    public function get_code_by_username($username, $type, $exist)
    {
        if ($type == 'phone') {
            $type_name = '手机';
        } else {
            $type_name = '邮箱';
        }
        //    检测手机/邮箱号是否存在
        $this->check_exist($username, $type, $exist);
        //    检查验证码请求频率 30秒一次
        if (session('?' . $username . '_last_send_time')) {
            if (time() - session($username . '_last_send_time') < 30) {
                $this->return_msg(400, $type_name . '验证码，每30秒只能发送一次!');
            }
        }
        //    生成验证码
        $code = $this->make_code(6);
        //    使用session存储验证码，方便比对，md5加密
        $md5_code = md5($username . '_' . md5($code));
        session($username . '_code' . $md5_code);
        //    使用过session存储验证码的发送时间
        session($username . '_last_send_time', time());
        //    发送手机/邮箱发送验证码
        if ($type == 'phone') {
            $this->send_code_to_phone($username, $code);
        } else {
            $this->send_code_to_email($username, $code);
        }
    }

    /**
     * 生成验证码
     * @param [int] $num  [验证码的位数]
     * @return[int]      ［生成的验证码］
     */
    private function make_code($num)
    {

        $max = pow(10,$num)-1;
        $min = pow(10,$num-1);
        return rand($min,$max);
    }

    private function send_code_to_phone($username, $code)
    {
        echo 'send_code_to_phone';
    }

    private function send_code_to_email($email, $code)
    {
       $toemail = $email;
       $mail = new \phpmailer\PHPMailer();
       $mail->isSMTP();
       $mail->CharSet = 'utf8';
       $mail->Host = 'smtp.163.com';
       $mail->SMTPAuth=true;
       $mail->Username='weixingchana@163.com';
       $mail->Password='Bear12345';
       $mail->SMTPSecure='ssl'; //apache需要开启openssl
       $mail->Port = 994;
       $mail->setFrom('weixingchana@163.com','接口测试');
       $mail->addAddress($toemail,'test');
       $mail->addReplyTo('weixingchana@163.com','Reply');
       $mail->Subject = "您有新的验证码!";
       $mail->Body="这是一个测试邮件,您的验证码为$code,验证码的有效期为1分钟,本邮件请勿回复!";
       if (!$mail->send()){
           $this->return_msg(400,$mail->ErrorInfo);
       }else{
           $this->return_msg(200,"验证码已经发送成功,请注意查收!");
       }

    }
}
