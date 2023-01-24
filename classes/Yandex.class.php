<?php

/*
 * Copyright (C) 2021 Sergey Ilyin <developer@ilyins.ru>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Description of Yandex
 *
 * @author Sergey Ilyin <developer@ilyins.ru>
 */
class Yandex {
    private static function getTextBalance($balance) {
        $exp = explode('.', $balance);
        $result = ($exp[1]) ? "$exp[0] руб $exp[1] коп" : "$exp[0] руб";
        return $result;
    }

    public static function getResponseBalanceText($cid) {
        $contractStatus = BGBilling::getContract($cid)->status;
        $contractAddress = preg_replace('/(\d{0,6}, г. Кумертау, )?(, \d* под.)?(, \d* эт\.)?/', '', BGBilling::getContractParameter($cid, 12)->title);
        $contractTariff = BGBilling::getContractTariff($cid);
        $contractBalance = BGBilling::getCurrentBalance($cid);
        $countDays = BGBilling::getCountDays($cid, $contractBalance);
        $contractBalanceText = static::getTextBalance($contractBalance);
        switch ($contractStatus) {
            case '0':
                $responseText = ($countDays > -1) ? "$contractBalanceText. Этого хватит примерно на $countDays дн." : "$contractBalanceText";
                break;
            case '3':
                $minPay = ceil(300 - $contractBalance);
                $responseText = "$contractBalanceText. Этого не достаточно для работы интернета. Нужно доплатить минимум $minPay руб";
                break;
            case '4':
                $responseText = "$contractBalanceText. Ваш договор приостановлен. Для возобновления услуг необходимо обратиться в телекомпанию.";
                break;

            default:
                $responseText = "Что-то пошло не так. Повторите попытку через несколько минут.";
                break;
        }
        return $responseText;
    }

    public static function getBalance($requestJson){
        if ($cid = $requestJson->state->user->value) {
            switch ($requestJson->request->command) {
                case 'да':
                    $response['user_state_update']['value'] = $cid;
                    $responseText = static::getResponseBalanceText($cid);
                    $response['response']['end_session'] = true;
                    break;

                case 'нет':
                    $response['user_state_update']['value'] = null;
                    $responseText = 'По какому лицевому счету нужен баланс?';
                    $response['response']['end_session'] = false;
                    break;

                default:
                    $address = preg_replace('/(\d{0,6}, г. Кумертау, )?(, \d* под.)?(, \d* эт\.)?/', '', BGBilling::getContractParameter($cid, 12)->title);
                    $responseText = "Вас интересует баланс по адресу $address?";
                    $response['response']['end_session'] = false;
                    break;
            }
        } else {
            preg_match ('/([A,B]\d{4,5})|(\d{1,5})$/', preg_replace('/\D/', '', $requestJson->request->command), $matches);
            if ($matches[2]) {
                $cid = $matches[2];
                $responseText = static::getResponseBalanceText($cid);
                $response['user_state_update']['value'] = $cid;
                $response['response']['end_session'] = true;
            } else {
            $responseText = 'Для получения баланса отправьте лицевой счет из десяти цифр, указанный в договоре. Также можно отправить часть кода, идущую после нулей.';
            $response['user_state_update']['value'] = null;
            }
        }
        $response['response']['text'] = $responseText;
        $response['version'] = '1.0';
        $response['session'] = $requestJson->session;
        return json_encode($response);
    }
}
