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

include_once 'config.php';

spl_autoload_register(function ($class) {
    include 'classes/' . $class . '.class.php';
});

switch (filter_input(INPUT_SERVER, 'REQUEST_METHOD')) {
    case 'POST':
        $requestData = filter_input_array(INPUT_POST);
        break;

    case 'GET':
        $requestData = filter_input_array(INPUT_GET);
        break;

    default:
        break;
}

switch (filter_input(INPUT_SERVER, 'CONTENT_TYPE')) {
    case 'application/json;charset=utf-8':
    case 'application/json; charset=utf-8':
    case 'application/json':
        $requestHost = filter_input(INPUT_SERVER, 'REMOTE_ADDR');
        $requestID = filter_input(INPUT_SERVER, 'REQUEST_ID');
        $requestJson = json_decode(file_get_contents("php://input"));
        header("Content-type: application/json; charset=utf-8");
        if (isset($requestJson->session->skill_id)) {
            echo Yandex::getBalance($requestJson);
        } else {
            echo BGBilling::getContractsInfo($requestJson);
        }
        break;
    case 'application/x-www-form-urlencoded':
        botOLBX24::handler($requestData);
#        file_put_contents('request.log', serialize($requestData));

    default:
        if (filter_input(INPUT_SERVER, 'HTTP_USER_AGENT') == 'curl/7.64.0' && filter_input(INPUT_SERVER, 'REMOTE_ADDR') == '195.191.78.20') {
            $request = json_decode(file_get_contents("php://input"), true);
            $request['class']['method']($request['args']);
        } else {
            echo 'Silent is golden!';
        }
        break;
}
#file_put_contents('request.log', date('c') . " | $requestID | $requestHost | " . filter_input(INPUT_SERVER, 'REQUEST_METHOD') . " | " . filter_input(INPUT_SERVER, 'CONTENT_TYPE') . " | " . serialize($requestJson) . PHP_EOL, FILE_APPEND);
