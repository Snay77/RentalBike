<?php
function getDB()
{
    $db_host = 'localhost';
    $db_name = 'rentalbike';
    $db_user = 'root';
    $db_pass = '';

    try {
        $db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        $db->exec("set names utf8");
        return $db;
    } catch (PDOException $e) {
        print("Erreur : " . $e->getMessage() . '<br/>');
    }
}
