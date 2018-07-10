<?php

namespace FiveThings;

class ReplacementItem {
    public $id;
    public $name;
    public $type;
    public $article;

    public function __construct(array $data) {
        $this->id = $data['Id'] ?? null;
        $this->type = $data['Type'] ?? 'thing';
        $this->name = $data['Name'] ?? null;
        $this->article = $data['Article'] ?? '';
    }
}