<?php

namespace FiveThings;

class Activity {

    public $id;
    public $name;
    public $items;
    public $isSimple;

    public function __construct(array $data) {
        // There will not be an id if we are creating a new activity.
        $this->id = $data['Id'] ?? null;
        $this->name = $data['Name'] ?? null;
        $this->isSimple = $data['IsSimple'] ?? false;
        $this->items = array(); // these are loaded separately
        // var_dump($data); exit();
    }

    public function randomActivityItems($howMany) {
        shuffle($this->items);
        return array_slice($this->items, 0, $howMany);
    }
}