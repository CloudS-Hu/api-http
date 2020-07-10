<?php

namespace CloudS\Hu\Api\Http\validator\Rules;

use Rakit\Validation\Rule;

class IdCard extends Rule
{
    protected $message = "The :attribute is not valid identity card";

    public function check($value)
    {
        $value = strtoupper($value);
        $regx = "/(^\d{15}$)|(^\d{17}([0-9]|X)$)/";
        $arrSplit = array();
        if (!preg_match($regx, $value)) {
            return false;
        }
        if (15 == strlen($value)) { //检查15位
            $regx = "/^(\d{6})+(\d{2})+(\d{2})+(\d{2})+(\d{3})$/";
            @preg_match($regx, $value, $arrSplit);
            //检查生日日期是否正确
            $dtmBirth = "19" . $arrSplit[2] . '/' . $arrSplit[3] . '/' . $arrSplit[4];
            if (!strtotime($dtmBirth)) {
                return false;
            } else {
                return true;
            }
        } else { //检查18位
            $regx = "/^(\d{6})+(\d{4})+(\d{2})+(\d{2})+(\d{3})([0-9]|X)$/";
            @preg_match($regx, $value, $arrSplit);
            $dtmBirth = $arrSplit[2] . '/' . $arrSplit[3] . '/' . $arrSplit[4];
            if (!strtotime($dtmBirth)) { //检查生日日期是否正确
                return false;
            } else {
                //检验18位身份证的校验码是否正确。
                //校验位按照ISO 7064:1983.MOD 11-2的规定生成，X可以认为是数字10。
                $arrInt = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
                $arrCh = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
                $sign = 0;
                for ($i = 0; $i < 17; $i++) {
                    $b = (int)$value{$i};
                    $w = $arrInt[$i];
                    $sign += $b * $w;
                }
                $n = $sign % 11;
                $valNum = $arrCh[$n];
                if ($valNum != substr($value, 17, 1)) {
                    return false;
                } else {
                    return true;
                }
            }
        }
    }

}
