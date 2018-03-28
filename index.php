<html>
<head>
    <title>Five Things Generator</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <style>
        body {
            width: 70%;
            margin: auto;
            text-align: left;
            font-size: 3em;
            background-color: #FFDC00;
        }
        .activity, .attr, .repl {
            font-weight: bold;
        }
    </style>
<?php

class Activity {
    public $name;
    public $attributes;
    public function __construct($act, $attrs) {
        $this->name = $act;
        $this->attributes = $attrs;
    }
    public function describeHtml($repls) {
        $msg = array();
        $msg[] = "<p>You are <span class='activity'>" . $this->name . "</span>.</p>";
        $attrMsgs = array();
        shuffle($this->attributes);
        $chosenAttrs = array_slice($this->attributes, 0, count($repls));
        $i = 0;
        foreach ($chosenAttrs as $attr) {
            $isAre = substr($attr, -1) == "s" ? "are" : "is";
            $repl = $repls[$i];
            $aAn = preg_match("/^[aeiou]/", $repl) ? ' an ' : ' a ';
            if (preg_match("/^[A-Z]/", $repl)) {
                $aAn = ' '; // proper nouns
            }
            $msg[] = "<p><span class='attr'>" . ucfirst($attr) . "</span> $isAre <span class='repl'>$aAn $repl.</span></p>";
            $i++;
        }
        return implode("\n", $msg);
    }
}

function getDbConnection() {
    $dir = 'sqlite:5things.db3';
    $dbh  = new PDO($dir) or die("cannot open the database");
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    return $dbh;
}

function getActivity() {
    $dbh = getDbConnection();

    $stmt = $dbh->prepare("SELECT * FROM Activity a ORDER BY RANDOM() LIMIT 1");
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

function getReplacements($howMany, $type = 'thing') {
    $dbh = getDbConnection();
    $stmt = $dbh->prepare("SELECT * FROM ReplacementItem WHERE Type = :type ORDER BY RANDOM() LIMIT :howmany");
    $stmt->bindParam(':type', $type);
    $stmt->bindParam(':howmany', $howMany);
    $result = $stmt->execute();
    $replacementItems = array();

    if ($stmt->execute()) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $replacementItems[] = $row['Name'];
        }
    }
    return $replacementItems;
}

$activity = getActivity();
$replacements = getReplacements(3);
print $activity->describeHtml($replacements, 3);


