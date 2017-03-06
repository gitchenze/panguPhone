<?php

namespace Aze\panguPhone;

class PanguPhone {
    //---------------据和数据平台的---------------------------------

    protected $Sid = '';

    protected $open_id = 'JHee8010f283288e0cb270094e8bc2f42c';

    protected $flow_key = '51066947eee4877564546c1eb64ba34b';

    protected $farce_key = '59b86e0795c24815e0d87f77ae2e476b';
    //---------------end据和数据平台的---------------------------------

    //-----------------秒嘀平台的-------------------------------
    protected $mobile_sid = 'de5c5a7d7f1544aaaefdde433e2df0a3';

    protected $mobile_token = '5e7367d51cf5431aaaf9c965d59b4642';

    //-----------------end秒嘀平台的-------------------------------

    public function __construct($reseller_id = 1) {

    }

    public function test() {
        return 'panguPhone test';
    }

//语音验证码
    public function mobile_voice_code($called = '13796192381', $verifyCode = '123456') {
//        $url = 'https://api.miaodiyun.com/call/voiceCode';
        $url = 'https://api.miaodiyun.com/20150822/call/voiceCode';

        $Sid = $this->mobile_sid;
        $token = $this->mobile_token;
//        $called = '13796192381';
//        $verifyCode = '123456';
        $timestamp = date("YmdHis");
        // 签名
        $sign = md5($Sid . $token . $timestamp);
        $data = [
            //开发者账号
            'accountSid' => $Sid,
            'verifyCode' => $verifyCode,
            'called' => $called,
            'timestamp' => $timestamp,
            'sig' => $sign,
            "respDataType" => "JSON",
            "playTimes" => 3

        ];
        $fields_string = '';
        foreach ($data as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        }
        $fields_string = trim($fields_string, '&');

        $res = $this->http_post($url, $fields_string);
        $res = json_decode($res, true);
        $error_arr = [
            '00023' => '开发者余额不足,短信验证码发送失败',
            '00006' => 'sign错误,短信验证码发送失败',
            '00001' => '未知错误，请联系技术秒嘀客服,短信验证码发送失败',
            '00003' => '请求方式错误,短信验证码发送失败',
            '00004' => '参数非法,短信验证码发送失败',
            '00025' => '手机格式不对,短信验证码发送失败',
            '00036' => '验证码格式不对（4-8位数字）,短信验证码发送失败',
            '00104' => '发送次数上限 一分钟2次 一小时4次 一天10次,短信验证码发送失败',
        ];

        if ($res['respCode'] == '00000') {
            return _success([], '语音验证码发送成功');
            \Redis::set($called, $verifyCode);
        } else {
            return _error($error_arr[$res['respCode']]);

        }
    }

//短信验证码
    public function mobile_number_code($called = '13796192381', $verifyCode = '123456') {

        $url = 'https://api.miaodiyun.com/20150822/industrySMS/sendSMS';
        $Sid = $this->mobile_sid;
        $token = $this->mobile_token;
        //与秒嘀平台的短信模版对应 内容去前面需要完全匹配
        $smsContent = '【陈泽科技】欢迎使用chenze.site网站您的验证码:' . $verifyCode;
        $timestamp = date("YmdHis");
        // 签名
        $sign = md5($Sid . $token . $timestamp);
        $data = [
            //开发者账号
            'accountSid' => $Sid,
            'smsContent' => $smsContent,
            'to' => $called,
            'timestamp' => $timestamp,
            'sig' => $sign,
            "respDataType" => "JSON",
        ];
        $fields_string = '';
        foreach ($data as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        }
        $fields_string = trim($fields_string, '&');

        $res = $this->http_post($url, $fields_string);
//        dd($res);
        $res = json_decode($res, true);
        $error_arr = [
            '00023' => '开发者余额不足,短信验证码发送失败',
            '00006' => 'sign错误,短信验证码发送失败',
            '00001' => '未知错误，请联系技术秒嘀客服,短信验证码发送失败',
            '00003' => '请求方式错误,短信验证码发送失败',
            '00004' => '参数非法,短信验证码发送失败',
            '00025' => '手机格式不对,短信验证码发送失败',
            '00036' => '验证码格式不对（4-8位数字）,短信验证码发送失败',
            '00104' => '发送次数上限 一分钟2次 一小时4次 一天10次,短信验证码发送失败',
        ];

        if ($res['respCode'] == '00000') {
            \Redis::set($called, $verifyCode);
            return _success([], '短信验证码发送成功');
        } else {
            return _error($error_arr[$res['respCode']]);
        }

    }

    //充话费
    public function recharge_telephone_farce($phone = '13796192381', $money = '30', $order = 'rtf12345678') {
        $url = 'http://op.juhe.cn/ofpay/mobile/onlineorder';
        //充值金额,目前可选：10、20、30、50、100、md5(OpenID+key+phoneno+cardnum+orderid)
        $sign = md5($this->open_id . $this->farce_key . $phone . $money . $order);
        $data = [
            'key' => $this->farce_key,
            'phoneno' => $phone,
            'cardnum' => $money,
            'orderid' => $order,
            'sign' => $sign,
        ];
        $res = $this->http_get($url, $data);
        $res = json_decode($res, true);
        if ($res['error_code'] == 0) {
            return _success(['tel' => $phone, 'money' => $money], '话费充值成功');
        } else {
            dd($res);
            return _error('话费充值失败');
        }
    }

//    冲流量
    public function recharge_telephone_flow($phone = '13796192381', $ll = '500', $order = 'ref12345678') {
        $url = 'http://v.juhe.cn/flow/recharge';
//        获取充值套餐的pid
        $check_url = 'http://v.juhe.cn/flow/telcheck';
        $data = [
            'phone' => $phone,
            'key' => $this->flow_key,
        ];
        $type = $this->http_get($check_url, $data);
        $type_arr = json_decode($type, true);

        /*collect($type_arr['result'][0]['flows'])->map(function ($item) use (&$pid, $ll) {
            if ($item['v'] == $ll) {
                $pid = $item['id'];
            }
        });*/
        $flows = !empty($type_arr['result'][0]['flows']) ? $type_arr['result'][0]['flows'] : null;
        if (empty($flows)) {
            return _error('没有获取到手机号对应的流量套餐');
        }
        $pid = collect($flows)->where('v', $ll)->pluck('id')->first();

        if (empty($pid)) {
            return _error('没有对应充值套餐');
        }
//        end获取充值套餐的pid
        $sign = md5($this->open_id . $this->flow_key . $phone . $pid . $order);

        $data = [
            'phone' => $phone,
            'pid' => $pid,
            'orderid' => $order,
            'key' => $this->flow_key,
            'sign' => $sign,
        ];
        $res = $this->http_get($url, $data);

        return $res;

    }

    public function http_post($url, $data) {
        $con = curl_init();
        curl_setopt($con, CURLOPT_URL, $url);
        curl_setopt($con, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($con, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($con, CURLOPT_HEADER, 0);
        curl_setopt($con, CURLOPT_POST, 1);
        curl_setopt($con, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($con, CURLOPT_HTTPHEADER, ['Content-type:application/x-www-form-urlencoded']);
        curl_setopt($con, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($con);
        curl_close($con);

        return $result;

    }

    public function http_get($url, $data = array()) {

        $fields_string = '';

        $delimiter = (strpos($url, '?') !== false) ? '&' : '?';
        foreach ($data as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        }
        $get_url = $url . $delimiter . $fields_string;
        rtrim($fields_string, '&');

        $con = curl_init();
        curl_setopt($con, CURLOPT_URL, $get_url);

        curl_setopt($con, CURLOPT_HEADER, 0);// 不要http header 加快效率
        curl_setopt($con, CURLOPT_TIMEOUT, 15);
        curl_setopt($con, CURLOPT_RETURNTRANSFER, 1);// 要求结果为字符串且输出到屏幕上
        $result = curl_exec($con);
        curl_close($con);

        return $result;

    }
}