<?php
include_once("standard_includes.php");
include_once("html_head.php");

$editCreate = "Create";

if (isset($_REQUEST["id"])) {
    $activity = Activity::findById($_REQUEST["id"]);
    $editCreate = "Edit";
}

if (isset($_REQUEST["save"]) && isset($_REQUEST["activityName"]) && isset($_REQUEST["itemList"])) {
    $activityName = $_REQUEST["activityName"];
    $items = explode("\n", $_REQUEST["itemList"]);
    if (isset($activity)) {
        $activity->name = $activityName;
        $activity->attributes = $items;
    } else {
        $activity = new Activity($activityName, $items);
    }
    
    try {
        $activity->save();
    } catch (Exception $e) {
        var_dump($e);
    }
}

$submit = Dictionary::weirdWord();

?>

<form method="post">
    <h1><?php echo $editCreate ?> an activity</h1>
    <div class="form-group">
        <?php if ($activity): ?>
            <input class="form-control" id="activityName" name="activityName" value="<?php echo $activity->name;?>">
        <?php else: ?>
            <input class="form-control" id="activityName" name="activityName" placeholder="Walking your dog">
        <?php endif; ?>
    </div>
    <div class="form-group">
        <label for="itemList">Items</label>
        <textarea class="form-control" id="itemList" name="itemList" rows="10">
<?php if ($activity) {
            // var_dump($activity);
            foreach ($activity->attributes as $attr) {
                echo "$attr\n";
            }
} ?>
        </textarea>
    </div>
    <input type="hidden" name="action" value="save">
    <button type="submit" class="btn btn-success"><?php echo $submit ?></button>
</form>


</body>
</html>