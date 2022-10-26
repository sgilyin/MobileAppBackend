<?php

/**
 * Description of botVK
 *
 * @author Sergey Ilyin <developer@ilyins.ru>
 */
class botVK {
    private static function exec($method, $params) {
        $url = "https://api.vk.com/method/$method";
        $params['v'] = VK_API_VERSION;
        $params['access_token'] = VK_API_TOKEN;
        return cURL::executeRequest('POST', $url, false, false, $params);
    }

    public static function messagesSend($args) {
        if (VK_API_TOKEN != '<Enter here>') {
            if (isset($args['user_id']) && isset($args['message'])) {
                $args['random_id'] = time();
                return self::exec('messages.send', $args);
            }
        }
    }
}
