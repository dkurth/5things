<?php
include_once("standard_includes.php");
include_once("html_head.php");
$replCount = 3;
$activity = Activity::getRandomActivity();
$replacements = ReplacementItem::getReplacements($replCount);
$attrs = $activity->getAttributes($replCount);
?>

<p>You are <span class='activity'><?php echo $activity->name; ?></span>.</p>

<?php
foreach ($activity->getAttributes($replCount) as $attr) {
    echo "<p><span class='attr'>" . ucfirst($attr) . "</span> " . Activity::isAre($attr) . " <span class='repl'>" . Activity::aAn($attr) . " " . $replacements[--$replCount] . ".</span></p>";
}
?>

</body>
</html>