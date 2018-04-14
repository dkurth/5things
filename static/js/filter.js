// Filter which rows in a table are shown as the user types in an input.
//     <input class="ft-filter" data-ft-filter-table="some-table-id">
//     ...
//     <table id="some-table-id">...<td class="ft-filterable">value to filter</td>

$(document).ready(function() {
    $("input.ft-filter").on("keyup", function(evt) {
        var $input = $(evt.target),
            query = $input.val(),
            tableId = $input.attr("data-ft-filter-table"),
            $table = $("#" + tableId),
            $fields = $table.find(".ft-filterable");
        for (var i=0; i<$fields.length; i++) {
            var $field = $($fields[i]);
            if ($field.text().toLowerCase().indexOf(query) === -1) {
                $field.closest("tr").hide();
            } else {
                $field.closest("tr").show();
            }
        }
    });
});