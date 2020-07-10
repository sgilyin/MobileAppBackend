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

switch (filter_input(INPUT_SERVER, 'CONTENT_TYPE')) {
    case 'application/json':
        $requestHost = filter_input(INPUT_SERVER, 'REMOTE_ADDR');
        $requestID = filter_input(INPUT_SERVER, 'REQUEST_ID');
        $requestJson = json_decode(file_get_contents("php://input"));
        header("Content-type: application/json; charset=utf-8");
        echo BGBilling::getContractInformation($requestJson);
//        file_put_contents('request.log', date('c') . " | $requestID | $requestHost | " . filter_input(INPUT_SERVER, 'REQUEST_METHOD') . " | " . serialize($requestJson) . PHP_EOL, FILE_APPEND);
        break;

    default:
        echo 'Silent is golden!';
        break;
}
