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
        preg_match ('/([A,B]\d{4,5})|(\d{1,5})$/', preg_replace('/\D/', '', $requestJson->request->command), $matches);
        $balance = (empty($matches[2])) ? null : static::getCurrentBalance($matches[2]);
        $balanceText = (empty($balance)) ? 'Для получения баланса отправьте лицевой счет из десяти цифр, указанный в договоре. Также можно отправить часть кода, идущую после нулей.' : "Ваш баланс в рублях: $balance";
        $response['response']['text'] = $balanceText;
        $response['response']['end_session'] = (empty($balance)) ? false : true;
//        $response['response']['end_session'] = true;
        $response['version'] = '1.0';
        $response['session'] = $requestJson->session;

        return json_encode($response);
    }

    public static function sixMonthsClose() {
        $wCTVTariffs = array(169);
        $sql = '
SELECT contract_status.cid FROM contract_status
LEFT JOIN contract_module ON contract_status.cid=contract_module.cid 
WHERE (contract_module.mid=15)AND((contract_status.date2 IS NULL)OR(contract_status.date2 >CURDATE()))AND(contract_status.status<>0)AND(DATEDIFF(CURDATE(),contract_status.date1)>180) 
ORDER BY DATEDIFF(CURDATE(),contract_status.date1) DESC 
LIMIT 1000
';
        $url = 'http://' . BGB_HOST . ':8080/bgbilling/executer?user=' . 
            BGB_USER . '&pswd=' . BGB_PASSWORD . 
            '&module=sqleditor&base=main&action=SQLEditor&sql=' . urlencode($sql);

        $xml = simplexml_load_string(file_get_contents($url));
        for ($i=0;count($xml->table->data->row)>$i;$i++) {
            $cid = (int) $xml->table->data->row[$i]->attributes()->row0;
            file_put_contents(date("Ymd") . '_six_inet.log', PHP_EOL . "Working with cid $cid:" . PHP_EOL, FILE_APPEND);
            $inetServList = static::inetServList($cid);
            for ($i=0; $i<count($inetServList->data->return); $i++) {
                static::changeContractStatus($cid, 4, "Time | {$inetServList->data->return[$i]->deviceTitle} | {$inetServList->data->return[$i]->interfaceTitle}");
                static::inetServDelete($inetServList->data->return[$i]->id, true);
            }
            $urlDeleteInet = 'http://' . BGB_HOST . ':8080/bgbilling/executer?user=' . BGB_USER . 
                '&pswd=' . BGB_PASSWORD . "&module=contract&action=ContractModuleDelete&module_id=15&cid=$cid";
            cURL::executeRequest('POST', $urlDeleteInet, false, false, false);
            static::contractGroupRemove($cid, 18);
            static::contractGroupRemove($cid, 30);
            static::updateParameter(6, $cid, 41, null);
            static::updateParameter(1, $cid, 43, null);
            $contractTariffList = static::contractTariffList($cid);
            for ($i=0;$i<count($contractTariffList->data->return);$i++) {
                static::contractTariffUpdate($contractTariffList->data->return[$i]);
                if (in_array($contractTariffList->data->return[$i]->tariffPlanId, $wCTVTariffs)) {
                    $tariff = new stdClass();
                    $tariff->contractId = $cid;
                    $tariff->tariffPlanId=85;
                    static::contractTariffUpdate($tariff);
                    static::updateContractLimit($cid, -1000, 'Time');
                }
            }
            
        }
        echo 'ok';
    }

    private static function getPaCl($function) {
        $param = new stdClass();
        switch ($function) {
            case 'inetServList':
                $param->package = 'ru.bitel.bgbilling.modules.inet.api/15';
                $param->class = 'InetServService';
                break;
            case 'changeContractStatus':
                $param->package = 'ru.bitel.bgbilling.kernel.contract.status';
                $param->class = 'ContractStatusMonitorService';
                break;
            case 'inetServDelete':
                $param->package = 'ru.bitel.bgbilling.modules.inet.api/15';
                $param->class = 'InetServService';
                break;
            case 'contractGroupRemove':
            case 'contractParameterGet':
            case 'contractParameterUpdate':
                $param->package = 'ru.bitel.bgbilling.kernel.contract.api';
                $param->class = 'ContractService';
                break;
            case 'contractTariffList':
            case 'contractTariffUpdate':
                $param->package = 'ru.bitel.bgbilling.kernel.contract.api';
                $param->class = 'ContractTariffService';
                break;
            case 'updateContractLimit':
                $param->package = 'ru.bitel.bgbilling.kernel.contract.limit';
                $param->class = 'contractLimitService';
                break;

            default:
                break;
        }
        return $param;
    }

    private static function getJSON($function, $params) {
        $param = static::getPaCl($function);
        $param->method = $function;
        $param->params = $params;
        file_put_contents(date("Ymd") . '_six_inet.log', "$function" . implode(",", $params) . ".....", FILE_APPEND);
        $json = static::execute($param);
        file_put_contents(date("Ymd") . '_six_inet.log', $json->status . PHP_EOL, FILE_APPEND);
        return $json;
    }

    private static function inetServList($cid) {
        $params['contractId'] = $cid;
        $params['orderBy'] = null;
        return static::getJSON(__FUNCTION__, $params);
    }

    private static function changeContractStatus($cid, $statusId, $comment) {
        $params['cid'] = array($cid);
        $params['statusId'] = $statusId;
        $params['dateFrom'] = date('c');
        $params['comment'] = $comment;
        $params['confirmChecked'] = false;
        return static::getJSON(__FUNCTION__, $params);
    }

    private static function inetServDelete($id, $force) {
        $params['id'] = $id;
        $params['force'] = $force;
        return static::getJSON(__FUNCTION__, $params);
    }

    private static function contractGroupRemove($contractId, $contractGroupId) {
        $params['contractId'] = $contractId;
        $params['contractGroupId'] = $contractGroupId;
        return static::getJSON(__FUNCTION__, $params);
    }

    private static function updateContractLimit($contractId, $limit, $comment) {
        $params['contractId'] = $contractId;
        $params['limit'] = $limit;
        $params['comment'] = $comment;
        return static::getJSON(__FUNCTION__, $params);
    }

    private static function contractTariffList($contractId) {
        $params['contractId'] = $contractId;
        $params['date'] = date('c');
        $params['entityMid'] = null;
        $params['entityId'] = null;
        return static::getJSON(__FUNCTION__, $params);
    }

    private static function contractTariffUpdate($tariff) {
        $params['contractTariff']['id'] = $tariff->id ?? null;
        $params['contractTariff']['contractId'] = $tariff->contractId;
        $params['contractTariff']['tariffPlanId'] = $tariff->tariffPlanId;
        $params['contractTariff']['dateFrom'] = (empty($tariff->dateFrom)) ? date('c') : $tariff->dateFrom;
        $params['contractTariff']['dateTo'] = (empty($tariff->id)) ? null : date('c', strtotime("yesterday"));
        $params['contractTariff']['comment'] = "Time";
        return static::getJSON(__FUNCTION__, $params);
    }

    private static function contractParameterGet($contractId, $parameterId) {
        $params['contractId'] = $contractId;
        $params['parameterId'] = $parameterId;
        return static::getJSON(__FUNCTION__, $params);
    }

    private static function updateParameter($type, $cid, $pid, $value) {
        $url = 'http://' . BGB_HOST . ':8080/bgbilling/executer?user=' . BGB_USER . 
            '&pswd=' . BGB_PASSWORD . "&module=contract&action=UpdateParameterType$type&pid=$pid&value=$value&cid=$cid";
        return cURL::executeRequest('POST', $url, false, false, false);
    }
}
