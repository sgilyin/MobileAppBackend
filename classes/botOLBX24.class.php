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
 * Description of botOLBX24
 *
 * @author Sergey Ilyin <developer@ilyins.ru>
 */
class botOLBX24 {
    private static function defaultMessage($type) {
        switch ($type) {
            case 'dfltMsgHelp':
            case 'dfltMsgBalance':
            case 'dfltMsgPayCode':
            case 'dfltMsgRouterSetup':
            case 'dfltMsgSpeedtest':
            case 'dfltMsgDiagInet':
            case 'dfltMsgVisitOffice':
            case 'dfltMsgAddress':
            case 'dfltMsgSchedule':
            case 'dfltMsgTariffs':
            case 'dfltMsgProfanity':
            case 'dfltMsgFaultTV':
            case 'dfltMsgContacts':
            case 'dfltMsgPaymentMethods':
            case 'dfltMsgFaultInternet':
                $fileName = $type;
                break;

            default:
                $fileName = 'dfltMsg';
                break;
        }
        return file_get_contents(__DIR__ . "/../templates/$fileName.tpl");
    }

    private static function getResponseBalanceText($cid) {
        $contractBalance = BGBilling::getCurrentBalance($cid);
        $countDays = BGBilling::getCountDays($cid, $contractBalance);
        $contractBalanceText = self::getTextBalance($contractBalance);
        switch (BGBilling::getContract($cid)->status) {
            case '0':
                $responseText = ($countDays > -1) ? "$contractBalanceText. Количество дней до блокировки: $countDays." : "$contractBalanceText";
                break;
            case '3':
                $minPay = ceil(BGBilling::getContractCost($cid) - $contractBalance);
                $responseText = "$contractBalanceText. Этого не достаточно для работы интернета. Нужно доплатить минимум $minPay руб.";
                break;
            case '4':
                $responseText = "$contractBalanceText. Ваш договор приостановлен. Для возобновления услуг необходимо обратиться в абонентский отдел. При себе необходимо иметь документ, удостоверяющий личность.";
                break;

            default:
                $responseText = "Что-то пошло не так. Повторите попытку через несколько минут.";
                break;
        }
        return $responseText;
    }

    private static function getTextBalance($balance) {
        $exp = explode('.', $balance);
        $result = ($exp[1]) ? "$exp[0] руб. $exp[1] коп" : "$exp[0] руб";
        return $result;
    }

    private static function getMsngrPid($msngr) {
        switch ($msngr) {
            case 'telegrambot':
                $pid = 54;
                break;
            case 'vkgroup':
                $pid = 55;
                break;

            default:
                break;
        }
        return $pid;
    }

    private static function subscribe($msngr, $uid, $cid) {
        switch ($msngr) {
            case 'telegrambot':
            case 'vkgroup':
                $address = preg_replace(
                    array('/\d{6}/', '/ г. Кумертау/', '/ \d* под./',
                        '/ \d* эт./', '/,/', '/^ /'),
                    '', BGBilling::getContractParameter($cid, 12)->title);
                $query = "INSERT INTO msngr_sbscrbrs SET cid=$cid, address='$address', messenger='$msngr', uid=$uid";
                DB::query($query);
                $query = "SELECT uid FROM msngr_sbscrbrs WHERE messenger='$msngr' AND cid=$cid";
                $sqlResult = DB::query($query);
                $uids = '';
                while ($row = $sqlResult->fetch_object()) {
                    $uids .= $row->uid . ';';
                }
                BGBilling::updateParameterTypeString($cid, self::getMsngrPid($msngr), $uids);
                $result = "$address: уведомления подключены";
                break;

            default:
                $result = 'Данная функция доступна только в Телеграм и Вконтакте';
                break;
        }
        return $result;
    }

    private static function unsubscribe($msngr, $uid, $cid) {
        switch ($msngr) {
            case 'telegrambot':
            case 'vkgroup':
                switch ($cid) {
                    case 'all':
                        $address = 'Все подписки';
                        $query = "SELECT cid FROM msngr_sbscrbrs WHERE messenger='$msngr' AND uid=$uid";
                        $sqlResult = DB::query($query);
                        while ($row = $sqlResult->fetch_object()) {
                            $cids[] = $row->cid;
                        }
                        $query = "DELETE FROM msngr_sbscrbrs WHERE messenger='$msngr' AND uid=$uid";
                        DB::query($query);
                        break;

                    default:
                        $address = preg_replace(array(
                        '/\d{6}/', '/ г. Кумертау/', '/ \d* под./', '/ \d* эт./', '/,/', '/^ /'),
                        '', BGBilling::getContractParameter($cid, 12)->title);
                        $cids[] = $cid;
                        $query = "DELETE FROM msngr_sbscrbrs WHERE cid=$cid AND address='$address' AND messenger='$msngr' AND uid=$uid";
                        DB::query($query);
                        break;
                }
                foreach ($cids as $cid) {
                    $query = "SELECT uid FROM msngr_sbscrbrs WHERE messenger='$msngr' AND cid=$cid";
                    $sqlResult = DB::query($query);
                    $uids = '';
                    while ($row = $sqlResult->fetch_object()) {
                        $uids .= $row->uid . ';';
                    }
                    BGBilling::updateParameterTypeString($cid, self::getMsngrPid($msngr), $uids);
                }
                $result = "$address: уведомления отключены";
                break;

            default:
                $result = 'Данная функция доступна только в Телеграм и Вконтакте';
                break;
        }
        return $result;
    }

    private static function subscriptions($msngr, $uid) {
        switch ($msngr) {
            case 'telegrambot':
            case 'vkgroup':
                $query = "SELECT address, cid FROM msngr_sbscrbrs WHERE messenger='$msngr' AND uid=$uid";
                $sqlResult = DB::query($query);
                $result = '';
                while ($row = $sqlResult->fetch_object()) {
                    $payCode = 1000000000 + $row->cid;
                    $result .= $row->address . " (ЛС: $payCode)" . PHP_EOL;
                }
                if ($result == '') {
                    $result = 'Нет активных подписок';
                }
                break;

            default:
                $result = 'Данная функция доступна только в Телеграм и Вконтакте';
                break;
        }
        return $result;
    }

    public static function handler($param) {
        if ($param['event'] == 'ONIMBOTJOINCHAT') {
            foreach (self::getBotCommands() as $command) {
                BX24::imbotCommandRegister($command);
            }
        }
        $chatId = $param['data']['PARAMS']['CHAT_ID'];
        $inMessage = $param['data']['PARAMS']['MESSAGE'] ?? '';
        # BOT COMANDS START
        if (preg_match('/\/(?<cmd>\w+) ?(?<code>\d{10})? ?(?<all>all)?(?<address>.*)/', $inMessage, $sub)) {
            $expld = explode('|', $param['data']['PARAMS']['CHAT_ENTITY_ID']);
            $uid = $expld[2];
            $msngr = $expld[0];
            switch ($sub['cmd']) {
                case 'subscriptions':
                    BX24::sendMessageOpenLine($chatId, 'Чат-бот:' .
                        PHP_EOL . self::subscriptions($msngr, $uid));
                    break;
                case 'subscribe':
                    if (isset($sub['code'])) {
                        $cid = $sub['code'] - 1000000000;
                        BX24::sendMessageOpenLine($chatId, 'Чат-бот:' .
                            PHP_EOL . self::subscribe($msngr, $uid, $cid));
                    } else {
                        BX24::sendMessageOpenLine($chatId, 'Чат-бот:'. PHP_EOL . 'Не указан лицевой счет');
                    }
                    break;
                case 'unsubscribe':
                    if (isset($sub['code'])||isset($sub['all'])) {
                    $cid = $sub['all'] ?? $sub['code'] - 1000000000;
                    BX24::sendMessageOpenLine($chatId, 'Чат-бот:' .
                        PHP_EOL . self::unsubscribe($msngr, $uid, $cid));
                } else {
                    BX24::sendMessageOpenLine($chatId, 'Чат-бот:'. PHP_EOL .
                        'Не указан лицевой счет или <all> для удаления всех подписок');
                }
                    break;
                case 'router_setup':
                    BX24::sendMessageOpenLine($chatId,
                        self::defaultMessage('dfltMsgRouterSetup'));
                    break;
                case 'speedtest':
                    BX24::sendMessageOpenLine($chatId,
                        self::defaultMessage('dfltMsgSpeedtest'));
                    break;
                case 'diagnostics_inet':
                    BX24::sendMessageOpenLine($chatId,
                        self::defaultMessage('dfltMsgDiagInet'));
                    break;
                case 'balance':
                    if ($sub['code'] != ''){
                        $cid = (strlen($sub['code']) == 10) ?
                            intval($sub['code'])-1000000000 : $sub['code'];
                        BX24::sendMessageOpenLine($chatId, sprintf('%s%s%s',
                            'Чат-бот:', PHP_EOL, self::getResponseBalanceText($cid)));
                    } else {
                        BX24::sendMessageOpenLine($chatId, sprintf('%s%s%s%s%s',
                            'Чат-бот:', PHP_EOL, 'Не указан лицевой счет.', PHP_EOL,
                            'Формат команды "/balance <paycode>"'));
                    }
                    break;
                case 'contacts':
                    BX24::sendMessageOpenLine($chatId,
                        self::defaultMessage('dfltMsgContacts'));
                    break;
                case 'paycode':
                    if ($sub['address'] != ''){
                        preg_match('/(?<street>(пер\. )?\d?\d?[-]?\D*) (?<house>\d+)[ ]?(?<frac>[АаБбВвГгДдЕеЖжЗз]|\/\d)?[ ]?[-]?[ ]?(?<flat>\d+)?/u', $sub['address'], $address);
                        $paycode = BGBilling::getContractPayCode($address);
                        BX24::sendMessageOpenLine($chatId, sprintf('%s%s%s',
                            'Чат-бот:', PHP_EOL, $paycode));
                    } else {
                        BX24::sendMessageOpenLine($chatId, sprintf('%s%s%s%s%s',
                            'Чат-бот:', PHP_EOL, 'Не указан адрес.', PHP_EOL,
                            'Формат команды "/paycode <Улица> <Дом> <Корпус> <Квартира>"'));
                    }
                    break;
                case 'test':
                    BX24::sendMessageOpenLine($chatId, 'Чат-бот:'. PHP_EOL .
                        '[CALL=+79373444320]+79373444320[/CALL]');
                    break;

                default:
                    BX24::sendMessageOpenLine($chatId,
                        self::defaultMessage('dfltMsgHelp'));
                    break;
            }
        }
        # BOT COMANDS END
        if ($param['data']['USER']['IS_EXTRANET'] == 'Y') {
            #BOT ANSWER START
            $botCheck = array();
            #$botCheck[] = ['', '//'];
            $botCheck[] = ['dfltMsgPayCode', '/од для.*оплат?.|ицевой сч.т/'];
            $botCheck[] = ['dfltMsgProfanity', '/\b[Бб][Лл][Яя]|[Сс][Уу][Кк][Аа]|[Хх][Уу][ИиЙйЮюЯя]|[Ее][Бб][Уу]|[Ее][Бб][Аа][ЛлНн]|[Пп][Ии][Дд][Оо][Рр]/u'];
            $botCheck[] = ['dfltMsgRouterSetup', '/настр.*роутер|настр.*[wW]i[fF]i/'];
            $botCheck[] = ['dfltMsgSpeedtest', '/корост|пидтест|едлен/'];
            $botCheck[] = ['dfltMsgVisitOffice', '/одключить|тключить|менить|оменять|ровести/'];
            $botCheck[] = ['dfltMsgAddress', '/дрес|аходитесь|ахожден|де.*асса/'];
            $botCheck[] = ['dfltMsgContacts', '/елефон|звон|связ/'];
            $botCheck[] = ['dfltMsgSchedule', '/[Гг]рафик/'];
            $botCheck[] = ['dfltMsgTariffs', '/ариф/'];
            $botCheck[] = ['dfltMsgFaultTV', '/телеви| ТВ| тв|ТВ |тв |налог|абельн|ифров|омехи|анал/'];
            $botCheck[] = ['dfltMsgBalance', '/[Бб]аланс|[Оо]статок/'];
            $botCheck[] = ['dfltMsgPaymentMethods', '/плат.*line|плат.*лайн|ак.*плат|line.*плат|лайн.*плат/'];
            $botCheck[] = ['dfltMsgFaultInternet', '/работ.*нет|нет.*работ/'];
            for ($index = 0; $index < count($botCheck); $index++) {
                preg_match($botCheck[$index][1], $inMessage, $matches);
                if ($matches) {
                    BX24::sendMessageOpenLine($chatId, self::defaultMessage($botCheck[$index][0]));
                }
            }
            #BOT ANSWER END
        }
    }

    private function getBotCommands() {
        $commands[0]->COMMAND = 'help';
        $commands[0]->LANG[0]->TITLE = 'Список команд бота';
        $commands[0]->LANG[0]->PARAMS = '';
        $commands[1]->COMMAND = 'balance';
        $commands[1]->LANG[0]->TITLE = 'Баланс по ЛС';
        $commands[1]->LANG[0]->PARAMS = 'paycode';
        $commands[2]->COMMAND = 'paycode';
        $commands[2]->LANG[0]->TITLE = 'Код для оплаты';
        $commands[2]->LANG[0]->PARAMS = 'address';
        $commands[3]->COMMAND = 'router_setup';
        $commands[3]->LANG[0]->TITLE = 'Инструкция по настройке роутера';
        $commands[3]->LANG[0]->PARAMS = '';
        $commands[4]->COMMAND = 'speedtest';
        $commands[4]->LANG[0]->TITLE = 'Инструкция по замеру скорости интернета';
        $commands[4]->LANG[0]->PARAMS = '';
        $commands[5]->COMMAND = 'diagnostics_inet';
        $commands[5]->LANG[0]->TITLE = 'Инструкция по диагностике неисправностей интернета';
        $commands[5]->LANG[0]->PARAMS = '';
        for ($index = 0; $index < count($commands); $index++) {
            $commands[$index]->BOT_ID = BX24_BOT_ID;
            $commands[$index]->COMMON = 'Y';
            $commands[$index]->CLIENT_ID = BX24_BOT_CLIENT_ID;
            $commands[$index]->LANG[0]->LANGUAGE_ID = 'ru';
            $commands[$index]->EVENT_COMMAND_ADD = 'https://backend.fialka.tv';
        }
        return json_decode(json_encode($commands), true);
    }
}
