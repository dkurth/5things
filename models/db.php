<?php

function getDbConnection() {
    $dir = 'sqlite:5things.db3';
    $dbh  = new PDO($dir) or die("cannot open the database");
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    return $dbh;
}