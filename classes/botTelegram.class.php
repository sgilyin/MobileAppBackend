<?php

/**
 * Description of botTelegram
 *
 * @author Sergey Ilyin <developer@ilyins.ru>
 */
class botTelegram {
    private static function exec($method, $params) {
        $url = 'https://api.telegram.org/bot' . TELEGRAM_BOT_TOKEN . '/' . $method;
        return cURL::executeRequest('POST', $url, false, false, $params);
    }

    public static function sendMessage($args) {
        if (TELEGRAM_BOT_TOKEN != '<Enter here>') {
            if (isset($args['chat_id']) && isset($args['text'])) {
                return self::exec(__FUNCTION__, $args);
            }
        }
    }
}
