<?php

namespace FiveThings;

class ActivityLoader {

    public $db;

    public function __construct($container) {
        $this->db = $container->db;
        var_dump($this->db);
    }

    public function randomAttributes($activity, $howMany) {
        $attrs = $activity->attributes;
        shuffle($attrs);
        if ($howMany > sizeof($attrs)) {
            $this->logger->info("randomAttributes: $howMany attributes requested, but only " . sizeof($attrs) . " available.");
            $howMany = sizeof($attrs);
        }
        $howMany = min($howMany, sizeof($attrs));
        return array_slice($attrs, 0, $howMany);
    }

    public static function getAll() {
        $activities = array();
        $stmt = $this->db->prepare("SELECT Id, Name FROM Activity ORDER BY Name ASC");
        $stmt->execute();
        while($row = $stmt->fetch()) {
            $act = new Activity($row['Name']);
            $act->id = $row['Id'];
            $activities[] = $act;
        }
        return $activities;
    }

    public static function getRandomActivity() {
        $stmt = $this->db->prepare("SELECT Id, Name FROM Activity a ORDER BY RANDOM() LIMIT 1");
        $stmt->execute();
        $row = $stmt->fetch();
        $activityId = $row[0];
        $activityName = $row[1]; // todo - figure out how to access this by name

        $stmt = $this->db->prepare("SELECT * FROM ActivityItem WHERE ActivityId = :id");
        $stmt->bindParam(':id', $activityId);
        $result = $stmt->execute();
        $activityItems = array();

        if ($stmt->execute()) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $activityItems[] = $row['Name'];
            }
        }

        $activity = new Activity($activityName, $activityItems);
        return $activity;
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT Name FROM Activity WHERE Id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $activity = new Activity($row["Name"]);
                $activity->id = $id;
                $activity->loadAttrs();
                return $activity;                
            }
        }
        return new Activity(0, "");
    }

    public function loadAttrs() {
        if ($this->id == 0) return array();
        // todo - load the type also.  ActivityItem needs a class.
        $stmt = $this->db->prepare("SELECT Name FROM ActivityItem WHERE ActivityId = :id");
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        $attrs = array();
        if ($stmt->execute()) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $attrs[] = $row['Name'];
            }
        }
        $this->attributes = $attrs;
    }
}