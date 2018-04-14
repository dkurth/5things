<?php

class Activity {
    public $name;
    public $attributes;

    public function __construct($act, $attrs = array()) {
        $this->name = $act;
        $this->attributes = $attrs;
        $this->id = 0; // until it's written to the database
        $this->dbh = getDbConnection();
    }

    public function getAttributes($howMany) {
        shuffle($this->attributes);
        return array_slice($this->attributes, 0, $howMany);
    }

    public static function isAre($str) {
        return substr($str, -1) == "s" ? "are" : "is";
    }

    public static function aAn($str) {
        $aAn = preg_match("/^[aeiou]/", $str) ? ' an ' : ' a ';
        if (preg_match("/^[A-Z]/", $str)) {
            $aAn = ' '; // proper nouns
        }
        return $aAn;
    }

    public static function getAll() {
        $dbh = getDbConnection();
        $activities = array();
        $stmt = $dbh->prepare("SELECT Id, Name FROM Activity ORDER BY Name ASC");
        $stmt->execute();
        while($row = $stmt->fetch()) {
            $act = new Activity($row['Name']);
            $act->id = $row['Id'];
            $activities[] = $act;
        }
        return $activities;
    }

    public static function getRandomActivity() {
        $dbh = getDbConnection();
        $stmt = $dbh->prepare("SELECT Id, Name FROM Activity a ORDER BY RANDOM() LIMIT 1");
        $stmt->execute();
        $row = $stmt->fetch();
        $activityId = $row[0];
        $activityName = $row[1]; // todo - figure out how to access this by name

        $stmt = $dbh->prepare("SELECT * FROM ActivityItem WHERE ActivityId = :id");
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

    public static function findById($id) {
        $dbh = getDbConnection();
        $stmt = $dbh->prepare("SELECT Name FROM Activity WHERE Id = :id");
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
        $stmt = $this->dbh->prepare("SELECT Name FROM ActivityItem WHERE ActivityId = :id");
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        $attrs = array();
        if ($stmt->execute()) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $attrs[] = $row['Name'];
            }
        }
        $this->attributes = $attrs;
    }

    public function delete() {
        if (!$this->id) return;
        $this->dbh->beginTransaction();
        try {
            $stmt = $this->dbh->prepare("DELETE FROM Activity WHERE Id = :id");
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            $stmt->execute();

            $stmt = $this->dbh->prepare("DELETE FROM ActivityItem WHERE ActivityId = :id");
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            $stmt->execute();

            $this->dbh->commit();

        } catch (Exception $e) {
            echo $e->getMessage();
            $this->dbh->rollBack();
            exit();
        }
    }

    public function save() {
        if ($this->id > 0) {
            // this is an existing activity, so update it here

            $this->dbh->beginTransaction();

            try {

                $stmt = $this->dbh->prepare("UPDATE Activity SET Name = :name WHERE Id = :id");
                $stmt->bindParam(':name', $this->name);
                $stmt->bindParam(':id', $this->id);
                $stmt->execute();

                $stmt = $this->dbh->prepare("DELETE FROM ActivityItem WHERE ActivityId = :id");
                $stmt->bindParam(":id", $this->id);
                $stmt->execute();

                $type = "thing"; // todo

                foreach ($this->attributes as $attr) {
                    $stmt = $this->dbh->prepare("INSERT INTO ActivityItem (ActivityId, Type, Name) Values (:id, :type, :name)");
                    $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
                    $stmt->bindParam(':type', $type, PDO::PARAM_STR);
                    $stmt->bindParam(':name', $attr, PDO::PARAM_STR);
                    $stmt->execute();
                }

                $this->dbh->commit();

            } catch (Exception $e) {
                echo $e->getMessage();
                $this->dbh->rollBack();
                exit();
            }

        } else {
            // this is a new activity, so create it
            $stmt = $this->dbh->prepare("INSERT INTO Activity (Name) Values(:name)");
            $stmt->bindParam(':name', $this->name);
            $result = $stmt->execute();

            $id = $this->dbh->lastInsertId();
            $this->id = $id;

            $type = "thing"; // todo

            foreach ($this->attributes as $attr) {
                $stmt = $this->dbh->prepare("INSERT INTO ActivityItem (ActivityId, Type, Name) Values (:id, :type, :name)");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->bindParam(':type', $type, PDO::PARAM_STR);
                $stmt->bindParam(':name', $attr, PDO::PARAM_STR);
                $stmt->execute();
            }
        }
    }
}