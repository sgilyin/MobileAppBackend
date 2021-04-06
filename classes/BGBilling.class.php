<?php

/*
 * Copyright (C) 2020 Sergey Ilyin <developer@ilyins.ru>
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
 * Description of BGBilling
 *
 * @author Sergey Ilyin <developer@ilyins.ru>
 */
class BGBilling {
    public static function getContractInformation($requestJson){
        preg_match ('/^([A,B]\d{5})|(\d{5})$/', $requestJson->contract, $matches);
        $contractTitle = (empty($matches[1])) ? static::getContractTitle($matches[2]) : $matches[1];
        if ($contract = static::checkAccess($contractTitle, $requestJson->password)){
            $responseSuccess = true;
            $responseMessage = '';
            $responseData['id'] = $contract->id;
            $responseData['pay_code'] = 1000000000 + $contract->id;
            $responseData['title'] = $contract->title;
            $responseData['status'] = ($contract->status == 0) ? 'Активен' : 'Не активен';
            $responseData['subscriber'] = static::getContractParameter($contract->id, 1)->value;
            $responseData['address'] = static::getContractParameter($contract->id, 12)->title;
            $responseData['balance'] = static::getCurrentBalance($contract->id);
            $responseData['tariff'] = static::getContractTariff($contract->id);
            $responseData['last_pay'] = static::getLastPay($contract->id);
        } else {
            $responseSuccess = false;
            $responseMessage = 'Wrong contract number or password';
        }
        $response['success'] = $responseSuccess;
        $response['message'] = $responseMessage;
        $response['data'] = $responseData ?? false;
        return json_encode($response);
    }

    private static function execute($param) {
        $url = 'http://' . BGB_HOST . ':8080/bgbilling/executer/json/' . $param->package . '/' . $param->class;
        $post['method'] = $param->method;
        $post['user']['user'] = BGB_USER;
        $post['user']['pswd'] = BGB_PASSWORD;
        $post['params'] = $param->params;
        $json = json_decode(cURL::executeRequest('POST', $url, false, false, json_encode($post)));
        return $json;
    }
    private static function checkAccess($contract, $password) {
        $param->package = 'ru.bitel.bgbilling.kernel.contract.api';
        $param->class = 'ContractService';
        $param->method = 'contractList';
        $param->params['title'] = $contract;
        $param->params['fc'] = -1;
        $param->params['groupMask'] = 0;
        $param->params['subContracts'] = false;
        $param->params['closed'] = true;
        $param->params['hidden'] = false;
        $json = static::execute($param);
        if ($json->status == 'ok' && $json->data->page->recordCount == 1 && $json->data->return[0]->password == $password){
            return $json->data->return[0];
        } else {
            return false;
        }
    }

    private static function getContractTitle($cid) {
        $param->package = 'ru.bitel.bgbilling.kernel.contract.api';
        $param->class = 'ContractService';
        $param->method = 'contractGet';
        $param->params['contractId'] = $cid;
        $json = static::execute($param);
        return $json->data->return->title;
    }

    private static function getContractParameter($cid, $paramId) {
        $param->package = 'ru.bitel.bgbilling.kernel.contract.api';
        $param->class = 'ContractService';
        $param->method = 'contractParameterGet';
        $param->params['contractId'] = $cid;
        $param->params['parameterId'] = $paramId;
        $json = static::execute($param);
        return $json->data->return;
    }

    private static function getCurrentBalance($cid) {
        $param->package = 'ru.bitel.bgbilling.kernel.contract.balance';
        $param->class = 'BalanceService';
        $param->method = 'contractBalanceGet';
        $param->params['contractId'] = $cid;
        $param->params['year'] = date('Y');
        $param->params['month'] = date('n');
        $json = static::execute($param);
        $balance = round($json->data->return->incomingSaldo + $json->data->return->payments - $json->data->return->accounts - $json->data->return->charges, 2);
        return $balance;
    }

    private static function getContractTariff($cid) {
        $param->package = 'ru.bitel.bgbilling.kernel.contract.api';
        $param->class = 'ContractTariffService';
        $param->method = 'contractTariffEntryList';
        $param->params['contractId'] = $cid;
        $param->params['date'] = date('Y-m-d');
        $param->params['entityMid'] = -1;
        $param->params['entityId'] = -1;
        $json = static::execute($param);
        $tariff = array();
        for ($i=0; $i<count($json->data->return); $i++) {
            array_push($tariff, $json->data->return[$i]->title);
        }
        return implode(", ", $tariff);
    }

    private static function getLastPay($cid) {
        $param->package = 'ru.bitel.bgbilling.kernel.contract.balance';
        $param->class = 'PaymentService';
        $param->method = 'paymentList';
        $param->params['contractId'] = $cid;
        $param->params['members'] = 1;
        $json = static::execute($param);
        $last_pay = end($json->data->return);
        return $last_pay->date . " | " . $last_pay->sum . ' руб.';
    }

    public static function getBalanceYandex($requestJson){
        preg_match ('/([A,B]\d{5})|(\d{5})$/', preg_replace('/[[:space:]]/', '', $requestJson->request->command), $matches);
        $balance = static::getCurrentBalance($matches[2]);
//        $balanceText = (empty($balance)) ? 'Ваш баланс получить не удалось. Уточните лицевой счет, пожалуйста.' : "Ваш баланс в рублях: $balance";
        $balanceText = (empty($balance)) ? 'Для получения баланса сообщите лицевой счет, указанный в договоре' : "Ваш баланс в рублях: $balance";
        $response['response']['text'] = $balanceText;
        $response['response']['end_session'] = (empty($balance)) ? false : true;
        $response['version'] = '1.0';
//        
//        $contractTitle = (empty($matches[1])) ? static::getContractTitle($matches[2]) : $matches[1];
//        if ($contract = static::checkAccess($contractTitle, $requestJson->password)){
//            $responseSuccess = true;
//            $responseMessage = '';
//            $responseData['id'] = $contract->id;
//            $responseData['pay_code'] = 1000000000 + $contract->id;
//            $responseData['title'] = $contract->title;
//            $responseData['status'] = ($contract->status == 0) ? 'Активен' : 'Не активен';
//            $responseData['subscriber'] = static::getContractParameter($contract->id, 1)->value;
//            $responseData['address'] = static::getContractParameter($contract->id, 12)->title;
//            $responseData['balance'] = static::getCurrentBalance($contract->id);
//            $responseData['tariff'] = static::getContractTariff($contract->id);
//            $responseData['last_pay'] = static::getLastPay($contract->id);
//        } else {
//            $responseSuccess = false;
//            $responseMessage = 'Wrong contract number or password';
//        }
//        $response['success'] = $responseSuccess;
//        $response['message'] = $responseMessage;
//        $response['data'] = $responseData ?? false;
        return json_encode($response);
    }
}
