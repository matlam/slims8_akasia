<?php
/**
 *
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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

/* Visitor Report */

// key to authenticate
define('INDEX_AUTH', '1');

// main system configuration
require '../../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-reporting');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
// privileges checking
$can_read = utility::havePrivilege('reporting', 'r');
$can_write = utility::havePrivilege('reporting', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
}

require SIMBIO.'simbio_GUI/form_maker/simbio_form_element.inc.php';

$page_title = 'Library Visitor Report';
$reportView = false;
if (isset($_GET['reportView'])) {
    $reportView = true;
}

if (!$reportView) {
?>
    <!-- filter -->
    <fieldset>
    <div class="per_title">
        <h2>SpieleMA Jahresbericht</h2>
    </div>
    <div class="infoBox">
    <?php echo __('Report Filter'); ?>
    </div>
    <div class="sub_section">
    <form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>" target="reportView">
    <div id="filterForm">
        <div class="divRow">
            <div class="divRowLabel"><?php echo __('Year'); ?></div>
            <div class="divRowContent">
            <?php
            $current_year = date('Y');
            $year_options = array();
            for ($y = $current_year; $y > 1999; $y--) {
                $year_options[] = array($y, $y);
            }
            echo simbio_form_element::selectList('year', $year_options, $current_year);
            ?>
            </div>
        </div>
    </div>
    <div style="padding-top: 10px; clear: both;">
    <input type="submit" name="applyFilter" value="<?php echo __('Apply Filter'); ?>" />
    <input type="hidden" name="reportView" value="true" />
    </div>
    </form>
    </div>
    </fieldset>
    <!-- filter end -->
    <iframe name="reportView" id="reportView" src="<?php echo $_SERVER['PHP_SELF'].'?reportView=true'; ?>" frameborder="0" style="width: 100%; height: 500px;"></iframe>
<?php
} else {
    ob_start();
        // year
    $selected_year = date('Y');
    if (isset($_GET['year']) AND !empty($_GET['year'])) {
        $selected_year = (integer)$_GET['year'];
    }
    $output = ' ';
    $loan_report = array();

    $report_q = $dbs->query('SELECT COUNT(loan_id) FROM loan WHERE YEAR(loan_date) = ' . $selected_year);
    $report_d = $report_q->fetch_row();
    $loan_report['Anzahl der enliehenen Spiele'] = $report_d[0];
    // total number of loan transaction
    $report_q = $dbs->query('SELECT COUNT(loan_id)
        FROM loan
        WHERE YEAR(loan_date) = ' . $selected_year . '
        GROUP BY member_id, loan_date
        ORDER BY `COUNT(loan_id)` DESC');
    $report_d = $report_q->num_rows;
    $loan_report['Ausleihvorgänge'] = $report_d;
    $peak_transaction_data = $report_q->fetch_row();

    // transaction average per day
    // only count tuesdays and wednesday for SpieleMA
    $total_loan_days_query = $dbs->query('SELECT DISTINCT loan_date FROM loan WHERE YEAR(loan_date) = ' . $selected_year . ' AND DAYOFWEEK(loan_date) IN (3,4)');
    $total_loan_days = $total_loan_days_query->num_rows;
    $loan_report[__('Transaction Average (Per Day)')] = @ceil($report_d/$total_loan_days);

    // peak transaction
    $loan_report[__('Total Peak Transaction')] = $peak_transaction_data[0];

    // total members having loans
    $report_q = $dbs->query('SELECT DISTINCT member_id FROM loan WHERE YEAR(loan_date) = ' . $selected_year);
    $report_d = $report_q->num_rows;
    $loan_report[__('Members Already Had Loans')] = $report_d;

    // total members having loans
    // get total member that already not expired
    $total_members_query = $dbs->query('SELECT COUNT(member_id) FROM member
        WHERE TO_DAYS(expire_date)>TO_DAYS(\''.date('Y-m-d').'\')');
    $total_members_data = $total_members_query->fetch_row();
    $loan_report[__('Members Never Have Loans Yet')] = $total_members_data[0]-$loan_report[__('Members Already Had Loans')];

    $table = new simbio_table();
    $table->table_attr = 'align="center" class="border" cellpadding="5" cellspacing="0"';
    // table header
    $row = 1;
    foreach ($loan_report as $headings=>$report_d) {
        $table->appendTableRow(array($headings, ':', $report_d));
        // set cell attribute
        $table->setCellAttr($row, 0, 'class="alterCell" valign="top" style="width: 170px;"');
        $table->setCellAttr($row, 1, 'class="alterCell" valign="top" style="width: 1%;"');
        $table->setCellAttr($row, 2, 'class="alterCell2" valign="top" style="width: auto;"');
        // add row count
        $row++;
    }
    $output .= '<div class="printPageInfo">' . __('Loan Data Summary') . ' für das Jahr ' . $selected_year .' <a class="printReport" onclick="window.print()" href="#">'.__('Print Current Page').'</a></div>';
    $output .= $table->printTable();

    $output .= '<div class="printPageInfo">'. str_replace('{selectedYear}', $selected_year,__('Visitor Count Report for year <strong>{selectedYear}</strong>')).'</div>'."\n";
    // months array
    $months['01'] = __('Jan');
    $months['02'] = __('Feb');
    $months['03'] = __('Mar');
    $months['04'] = __('Apr');
    $months['05'] = __('May');
    $months['06'] = __('Jun');
    $months['07'] = __('Jul');
    $months['08'] = __('Aug');
    $months['09'] = __('Sep');
    $months['10'] = __('Oct');
    $months['11'] = __('Nov');
    $months['12'] = __('Dec');

    // table start
    $row_class = 'alterCellPrinted';
    $output .= '<table align="center" class="border" style="width: 100%;" cellpadding="3" cellspacing="0">';

    // header
    $output .= '<tr>';
    $output .= '<td class="dataListHeaderPrinted">'.__('Member Type').'</td>';
    foreach ($months as $month_num => $month) {
        $total_month[$month_num] = 0;
        $output .= '<td class="dataListHeaderPrinted">'.$month.'</td>';
    }
    $output .= '<td class="dataListHeaderPrinted">Gesamt</td>';
    $output .= '</tr>';


    // get member type data from databse
    $_q = $dbs->query("SELECT member_type_id, member_type_name FROM mst_member_type LIMIT 100");
    while ($_d = $_q->fetch_row()) {
        $member_types[$_d[0]] = $_d[1];
    }
    $r = 1;
    // count library member visitor each month
    // this custom version for spielema separates members by age
    // and also adds the children of members
    // from our custom fields geburtsjahrkind1,
    // geburtsjahrkind2 and geburtsjahrkind3 as
    // additional visitors.
    // To make caluculations easier, the age is not
    // the age of the visit, but the age of the
    // person at the end of the year
    $ages = array(
        'unter 6 Jahre' => array('min_year' => $selected_year -5, 'max_year' => $selected_year),
        '6-13 Jahre' => array('min_year' => $selected_year -13, 'max_year' => $selected_year-6),
        '14-17 Jahre' => array('min_year' => $selected_year -17, 'max_year' => $selected_year-14),
        '18-27 Jahre' => array('min_year' => $selected_year -27, 'max_year' => $selected_year-18),
        'ab 28 Jahre' => array('min_year' => 0, 'max_year' => $selected_year-28),
        );
    foreach ($member_types as $id => $member_type) {
        foreach($ages as $ageLabel => $years) {
            $totalForAge = 0;
            $row_class = ($r%2 == 0)?'alterCellPrinted':'alterCellPrinted2';
            $output .= '<tr>';
            $output .= '<td class="'.$row_class.'">'.$member_type. ' (' . $ageLabel . ')</td>'."\n";
            foreach ($months as $month_num => $month) {
                $sql_str = "SELECT COUNT(visitor_id) FROM visitor_count AS vc
                    INNER JOIN (member AS m LEFT JOIN mst_member_type AS mt ON m.member_type_id=mt.member_type_id) ON m.member_id=vc.member_id
                    WHERE m.member_type_id=$id AND vc.checkin_date LIKE '$selected_year-$month_num-%' 
                        AND ( 
                                 (YEAR(m.birth_date) BETWEEN ".$years['min_year']." AND ".$years['max_year'].") 
                              OR (m.geburtsjahrkind1 BETWEEN ".$years['min_year']." AND ".$years['max_year'].") 
                              OR (m.geburtsjahrkind2 BETWEEN ".$years['min_year']." AND ".$years['max_year'].") 
                              OR (m.geburtsjahrkind3 BETWEEN ".$years['min_year']." AND ".$years['max_year'].") 
                        )";
                $visitor_q = $dbs->query($sql_str);
                $visitor_d = $visitor_q->fetch_row();
                if ($visitor_d[0] > 0) {
                    $output .= '<td class="'.$row_class.'"><strong style="font-size: 1.5em;">'.$visitor_d[0].'</strong></td>';
                } else {
                    $output .= '<td class="'.$row_class.'"><span style="color: #ff0000;">'.$visitor_d[0].'</span></td>';
                }
                $total_month[$month_num] += $visitor_d[0];
                $totalForAge += $visitor_d[0];
            }
            if ($totalForAge > 0) {
                $output .= '<td class="'.$row_class.'"><strong style="font-size: 1.5em;">'.$totalForAge.'</strong></td>';
            } else {
                $output .= '<td class="'.$row_class.'"><span style="color: #ff0000;">'.$totalForAge.'</span></td>';
            }
            $output .= '</tr>';
            $r++;
        }
    }

    // non member visitor count
    $row_class = ($r%2 == 0)?'alterCellPrinted':'alterCellPrinted2';
    $output .= '<tr>';
    $output .= '<td class="'.$row_class.'">'.__('NON-Member Visitor').'</td>'."\n";
    $totalVisitors = 0;
    foreach ($months as $month_num => $month) {
        $sql_str = "SELECT COUNT(visitor_id) FROM visitor_count AS vc
            WHERE (vc.member_id IS NULL OR vc.member_id='') AND vc.checkin_date LIKE '$selected_year-$month_num-%'";
        $visitor_q = $dbs->query($sql_str);
        $visitor_d = $visitor_q->fetch_row();
        if ($visitor_d[0] > 0) {
            $output .= '<td class="'.$row_class.'"><strong style="font-size: 1.5em;">'.$visitor_d[0].'</strong></td>';
        } else {
            $output .= '<td class="'.$row_class.'"><span style="color: #ff0000;">'.$visitor_d[0].'</span></td>';
        }
        $total_month[$month_num] += $visitor_d[0];
        $totalVisitors += $visitor_d[0];
    }
    if ($totalVisitors > 0) {
        $output .= '<td class="'.$row_class.'"><strong style="font-size: 1.5em;">'.$totalVisitors.'</strong></td>';
    } else {
        $output .= '<td class="'.$row_class.'"><span style="color: #ff0000;">'.$totalVisitors.'</span></td>';
    }
    $output .= '</tr>';

    // total for each month
    $output .= '<tr>';
    $output .= '<td class="dataListHeaderPrinted">'.__('Total visit/month').'</td>';
    foreach ($months as $month_num => $month) {
        $output .= '<td class="dataListHeaderPrinted">'.$total_month[$month_num].'</td>';
    }
    $output .= '<td class="dataListHeaderPrinted">'.array_sum($total_month).'</td>';
    $output .= '</tr>';

    $output .= '</table>';

    // print out
    echo $output;

    $content = ob_get_clean();
    // include the page template
    require SB.'/admin/'.$sysconf['admin_template']['dir'].'/printed_page_tpl.php';
}
