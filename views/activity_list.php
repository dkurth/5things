<?php
include_once("standard_includes.php");

$activities = Activity::getAll();

include_once("html_head.php");
?>

<h1>
    Activities 
    <a style="font-size:0.5em; float: right; margin-top: 15px;" href="/activity/edit">Create new</a>
</h1>

<input type="text" class="ft-filter" data-ft-filter-table="activity-list" placeholder="filter...">

<table class="table table-condensed" id="activity-list">
    <?php foreach ($activities as $activity): ?>
        <tr>
            <td class="ft-filterable"><a href="edit?id=<?php echo $activity->id; ?>"><?php echo $activity->name; ?></a></td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>