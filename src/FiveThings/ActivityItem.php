<?php

namespace FiveThings;

class ActivityItem {
    public $id;
    public $activityId;
    public $name;
    public $type;

    public function __construct(array $data) {
        $this->id = $data['Id'] ?? null;
        $this->activityId = $data['ActivityId'] ?? null;
        $this->type = $data['Type'] ?? "thing";
        $this->name = $data['Name'] ?? null;
    }
}