<?php
include_once("standard_includes.php");

$editMode = false; // are we editing or creating?

if (isset($_REQUEST["id"])) {
    $activity = Activity::findById($_REQUEST["id"]);
    $editMode = true;
}

if (isset($_REQUEST["status"])) {
    if ($_REQUEST["status"] == "saved") {
        $msg = "Saved!";
    }
    if ($_REQUEST["status"] == "deleted") {
        $msg = "Deleted!";
    }
}

$actionTaken = "";
if (isset($_REQUEST["action_save"])) $actionTaken = "save";
if (isset($_REQUEST["action_delete"])) $actionTaken = "delete";

if ($actionTaken == "save" && isset($_REQUEST["action_save"]) && isset($_REQUEST["activityName"]) && isset($_REQUEST["itemList"])) {

    $activityName = $_REQUEST["activityName"];
    $items = explode("\n", $_REQUEST["itemList"]);
    $items = array_values(
        array_filter($items, function ($e) {
            return $e != '' && !ctype_space($e);
        })
    );

    if (isset($activity)) {
        $activity->name = $activityName;
        $activity->attributes = $items;
    } else {
        $activity = new Activity($activityName, $items);
    }
    
    try {
        $activity->save();
        header("Location: $basePath/activity/edit?status=saved&id=" . $activity->id);
        exit();
    } catch (Exception $e) {
        var_dump($e);
        exit();
    }
}

if ($actionTaken == "delete" && isset($_REQUEST['id'])) {
    $activity = Activity::findById($_REQUEST['id']);
    try {
        $activity->delete();
        header("Location: $basePath/activity/edit?status=deleted");
        exit();
    } catch (Exception $e) {
        var_dump($e);
        exit();
    }
}

include_once("html_head.php");
$submitSave = Dictionary::saveWord();
$submitDelete = Dictionary::deleteWord();
?>

<form method="post">

    <?php if (isset($msg)) { ?>
    <p class="page-msg">
        <?php echo $msg ?>
    </p>
    <?php } ?>

    <h1>
        <?php echo $editMode ? "Edit" : "Create" ?> an activity
        <?php if ($editMode) { ?>
            <a style="font-size:0.5em; float: right; margin-top: 15px;" href="/activity/edit">Create another</a>
        <?php } ?>
    </h1>
    <div class="form-group">
        <?php if ($activity): ?>
            <input class="form-control" id="activityName" name="activityName" value="<?php echo $activity->name;?>">
        <?php else: ?>
            <input class="form-control" id="activityName" name="activityName" placeholder="Walking your dog">
        <?php endif; ?>
    </div>
    <div class="form-group">
        <label for="itemList">Items</label>
        <textarea class="form-control" id="itemList" name="itemList" rows="10"><?php if ($activity) {
            // var_dump($activity);
            foreach ($activity->attributes as $attr) {
                echo "$attr\n";
            }
        } ?></textarea>
    </div>

    <input type="submit" class="btn btn-success pull-left" name="action_save" value="<?php echo $submitSave ?>">
    <input type="submit" class="btn btn-danger pull-right ft-confirm" name="action_delete" value="<?php echo $submitDelete ?>">
</form>

</body>
</html>