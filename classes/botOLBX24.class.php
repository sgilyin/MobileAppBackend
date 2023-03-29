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
    private static function getMonthCost($tariff) {
        $monthCost = 0;
        preg_match_all('/\d*(?=Р)/', $tariff, $matches);
        foreach ($matches[0] as $tariffCost) {
            $monthCost += intval($tariffCost);
        }
        return $monthCost;
    }

    private static function defaultMessage($type) {
        switch ($type) {
            case 'help':
                $message = '
/help - Список команд
/balance <code> - Баланс по ЛС <code>
/subscribe <code> - Подписка на уведомления по ЛС <code> (только ВК и ТГ)
/subscriptions - Список подписок на уведомления (только ВК и ТГ)
/unsubscribe <code> - Отписка от уведомлений по ЛС <code> (только ВК и ТГ)
/unsubscribe all - Отписка от уведомлений по всем ЛС (только ВК и ТГ)

баланс <code> - Баланс по ЛС <code>
лицевой счет <улица> <дом> <квартира> - ЛС по адресу
настроить роутер - Инструкция по настройке роутера
';
                break;
            case 'balance':
                $message = '
Баланс можно узнать следующим образом:

→ Отправив запрос вида "Баланс <код для оплаты>", например "баланс 0123456789".

→ Открыв страницу https://fialka.tv/balance/ через подключение, для которого вы хотите уточнить сумму на счете, предварительно отключив ускорители браузеров, VPN-соединения и мобильный интернет.

→ Спросив его у Алисы в браузере, телефоне или Яндекс Станции. Например, "Алиса, запусти навык Телекомпании Фиалка" и продиктовать код для оплаты.

→ Написав адрес, лицевой счет или номер договора в данном чате. Первый освободившийся оператор вам его сообщит.
';
                break;
            case 'payCode':
                $message = '
Код для оплаты можно узнать следующим образом:

→ Отправив запрос вида "Код для оплаты <Улица> <Дом> <Корпус> <Квартира>", например "код для оплаты Пушкина 11 А 156".

→ Открыв страницу https://fialka.tv/balance/ через подключение, для которого вы хотите узнать лицевой счет, предварительно отключив ускорители браузеров, VPN-соединения и мобильный интернет.

→ Написав адрес или номер договора в данном чате. Первый освободившийся оператор вам его сообщит.
';
                break;
            case 'routerSetup':
                $message = '
Ничего сложного, вот инструкция:
1. Подать питание на роутер (включить блок питания роутера в розетку 220В).
2. Подключить интернетный кабель, приходящий из подъезда, в WAN-порт роутера (обычно синего цвета, "Интернет" или "WAN").
3. Соединить компьютер с LAN-портом роутера (обычно желтого цвета, "LAN" или "Локальная сеть").
4. Проверить работу интернета: открыть браузер и попробовать загрузить страницу любого сайта.

Если интернет не заработал, то необходимо проверить настройки роутера:

1. Проверить, что на компьютере настроено автоматическое получение IP-адреса:
- Нажать сочетание клавиш Win+R → ввести "ncpa.cpl" → нажать Enter.
- В открывшемся окне найти Подключение по локальной сети, щелкнуть правой клавишей мыши и выбрать Свойства.
- Найти в списке IP версии 4 (TCP/IPv4), войти в Свойства.
- Выставить параметры "Получить IP-адрес автоматически" и "Получить адрес DNS-сервера автоматически". Нажать Ок.

2. Зайти на страницу настройки роутера:
- Нажать сочетание клавиш Win+R → ввести "ncpa.cpl" → нажать Enter.
- В открывшемся окне найти Подключение по локальной сети, щелкнуть правой клавишей мыши и выбрать Состояние.
- В открывшемся окне нажать Сведения... и найти строку "Шлюз по умолчанию".
- В адресную строку браузера ввести значение из строки "Шлюз по умолчанию" и нажать Enter.
- Ввести логин и пароль от административной учетной записи роутера. По умолчанию логин: admin, пароль: admin (либо пустое значение).
- Если стандартные учетные данные не подошли, то необходимо сбросить роутер к заводским значениям по инструкции из поисковика. Искать по фразе "как сбросить роутер <марка и модель вашего роутера>".

3. В настройках WAN-порта роутера необходимо выставить получение IP-адреса автоматически (динамически либо используя DHCP). Если у вас в качестве услуги подключен статический IP-адрес, то необходимо вручную прописать данные настройки.

4. В настройках WiFi роутера необходимо задать пароль для сети, чтобы предотвратить несанкционированное использование вашего роутера третьими лицами.

Если все вышеописанное вызывает у вас панику и ужас, то за небольшую сумму наш специалист избавит вас от необходимости постигать азы настройки роутера. Для этого необходимо оставить заявку на настройку роутера в техническую поддержку.
';
                break;
            case 'speedTest':
                $message = '
Как правильно выполнить замер скорости подробно описано [URL=https://fialka.tv/support/speedtest/]тут[/URL].
';
                break;
            case 'visitCash':
                $message = '
Чтобы подключить наши услуги (интернет или ТВ) вам необходимо:
1) Для многоквартирных домов: убедиться, что Ваш адрес доступен для подключения. Посмотреть это можно [URL=https://fialka.tv/net/subscribe/]тут[/URL].
2) Для частного сектора: убедиться, что Ваш адрес доступен для подключения. Посмотреть это можно [URL=https://fialka.tv/net/gepon/]на карте[/URL].
3) Подойти с паспортом в абонентский отдел (Комсомольская д. 19, ТЦ "Протей", 3 этаж) для заключения договора, оплатить подключение и первый месяц пользования услугой.

Чтобы поменять тарифный план или отключить наши услуги абоненту, на которого оформлен договор, необходимо подойти с паспортом в абонентский отдел и написать соответствующее заявление.
';
                break;
            case 'address':
                $message = 'г. Кумертау, ул. Комсомольская, д. 19, ТЦ "Протей", 3 этаж';
                break;
            case 'phone':
                $message = '
Телефоны для связи:
+7 (34761) 4-43-20
+7 (937) 344-43-20
+7 (34761) 4-31-13
+7 (937) 344-31-13
+7 (34761) 4-42-73

Внутренние номера:
1 - По вопросам услуг
2 - Абонентский отдел
3 - При проблемах с ТВ
4 - При проблемах с интернетом
5 - Бухгалтерия
6 - Администрация

E-mail: mail@fialka.tv
';
                break;
            case 'schedule':
                $message = '
Будни: с 09:00 до 18:00
Выходные: с 10:00 до 14:00
';
                break;
            case 'tariff':
                $message = '
[url=http://fialka.tv/net/fl]Тарифы для физических лиц[/url]
[url=http://fialka.tv/net/ul]Тарифы для юридических лиц[/url]
';
                break;
            case 'fuck':
                $message = 'Что, *#@&%?! Это мат?! Не надо так!';
                break;
            case 'faultTV':
                $message = '
Для устранения неисправности вы можете запустить автонастройку телевизора. Основные моменты, как настроить телевидение, перечислены тут: https://fialka.tv/tv/digital/
Если это не помогло, то необходимо вызвать нашего специалиста. Вызов нашего специалиста для устранения неисправности ТВ осуществляется посредством заявки по телефонам:
+7 (34761) 4-31-13, доб. 3
+7 (937) 344-31-13, доб. 3
';
                break;

            default:
                $message = '
Что-то пошло не так. Отправьте команду /help для отображения списка возможных команд чат-бота
';
                break;
        }
        return $message;
    }

    private static function getResponseBalanceText($cid) {
        $contractStatus = BGBilling::getContract($cid)->status;
        $contractAddress = preg_replace('/(\d{0,6}, г. Кумертау, )?(, \d* под.)?(, \d* эт\.)?/', '', BGBilling::getContractParameter($cid, 12)->title);
//        $contractTariff = BGBilling::getContractTariff($cid);
        $contractBalance = BGBilling::getCurrentBalance($cid);
        $countDays = BGBilling::getCountDays($cid, $contractBalance);
        $contractBalanceText = static::getTextBalance($contractBalance);
        switch ($contractStatus) {
            case '0':
                $responseText = ($countDays > -1) ? "$contractBalanceText. Количество дней до блокировки: $countDays." : "$contractBalanceText";
                break;
            case '3':
                $responseText = "$contractBalanceText. Этого не достаточно для работы интернета.";
//                $monthCost = self::getMonthCost($contractTariff);
                $monthCost = BGBilling::getContractCost($cid);
                $minPay = ceil($monthCost - $contractBalance);
                $responseText .= " Нужно доплатить минимум $minPay руб.";
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
        if ($param['data']['USER']['IS_EXTRANET'] == 'Y') {
            $chatId = $param['data']['PARAMS']['CHAT_ID'];
            $inMessage = $param['data']['PARAMS']['MESSAGE'];
            #BALANCE START
            preg_match('/[aа][lл][aа][nн][cс]((\D+)?)?([A,B]\d{4,5}|\d{1,10})?/u', $inMessage, $matchesBalance);
            if ($matchesBalance) {
                if ($matchesBalance[3]) {
                    $cid = (strlen($matchesBalance[3]) == 10) ? intval($matchesBalance[3])-1000000000 : $matchesBalance[3];
                    $message = sprintf('Чат-бот:%s%s%s%s%s',
                        PHP_EOL, self::getResponseBalanceText($cid),
                        PHP_EOL, PHP_EOL, '/help - все возможности чат-бота');
                } else {
                    $message = sprintf('Чат-бот:%s%s%s%s%s',
                        PHP_EOL, self::defaultMessage('balance'),
                        PHP_EOL, PHP_EOL, '/help - все возможности чат-бота');
                }
                BX24::sendMessageOpenLine($chatId, $message);
            }
            #BALANCE END
            #PAY CODE START
            preg_match('/(од для.*оплат?.|ицевой сч.т)([ ](?<street>(пер\. )?\d?\d?[-]?\D*) (?<house>\d+)[ ]?(?<frac>[АаБбВвГгДдЕеЖжЗз]|\/\d)?[ ]?[-]?[ ]?(?<flat>\d+)?)?/u', $inMessage, $matchesPayCode);
            if ($matchesPayCode) {
                if ($matchesPayCode['street']) {
                    $paycode = BGBilling::getContractPayCode($matchesPayCode);
                    $message = sprintf('Чат-бот:%s%s%s%s%s',
                        PHP_EOL, $paycode, PHP_EOL, PHP_EOL,
                        '/help - все возможности чат-бота');
                    #BX24::sendMessageOpenLine($chatId, 'Чат-бот:'. PHP_EOL . $paycode);
                } else {
                    $message = sprintf('Чат-бот:%s%s%s%s%s',
                        PHP_EOL, self::defaultMessage('payCode'), PHP_EOL, PHP_EOL,
                        '/help - все возможности чат-бота');
                    #BX24::sendMessageOpenLine($chatId, static::defaultMessage('payCode'));
                }
                BX24::sendMessageOpenLine($chatId, $message);
            }
            #PAY CODE END
            # BOT COMANDS START
            if (preg_match('/\/(?<cmd>\w+) ?(?<code>\d+)? ?(?<all>all)?/', $inMessage, $sub)) {
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

                    default:
                        BX24::sendMessageOpenLine($chatId, 'Чат-бот:' . PHP_EOL . static::defaultMessage('help'));
                        break;
                }
            }
            # BOT COMANDS END
            #BOT ANSWER START
            $botCheck = array();
            #$botCheck[] = ['', '//'];
            $botCheck[] = ['fuck', '/\b[Бб][Лл][Яя]|[Сс][Уу][Кк][Аа]|[Хх][Уу][ИиЙйЮюЯя]|[Ее][Бб][Уу]|[Ее][Бб][Аа][ЛлНн]|[Пп][Ии][Дд][Оо][Рр]/u'];
            $botCheck[] = ['routerSetup', '/настр.*роутер|настр.*[wW]i[fF]i/'];
            $botCheck[] = ['speedTest', '/корост|пидтест|peedtest|едлен/'];
            $botCheck[] = ['visitCash', '/одключить|тключить|менить|оменять/'];
            $botCheck[] = ['address', '/дрес|аходитесь|ахожден|де.*асса/'];
            $botCheck[] = ['phone', '/елефон|звон|связ/'];
            $botCheck[] = ['schedule', '/рафик/'];
            $botCheck[] = ['tariff', '/ариф/'];
            $botCheck[] = ['faultTV', '/телеви|ТВ|налог|абельн|ифров|омехи|анал/'];
            for ($index = 0; $index < count($botCheck); $index++) {
                preg_match($botCheck[$index][1], $inMessage, $matches);
                if ($matches) {
                    BX24::sendMessageOpenLine($chatId, 'Чат-бот:'. PHP_EOL . static::defaultMessage($botCheck[$index][0]));
                }
            }
            #BOT ANSWER END
        }
    }
}
