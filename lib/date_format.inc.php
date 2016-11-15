<?php
/**
 * date_format
 * Class for generating a list of records
 *
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
    die("can not access this file directly");
}

/**
 * converts a date from Y-m-d to $sysconf['date_format']
 * 
 * @param string $date datestring in a format that can be parsed by strtotime()
 * 
 * @return datestring formated according to $sysconf['date_format']
 */
function slims_date_format($date) {
    global $sysconf;
    return date($sysconf['date_format'], strtotime($date));
}

/**
 * converts a date and time from Y-m-d H:i:s to $sysconf['datetime_format']
 * 
 * @param string $datetime date-time-string in a format that can be parsed by strtotime()
 * 
 * @return date-time-string formated according to $sysconf['datetime_format']
 */
function slims_datetime_format($datetime) {
    global $sysconf;
    return date($sysconf['datetime_format'], strtotime($datetime));
}

/**
 * callback function to format a date field for the simbio_datagrid
 * if the field isn't in the format Y-m-d its value is returned unchanged
 * 
 * use it with simbio_datagrid::modifyColumnContent()
 * 
 * @return string 
 */
function slims_date_format_for_datagrid($obj_db, $row, $field_num) {
    global $sysconf;
    $date = DateTime::createFromFormat('Y-m-d', $row[$field_num]);

    if($date) {
        return $date->format($sysconf['date_format']);
    } else {
        return $row[$field_num];
    }
}

/**
 * callback function to format a datetime field for the simbio_datagrid
 * if the field isn't in the format Y-m-d H:i:s its value is returned unchanged
 * 
 * use it with simbio_datagrid::modifyColumnContent()
 * 
 * @return string 
 */
function slims_datetime_format_for_datagrid($obj_db, $row, $field_num) {
    global $sysconf;
    $date = DateTime::createFromFormat('Y-m-d H:i:s', $row[$field_num]);

    if($date) {
        return $date->format($sysconf['datetime_format']);
    } else {
        return $row[$field_num];
    }
}

/**
 * converts a date from $sysconf['date_format'] to Y-m-d
 * 
 * @param string $date datestring in the format $sysconf['date_format']
 * 
 * @return bool|string date in the format Y-m-d or false if $date is not in the format $sysconf['date_format']
 */
function slims_parse_formated_date($date) {
    global $sysconf;
    $dateTime = DateTime::createFromFormat($sysconf['date_format'], $date);
    if($dateTime) {
        return $dateTime->format('Y-m-d');
    } else {
        return false;
    }
}