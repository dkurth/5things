<?php

namespace FiveThings;

use \PDO;

// CRUD methods for Activity go here

class ActivityLoader extends Loader {

    public function getReplacementItems($howMany, $type = 'thing') {
        $stmt = $this->db->prepare("SELECT Id, Name, Type, Article FROM ReplacementItem WHERE Type = :type ORDER BY RANDOM() LIMIT :howmany");
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':howmany', $howMany);
        $result = $stmt->execute();
        $replacementItems = array();

        if ($stmt->execute()) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $replacementItems[] = new ReplacementItem($row);
            }
        }
        return $replacementItems;
    }

    public function getSimpleReplacementItems($howMany, $type = 'thing') {
        $stmt = $this->db->prepare("SELECT Id, Name, Type, Article FROM ReplacementItem WHERE Type = :type AND IsSimple ORDER BY RANDOM() LIMIT :howmany");
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':howmany', $howMany);
        $result = $stmt->execute();
        $replacementItems = array();

        if ($stmt->execute()) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $replacementItems[] = new ReplacementItem($row);
            }
        }
        return $replacementItems;
    }

    public function delete($activity) {
        if (!$activity->id) return;
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("DELETE FROM Activity WHERE Id = :id");
            $stmt->bindParam(':id', $activity->id, PDO::PARAM_INT);
            $stmt->execute();

            $stmt = $this->db->prepare("DELETE FROM ActivityItem WHERE ActivityId = :id");
            $stmt->bindParam(':id', $activity->id, PDO::PARAM_INT);
            $stmt->execute();

            $this->db->commit();

        } catch (Exception $e) {
            echo $e->getMessage();
            $this->db->rollBack();
            exit();
        }
    }

    public function save($id, $postBody, $action) {

        $activity = false;
        if ($id != "edit") {
            $activity = $this->findById($id, true);
            if ($action == "delete") {
                $this->delete($activity);
                return;
            }
        }

        if ($action != "save") {
            // this should not happen
            return;
        }

        $activityName = $postBody["activityName"];
        $itemNames = explode("\n", $postBody["itemNames"]);
        $itemNames = array_values(
            array_filter($itemNames, function ($e) {
                return $e != '' && !ctype_space($e); // filter out blank items
            })
        );

        $items = array();
        foreach ($itemNames as $name) {
            $items[] = new ActivityItem(array(
                'Name' => $name,
                //'Type' => 'thing', // todo
            ));
        }

        if (isset($postBody['isSimple'])) {
          $isSimple = true;
        } else {
          $isSimple = false;
        }

        // var_dump($postBody);
        // print("isSimple = $isSimple");
        // exit();

        if ($activity) {
            $activity->name = $activityName;
            $activity->isSimple = $isSimple;
        } else {
            $activity = new Activity(array(
                'Name' => $activityName,
                'IsSimple' => $isSimple,
            ));
        }
        $activity->items = $items;

        if ($activity->id > 0) {
            // this is an existing activity, so UPDATE it here

            $this->db->beginTransaction();

            try {

                $stmt = $this->db->prepare("UPDATE Activity SET Name = :name, IsSimple = :isSimple WHERE Id = :id");
                $stmt->bindParam(':name', $activity->name);
                $stmt->bindParam(":isSimple", $activity->isSimple);
                $stmt->bindParam(':id', $activity->id);
                $stmt->execute();

                $stmt = $this->db->prepare("DELETE FROM ActivityItem WHERE ActivityId = :id");
                $stmt->bindParam(":id", $activity->id);
                $stmt->execute();

                foreach ($activity->items as $item) {

                    $stmt = $this->db->prepare("INSERT INTO ActivityItem (ActivityId, Type, Name) Values (:id, :type, :name)");
                    $stmt->bindParam(':id', $activity->id, PDO::PARAM_INT);
                    $stmt->bindParam(':type', $item->type, PDO::PARAM_STR);
                    $stmt->bindParam(':name', $item->name, PDO::PARAM_STR);
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
            $stmt->bindParam(':name', $activity->name);
            $result = $stmt->execute();

            $activity->id = $this->db->lastInsertId();

            foreach ($activity->items as $item) {
                $stmt = $this->db->prepare("INSERT INTO ActivityItem (ActivityId, Type, Name) Values (:activityId, :type, :name)");
                $stmt->bindParam(':activityId', $activity->id, PDO::PARAM_INT);
                $stmt->bindParam(':type', $item->type, PDO::PARAM_STR);
                $stmt->bindParam(':name', $item->name, PDO::PARAM_STR);
                $stmt->execute();
            }
        }
        return $activity->id;
    }

    // Load the ActivityItem records
    public function loadItems($activity) {
        if ($activity->id == 0) return array();
        // todo - load the type also.
        $stmt = $this->db->prepare("SELECT Id, Name FROM ActivityItem WHERE ActivityId = :id");
        $stmt->bindParam(':id', $activity->id, PDO::PARAM_INT);
        $attrs = array();
        if ($stmt->execute()) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $activityItem = new ActivityItem($row);
                $activity->items[] = $activityItem;
            }
        }
    }

    public function checkDuplicate($name) {
        // Find and return Activity records that match the words from $name.

        $maxDupes = 20; // return up to this many results

        $words = preg_split('/\s+/', $name);
        $q = "SELECT Id, Name FROM Activity WHERE ";
        $wordCount = count($words);
        $likes = array();
        for ($i = 0; $i < $wordCount; $i++) {
            $likes[] = "Name LIKE ?";
            $words[$i] = "%" . $words[$i] . "%";
        }
        $q = $q . implode($likes, " AND ") . " LIMIT " . $maxDupes;

        $stmt = $this->db->prepare($q);
        $stmt->execute($words);
        $matches = array();
        while ($results = $stmt->fetch()) {
            $matches[] = ActivityLoader::findById($results["Id"]);
        }
        return $matches;
    }

    public function getAll($includeItems = false) {
        $activities = array();
        $stmt = $this->db->prepare("SELECT Id, Name FROM Activity ORDER BY Name COLLATE NOCASE ASC");
        $stmt->execute();
        while($row = $stmt->fetch()) {
            $activity = new Activity($row);
            if ($includeItems) {
                $this->loadItems($activity);
            }
            $activities[] = $activity;
        }
        return $activities;
    }

    public function getRandomSimpleActivity($includeItems = false) {
        $stmt = $this->db->prepare("SELECT Id, Name FROM Activity a WHERE a.IsSimple ORDER BY RANDOM() LIMIT 1");
        $stmt->execute();
        $row = $stmt->fetch();
        $activity = new Activity($row);
        if ($includeItems) {
            $this->loadItems($activity);
        }
        return $activity;
    }

    public function getRandomActivity($includeItems = false) {
        $stmt = $this->db->prepare("SELECT Id, Name FROM Activity a ORDER BY RANDOM() LIMIT 1");
        $stmt->execute();
        $row = $stmt->fetch();
        $activity = new Activity($row);
        if ($includeItems) {
            $this->loadItems($activity);
        }
        return $activity;
    }

    public function findById($id, $includeItems = false) {
        $stmt = $this->db->prepare("SELECT Id, Name, IsSimple FROM Activity WHERE Id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $activity = new Activity($row);
                if ($includeItems) {
                    $this->loadItems($activity);
                }
                return $activity;
            }
        } else {
            print_r($stmt->errorInfo());
        }
    }

    public function randomButtonLabel($btnType) {
        // returns a random word for the buttons
        if ($btnType == "save") {
            $labels = array(
                "save",
                "preserve",
                "keep",
                "perpetuate",
                "retain",
                "safeguard",
                "store",
                "refrigerate",
            );
        } elseif ($btnType == "delete") {
            $labels = array(
                "delete",
                "destroy",
                "expunge",
                "wipe out",
                "eliminate",
                "bleep",
                "obliterate",
                "squash",
            );
        } else {
            $labels = array(
                  "rural",
                  "frugal",
                  "twin",
                  "santorum",
                  "detritus",
                  "entrepreneurial",
                  "pronounciation",
                  "moisture",
                  "peculiarly",
                  "crepuscular",
                  "ubiquitous",
                  "malarkey",
                  "bubbles",
                  "dude",
                  "torque",
                  "google",
                  "visualization",
                  "cliche",
                  "floccinaucinihilipilification",
                  "conundrums",
                  "doodlesack",
                  "volunteer",
                  "mississippilessly",
                  "calisthenics",
                  "serendipity",
                  "grommets",
                  "dilettante",
                  "macrosmatic",
                  "supercalifragilisticexpialidocious",
                  "flabergasted",
                  "sphygmomanometer",
                  "Kardashian",
                  "hubbub",
                  "prescription",
                  "caulking",
                  "wreath",
                  "expat",
                  "jentacular",
                  "schnapps",
                  "zdravstvuite",
                  "phlegm",
                  "specific",
                  "quproch",
                  "riboflavin",
                  "cubersome",
                  "garage",
                  "flummoxed",
                  "profiterole",
                  "ruler",
                  "dingleberry",
                  "egg",
                  "ombudsman",
                  "perpendicular",
                  "collate",
                  "abruptly",
                  "word",
                  "bitter",
                  "cellar",
                  "airworthy",
                  "stupendous",
            );
        }

        shuffle($labels);
        return array_slice($labels, 0, 1)[0];
    }
}
