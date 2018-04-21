<?php

namespace FiveThings;

class Activity {
    public $name;
    public $attributes;

    public function __construct($act, $attrs = array()) {
        $this->name = $act;
        $this->attributes = $attrs;
        $this->id = 0; // until it's written to the database
    }

    public function getAttributes($howMany) {
        shuffle($this->attributes);
        return array_slice($this->attributes, 0, $howMany);
    }

    public function isAre($str) {
        return substr($str, -1) == "s" ? "are" : "is";
    }

    public function aAn($str) {
        $aAn = preg_match("/^[aeiou]/", $str) ? ' an ' : ' a ';
        if (preg_match("/^[A-Z]/", $str)) {
            $aAn = ' '; // proper nouns
        }
        return $aAn;
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

    public function delete() {
        if (!$this->id) return;
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("DELETE FROM Activity WHERE Id = :id");
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            $stmt->execute();

            $stmt = $this->db->prepare("DELETE FROM ActivityItem WHERE ActivityId = :id");
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            $stmt->execute();

            $this->db->commit();

        } catch (Exception $e) {
            echo $e->getMessage();
            $this->db->rollBack();
            exit();
        }
    }

    public function save() {
        if ($this->id > 0) {
            // this is an existing activity, so update it here

            $this->db->beginTransaction();

            try {

                $stmt = $this->db->prepare("UPDATE Activity SET Name = :name WHERE Id = :id");
                $stmt->bindParam(':name', $this->name);
                $stmt->bindParam(':id', $this->id);
                $stmt->execute();

                $stmt = $this->db->prepare("DELETE FROM ActivityItem WHERE ActivityId = :id");
                $stmt->bindParam(":id", $this->id);
                $stmt->execute();

                $type = "thing"; // todo

                foreach ($this->attributes as $attr) {
                    $stmt = $this->db->prepare("INSERT INTO ActivityItem (ActivityId, Type, Name) Values (:id, :type, :name)");
                    $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
                    $stmt->bindParam(':type', $type, PDO::PARAM_STR);
                    $stmt->bindParam(':name', $attr, PDO::PARAM_STR);
                    $stmt->execute();
                }

                $this->db->commit();

            } catch (Exception $e) {
                echo $e->getMessage();
                $this->db->rollBack();
                exit();
            }

        } else {
            // this is a new activity, so create it
            $stmt = $this->db->prepare("INSERT INTO Activity (Name) Values(:name)");
            $stmt->bindParam(':name', $this->name);
            $result = $stmt->execute();

            $id = $this->db->lastInsertId();
            $this->id = $id;

            $type = "thing"; // todo

            foreach ($this->attributes as $attr) {
                $stmt = $this->db->prepare("INSERT INTO ActivityItem (ActivityId, Type, Name) Values (:id, :type, :name)");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->bindParam(':type', $type, PDO::PARAM_STR);
                $stmt->bindParam(':name', $attr, PDO::PARAM_STR);
                $stmt->execute();
            }
        }
    }
}