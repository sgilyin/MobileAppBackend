<?php

/**
 * Description of Notification
 *
 * @author Sergey Ilyin <developer@ilyins.ru>
 */
class Notification {
    public function cidSend($args) {
        # Usage:
        # https://URL/?class[method]=Notification::cidSend&args[cid]=<cid>&args[msg]=<msg>
        # args[cid]=all for global push
        # args[cid]=<cid> for personal push
        # args[cid]=<cid>,<cid> for several push
        $query = "SELECT address, messenger, uid FROM msngr_sbscrbrs";
        if ($args['cid'] != 'all') {
            $query .= " WHERE cid IN ({$args['cid']})";
        }
        $sqlResult = DB::query($query);
        while ($row = $sqlResult->fetch_object()) {
            switch ($row->messenger) {
                case 'telegrambot':
                    $tlgrm['chat_id'] = $row->uid;
                    $tlgrm['text'] = 'Чат-бот:' . PHP_EOL . $row->address . ': ' . $args['msg'];
                    botTelegram::sendMessage($tlgrm);
                    break;
                case 'vkgroup':
                    $vk['user_id'] = $row->uid;
                    $vk['message'] = 'Чат-бот:' . PHP_EOL . $row->address . ': ' . $args['msg'];
                    botVK::messagesSend($vk);
                    break;

                default:
                    break;
            }
        }
    }
}
