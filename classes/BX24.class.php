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
 * Description of BX24
 *
 * @author Sergey Ilyin <developer@ilyins.ru>
 */
class BX24 {

    public static function callMethod($bx24Method, $bx24Data) {
        $url = CRM_HOST.'/rest/1/'.CRM_SECRET."/{$bx24Method}";
        $result = cURL::executeRequest('POST', $url, false, false, $bx24Data);
        return $result;
    }

    public static function sendMessageOpenLine($chatId, $message, $name = 'DEFAULT') {
        $bx24Data = http_build_query(
                array(
                    'CHAT_ID' => $chatId,
                    'MESSAGE' => $message,
                    'NAME' => $name,
                    )
                );
        return static::callMethod('imopenlines.bot.session.message.send', $bx24Data);
    }

    public static function finishSessionOpenLine($chatId) {
        $bx24Data = http_build_query(
                array(
                    'CHAT_ID' => $chatId,
                    )
                );
        return static::callMethod('imopenlines.bot.session.finish', $bx24Data);
    }
}
