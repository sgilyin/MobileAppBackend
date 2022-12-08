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

    public static function getContractsInfo($requestJson) {
        $contracts = array();
        for ($index = 0; $index < count($requestJson->contracts); $index++) {
            $contracts[] = self::getContractInfo($requestJson->contracts[$index]);
        }
        $response['contracts'] = $contracts;
        return json_encode($response);
    }

    private function getContractInfo($requestJson) {
        preg_match('/^([A,B]\d{5})|(\d{5})$/', $requestJson->contract, $matches);
        $contractTitle = (empty($matches[1])) ? self::getContractTitle($matches[2]) : $matches[1];
        $patterns = array('/\d{6}/', '/ г. Кумертау/', '/ \d* под./', '/ \d* эт./', '/,/');
        $replacements = '';
        if ($contract = self::checkAccess($contractTitle, $requestJson->password)) {
            $responseSuccess = true;
            $responseMessage = '';
            $responseData['id'] = $contract->id;
            $responseData['pay_code'] = 1000000000 + $contract->id;
            $responseData['title'] = $contract->title;
            $responseData['status'] = ($contract->status == 0) ? 'Активен' : 'Не активен';
            $responseData['subscriber'] = self::getContractParameter($contract->id, 1)->value;
            $responseData['address'] = preg_replace($patterns, $replacements, self::getContractParameter($contract->id, 12)->title);
            $responseData['balance'] = self::getCurrentBalance($contract->id);
            $responseData['tariff'] = self::getContractTariff($contract->id);
            $responseData['last_pay'] = self::getLastPay($contract->id);
        } else {
            $responseSuccess = false;
            $responseMessage = 'Wrong contract number or password';
        }
        $response['success'] = $responseSuccess;
        $response['message'] = $responseMessage;
        $response['data'] = $responseData ?? false;
        return $response;
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
        $param = new stdClass();
        $param->package = 'ru.bitel.bgbilling.kernel.contract.api';
        $param->class = 'ContractService';
        $param->method = 'contractList';
        $param->params['title'] = $contract;
        $param->params['fc'] = -1;
        $param->params['groupMask'] = 0;
        $param->params['subContracts'] = false;
        $param->params['closed'] = true;
        $param->params['hidden'] = false;
        $json = self::execute($param);
        if ($json->status == 'ok' && $json->data->page->recordCount == 1 && $json->data->return[0]->password == $password) {
            return $json->data->return[0];
        } else {
            return false;
        }
    }

    private static function getContractTitle($cid) {
        $param = new stdClass();
        $param->package = 'ru.bitel.bgbilling.kernel.contract.api';
        $param->class = 'ContractService';
        $param->method = 'contractGet';
        $param->params['contractId'] = $cid;
        $json = self::execute($param);
        return $json->data->return->title;
    }

    public static function getContract($cid) {
        $param = new stdClass();
        $param->package = 'ru.bitel.bgbilling.kernel.contract.api';
        $param->class = 'ContractService';
        $param->method = 'contractGet';
        $param->params['contractId'] = $cid;
        $json = self::execute($param);
        return $json->data->return;
    }

    public static function getContractParameter($cid, $paramId) {
        $param = new stdClass();
        $param->package = 'ru.bitel.bgbilling.kernel.contract.api';
        $param->class = 'ContractService';
        $param->method = 'contractParameterGet';
        $param->params['contractId'] = $cid;
        $param->params['parameterId'] = $paramId;
        $json = self::execute($param);
        return $json->data->return;
    }

    public static function getCurrentBalance($cid) {
        $param = new stdClass();
        $param->package = 'ru.bitel.bgbilling.kernel.contract.balance';
        $param->class = 'BalanceService';
        $param->method = 'contractBalanceGet';
        $param->params['contractId'] = $cid;
        $param->params['year'] = date('Y');
        $param->params['month'] = date('n');
        $json = self::execute($param);
        $balance = round($json->data->return->incomingSaldo + $json->data->return->payments - $json->data->return->accounts - $json->data->return->charges, 2);
        return $balance;
    }

    public static function getContractTariff($cid) {
        $param = new stdClass();
        $param->package = 'ru.bitel.bgbilling.kernel.contract.api';
        $param->class = 'ContractTariffService';
        $param->method = 'contractTariffEntryList';
        $param->params['contractId'] = $cid;
        $param->params['date'] = date('Y-m-d');
        $param->params['entityMid'] = -1;
        $param->params['entityId'] = -1;
        $json = self::execute($param);
        $tariff = array();
        for ($i = 0; $i < count($json->data->return); $i++) {
            array_push($tariff, $json->data->return[$i]->title);
        }
        return implode(", ", $tariff);
    }

    private static function getLastPay($cid) {
        $param = new stdClass();
        $param->package = 'ru.bitel.bgbilling.kernel.contract.balance';
        $param->class = 'PaymentService';
        $param->method = 'paymentList';
        $param->params['contractId'] = $cid;
        $param->params['members'] = 1;
        $json = self::execute($param);
        $last_pay = end($json->data->return);
        return $last_pay->date . " | " . $last_pay->sum . ' руб.';
    }

    private static function getTariffCost($tariff) {
        $cost = 0;
        preg_match_all('/\d*(?=Р)/', $tariff, $matches);
        foreach ($matches[0] as $tariffCost) {
            $cost += intval($tariffCost);
        }
        return $cost;
    }

    public static function getCountDays($tariff, $balance) {
        $cost = self::getTariffCost($tariff);
        return floor($balance / ($cost / intval(date("t"))) - 1);
    }

    public static function removeInet($args) {
        $wCTVTariffs = array(76, 85, 101, 108, 109, 169, 275, 276);
        $sql = "
SELECT t_cs.cid
FROM contract_status t_cs
LEFT JOIN contract_module t_cm ON t_cs.cid=t_cm.cid
LEFT JOIN contract t_c ON t_cs.cid=t_c.id
WHERE t_cm.mid=15 AND (t_cs.date2 IS NULL OR t_cs.date2 >CURDATE()) AND t_cs.status<>0 AND NOT ((t_c.gr&(1<<27)>0) OR (t_c.gr&(1<<32)>0) OR (t_c.gr&(1<<39)>0)) AND t_cs.date1<CURRENT_DATE - INTERVAL '{$args['days']}' DAY
ORDER BY DATEDIFF(CURDATE(),t_cs.date1) DESC
LIMIT 1000
";
        $url = 'http://' . BGB_HOST . ':8080/bgbilling/executer?user=' .
                BGB_USER . '&pswd=' . BGB_PASSWORD .
                '&module=sqleditor&base=main&action=SQLEditor&sql=' . urlencode($sql);

        $xml = simplexml_load_string(file_get_contents($url));
        for ($rowItems = 0; $rowItems < count($xml->table->data->row); $rowItems++) {
            $cid = (int) $xml->table->data->row[$rowItems]->attributes()->row0;
            file_put_contents(date("Ymd") . '.log', PHP_EOL . "Working with cid $cid ({$args['days']} days):" . PHP_EOL, FILE_APPEND);
            $inetServList = self::inetServList($cid);
            for ($inetServItems = 0; $inetServItems < count($inetServList->data->return); $inetServItems++) {
                self::changeContractStatus($cid, 3, "Time | {$inetServList->data->return[$inetServItems]->deviceTitle} | {$inetServList->data->return[$inetServItems]->interfaceTitle}");
                self::inetServDelete($inetServList->data->return[$inetServItems]->id, true);
            }
            $urlDeleteInet = 'http://' . BGB_HOST . ':8080/bgbilling/executer?user=' . BGB_USER .
                    '&pswd=' . BGB_PASSWORD . "&module=contract&action=ContractModuleDelete&module_id=15&cid=$cid";
            cURL::executeRequest('POST', $urlDeleteInet, false, false, false);
            self::contractGroupRemove($cid, 18);
            self::contractGroupRemove($cid, 30);
            self::updateParameter(6, $cid, 41, null);
            self::updateParameter(1, $cid, 43, null);
            $contractTariffList = self::contractTariffList($cid);
            for ($contractTariffItems = 0; $contractTariffItems < count($contractTariffList->data->return); $contractTariffItems++) {
                self::contractTariffUpdate($contractTariffList->data->return[$contractTariffItems]);
                if (in_array($contractTariffList->data->return[$contractTariffItems]->tariffPlanId, $wCTVTariffs)) {
                    $tariff = new stdClass();
                    $tariff->contractId = $cid;
                    $tariff->tariffPlanId = 85;
                    self::contractTariffUpdate($tariff);
                    self::updateContractLimit($cid, -1000, 'Time');
                    self::changeContractStatus($cid, 0, "Time | {$inetServList->data->return[$contractTariffItems]->deviceTitle} | {$inetServList->data->return[$contractTariffItems]->interfaceTitle}");
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
                $param->class = 'ContractLimitService';
                break;

            default:
                break;
        }
        return $param;
    }

    private static function getJSON($function, $params) {
        $param = self::getPaCl($function);
        $param->method = $function;
        $param->params = $params;
        file_put_contents(date("Ymd") . '.log', "$function (" .
                serialize($params) . ").....", FILE_APPEND);
#        file_put_contents(date("Ymd") . '.log', "$function.....", FILE_APPEND);
        $json = self::execute($param);
        file_put_contents(date("Ymd") . '.log', $json->status .
                PHP_EOL, FILE_APPEND);
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
        return self::getJSON(__FUNCTION__, $params);
    }

    private static function inetServDelete($id, $force) {
        $params['id'] = $id;
        $params['force'] = $force;
        return self::getJSON(__FUNCTION__, $params);
    }

    private static function contractGroupRemove($contractId, $contractGroupId) {
        $params['contractId'] = $contractId;
        $params['contractGroupId'] = $contractGroupId;
        return self::getJSON(__FUNCTION__, $params);
    }

    private static function updateContractLimit($contractId, $limit, $comment) {
        $params['contractId'] = $contractId;
        $params['limit'] = $limit;
        $params['comment'] = $comment;
        return self::getJSON(__FUNCTION__, $params);
    }

    private static function contractTariffList($contractId) {
        $params['contractId'] = $contractId;
        $params['date'] = date('c');
        $params['entityMid'] = null;
        $params['entityId'] = null;
        return self::getJSON(__FUNCTION__, $params);
    }

    private static function contractTariffUpdate($tariff) {
        $params['contractTariff']['id'] = $tariff->id ?? null;
        $params['contractTariff']['contractId'] = $tariff->contractId;
        $params['contractTariff']['tariffPlanId'] = $tariff->tariffPlanId;
        $params['contractTariff']['dateFrom'] = (empty($tariff->dateFrom)) ? date('c') : $tariff->dateFrom;
        $params['contractTariff']['dateTo'] = (empty($tariff->id)) ? null : date('c', strtotime("yesterday"));
        $params['contractTariff']['comment'] = "Time";
        return self::getJSON(__FUNCTION__, $params);
    }

    private static function contractParameterGet($contractId, $parameterId) {
        $params['contractId'] = $contractId;
        $params['parameterId'] = $parameterId;
        return self::getJSON(__FUNCTION__, $params);
    }

    private static function updateParameter($type, $cid, $pid, $value) {
        $url = 'http://' . BGB_HOST . ':8080/bgbilling/executer?user=' . BGB_USER .
                '&pswd=' . BGB_PASSWORD .
                "&module=contract&action=UpdateParameterType$type&pid=$pid&value=$value&cid=$cid";
        return cURL::executeRequest('POST', $url, false, false, false);
    }

    public static function getContractPayCode($matchesPayCode) {
        $sql = "
SELECT 1000000000+tbl_contract.id AS paycode, CONCAT(tbl_street.title, ' д. ', tbl_house.house, CONCAT_WS( ' кв. ',tbl_house.frac, IF(tbl_flat.flat='',NULL,tbl_flat.flat))) AS 'address'
FROM contract tbl_contract
LEFT JOIN contract_parameter_type_2 tbl_flat ON tbl_contract.id=tbl_flat.cid
LEFT JOIN address_house tbl_house ON tbl_flat.hid=tbl_house.id
LEFT JOIN address_street tbl_street ON tbl_house.streetid=tbl_street.id
WHERE tbl_contract.date2 IS NULL AND tbl_contract.fc=0 AND tbl_street.title='{$matchesPayCode['street']}' AND tbl_house.house={$matchesPayCode['house']}
";
        if ($matchesPayCode['frac']) {
            $sql .= " AND tbl_house.frac LIKE '%{$matchesPayCode['frac']}'";
        }
        if ($matchesPayCode['flat']) {
            $sql .= " AND tbl_flat.flat='{$matchesPayCode['flat']}'";
        }
        $url = 'http://' . BGB_HOST . ':8080/bgbilling/executer?user=' .
                BGB_USER . '&pswd=' . BGB_PASSWORD .
                '&module=sqleditor&base=main&action=SQLEditor&sql=' . urlencode($sql);
        $xml = simplexml_load_string(file_get_contents($url));
        if ($xml->table->data->row[0]) {
            $paycode = $xml->table->data->row[0]->attributes()->row0;
            $address = $xml->table->data->row[0]->attributes()->row1;
            $result = "Код для оплаты (лицевой счет) по адресу: $address - $paycode";
        } else {
            $result .= "Код для оплаты (лицевой счет) не найден. Ждите ответа оператора.";
        }
        return $result;
    }

    public static function updateParameterTypeString($cid, $pid, $value) {
        $url = sprintf('http://%s:8080/bgbilling/executer?module=contract&action=UpdateParameterType1&cid=%d&pid=%d&value=%s&user=%s&pswd=%s&authToSession=0',
            BGB_HOST, $cid, $pid, $value, BGB_USER, BGB_PASSWORD);
        file_get_contents($url);
    }

}
