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
Как правильно выполнить замер скорости подробно описано [URL=https://fialka.tv/question/zamer-skorosti/]тут[/URL].
';
                break;
            case 'visitCash':
                $message = '
Чтобы подключить наши услуги (интернет или ТВ) вам необходимо:
1) Для многоквартирных домов: убедиться, что Ваш адрес доступен для подключения. Посмотреть это можно [URL=https://fialka.tv/net/subscribe/]тут[/URL].
2) Для частного сектора: убедиться, что Ваш адрес доступен для подключения. Посмотреть это можно [URL=https://fialka.tv/net/gepon/]на карте[/URL].
3) Подойти с паспортом в кассу для заключения договора, оплатить подключение и первый месяц пользования услугой.

Чтобы поменять тарифный план или отключить наши услуги абоненту, на которого оформлен дорговор, необходимо подойти с паспортом в кассу и написать соответствующее заявление.
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
1 - По вопросам Интернета
2 - По вопросам ТВ
3 - Касса
4 - Бухгалтерия
5 - Администрация

E-mail: mail@fialka.tv
';
                break;
            case 'schedule':
                $message = '
Пн - Пт: с 09:00 до 18:00
Сб - Вс: с 10:00 до 14:00
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
+7 (34761) 4-31-13, доб. 2
+7 (937) 344-31-13, доб. 2
';
                break;

            default:
                $message = 'Что-то пошло не так';
                break;
        }
        return $message;
    }

    private static function getResponseBalanceText($cid) {
        $contractStatus = BGBilling::getContract($cid)->status;
        $contractAddress = preg_replace('/(\d{0,6}, г. Кумертау, )?(, \d* под.)?(, \d* эт\.)?/', '', BGBilling::getContractParameter($cid, 12)->title);
        $contractTariff = BGBilling::getContractTariff($cid);
        $contractBalance = BGBilling::getCurrentBalance($cid);
        $countDays = BGBilling::getCountDays($contractTariff, $contractBalance);
        $contractBalanceText = static::getTextBalance($contractBalance);
        switch ($contractStatus) {
            case '0':
                $responseText = ($countDays > -1) ? "$contractBalanceText. Этого хватит примерно на $countDays дн." : "$contractBalanceText";
                break;
            case '3':
                $responseText = "$contractBalanceText. Этого не достаточно для работы интернета.";
                $tariffs300 = array (
                    '2018 СуперХит (100М+ТВ/300Р)',
                    '2018 Отличный (100М/300Р)'
                    );
                if (in_array($contractTariff, $tariffs300)) {
                    $minPay = ceil(300 - $contractBalance);
                    $responseText .= " Нужно доплатить минимум $minPay руб.";
                }
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

    private static function getTextBalance($balance) {
        $exp = explode('.', $balance);
        $result = ($exp[1]) ? "$exp[0] руб $exp[1] коп" : "$exp[0] руб";
        return $result;
    }

    public static function handler($param) {
        if ($param['data']['USER']['IS_EXTRANET'] == 'Y') {
            $chatId = $param['data']['PARAMS']['CHAT_ID'];
            $inMessage = $param['data']['PARAMS']['MESSAGE'];
            #BALANCE START
            preg_match('/аланс((\D+)?)?([A,B]\d{4,5}|\d{1,10})?/', $inMessage, $matchesBalance);
            if ($matchesBalance) {
                if ($matchesBalance[3]) {
                    $cid = (strlen($matchesBalance[3]) == 10) ? intval($matchesBalance[3])-1000000000 : $matchesBalance[3];
                    BX24::sendMessageOpenLine($chatId, 'Чат-бот:'. PHP_EOL . static::getResponseBalanceText($cid));
#                    BX24::finishSessionOpenLine($chatId);
                } else {
                    BX24::sendMessageOpenLine($chatId, static::defaultMessage('balance'));
                }
            }
            #BALANCE END
            #PAY CODE START
            preg_match('/(од для.*оплат?.|ицевой сч.т)([ ](?<street>\d{0,2}[ ]?\D+)[ ](?<house>\d+)[ ]?(?<frac>\D)?[ ]?(?<flat>\d+)?)?/u', $inMessage, $matchesPayCode);
            if ($matchesPayCode) {
                if ($matchesPayCode['street']) {
                    $paycode = BGBilling::getContractPayCode($matchesPayCode);
                    BX24::sendMessageOpenLine($chatId, 'Чат-бот:'. PHP_EOL . $paycode);
                } else {
                    BX24::sendMessageOpenLine($chatId, static::defaultMessage('payCode'));
                }
            }
            #PAY CODE END
            #BOT ANSWER START
            $botCheck = array();
            #$botCheck[] = ['', '//'];
            $botCheck[] = ['fuck', '/\bбля|сука|хуй|хуя|ебу|ебал|ебан|пидор/u'];
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
