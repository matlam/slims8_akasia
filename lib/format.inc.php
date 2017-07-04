<?php

function slims_money_format($value) {
    $fmt = new NumberFormatter('de_DE', NumberFormatter::CURRENCY);
    return $fmt->formatCurrency($value, "EUR");
}

function slims_money_format_for_datagrid($obj_db, $row, $field_num) {
    return slims_money_format($row[$field_num]);
}
