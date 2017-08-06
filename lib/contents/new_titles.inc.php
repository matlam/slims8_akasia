<?php

/**
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA
 *
 */
// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
    die("can not access this file directly");
}

/* Showing list of catalogues of the last 6 months */

// include required class class
require SIMBIO . 'simbio_UTILS/simbio_tokenizecql.inc.php';
require SIMBIO . 'simbio_GUI/paging/simbio_paging.inc.php';
require LIB . 'biblio_list_model.inc.php';
require LIB . 'biblio_list.inc.php';

class biblio_list_new_titles extends biblio_list {

    public function __construct($obj_db, $int_num_show) {
        parent::__construct($obj_db, $int_num_show);
        $this->criteria = array('sql_criteria' => 'biblio.input_date > "'.date('Y-m-d H:i:s', strtotime('-6 months')).'" AND publish_year >= (YEAR(CURDATE()) - 2)', 'searched_fields' => array());
    }

    public function compileSQL() {
        $sql = parent::compileSQL();
        return str_replace('ORDER BY biblio.input_date', 'ORDER BY biblio.last_update', $sql);
    }

}

// create biblio list object
try {
    $biblio_list = new biblio_list_new_titles($dbs, $sysconf['opac_result_num']);
} catch (Exception $err) {
    die($err->getMessage());
}
if (isset($sysconf['enable_xml_detail']) && !$sysconf['enable_xml_detail']) {
    $biblio_list->xml_detail = false;
}
if (isset($sysconf['enable_mark']) && !$sysconf['enable_mark']) {
    $biblio_list->enable_mark = false;
}
// search result info
$search_result_info = '';

// search result info construction
$keywords_info = '<span class="search-keyword-info" title="Neuaufnahmen der letzten 6 Monate">Neuaufnahmen der letzten 6 Monate</span>';
$search_result_info .= '<div class="search-found-info">';
$search_result_info .= __('Found <strong>{biblio_list->num_rows}</strong> from your keywords') . ': <strong class="search-found-info-keywords">' . $keywords_info . '</strong>';
$search_result_info .= '</div>';

echo $biblio_list->getDocumentList();
echo '<br />' . "\n";

// set result number info
$search_result_info = str_replace('{biblio_list->num_rows}', $biblio_list->num_rows, $search_result_info);

// count total pages
$total_pages = ceil($biblio_list->num_rows / $sysconf['opac_result_num']);

// page number info
if (isset($_GET['page']) AND $_GET['page'] > 1) {
    $page = intval($_GET['page']);
    $msg = str_replace('{page}', $page, __('You currently on page <strong>{page}</strong> of <strong>{total_pages}</strong> page(s)')); //mfc
    $msg = str_replace('{total_pages}', $total_pages, $msg);
    $search_result_info .= '<div class="search-page-info">' . $msg . '</div>';
} else {
    $page = 1;
}
  