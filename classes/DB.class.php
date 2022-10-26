<?php

/**
 * Description of DB
 *
 * @author Sergey Ilyin <developer@ilyins.ru>
 */
class DB {
    public static function query($query){
        if (DB_HOST != '<Enter here>' && DB_USER != '<Enter here>' &&
            DB_PASSWORD != '<Enter here>' && DB_NAME != '<Enter here>') {
            $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
            if ($mysqli->connect_errno) {
                file_put_contents('debug.log', $mysqli->connect_error, FILE_APPEND);
                exit();
            }
            $mysqli->set_charset('utf8');
            $result = $mysqli->query($query);
            if ($mysqli->errno) {
                file_put_contents('debug.log', $mysqli->errno . $mysqli->error, FILE_APPEND);
            }
            $mysqli->close();
            return $result;
        }
    }
}
