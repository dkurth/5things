<?php

class ReplacementItem {
    public $id;
    public $type;
    public $name;

    public function __construct($name, $type = "thing") {
        $this->name = $name;
        $this->type = $type;
        $this->id = 0; // until it's written to the database
    }

    static function getReplacements($howMany, $type = 'thing') {
        $dbh = getDbConnection();
        $stmt = $dbh->prepare("SELECT Name FROM ReplacementItem WHERE Type = :type ORDER BY RANDOM() LIMIT :howmany");
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
}