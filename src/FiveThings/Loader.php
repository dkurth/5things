<?php

namespace FiveThings;

abstract class Loader {
    protected $db;
    public function __construct($db) {
        $this->db = $db;
    }
}