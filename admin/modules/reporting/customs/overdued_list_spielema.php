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

/* Overdues Report */

// key to authenticate
define('INDEX_AUTH', '1');

// main system configuration
require '../../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-circulation');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
// privileges checking
$can_read = utility::havePrivilege('circulation', 'r') || utility::havePrivilege('reporting', 'r');
$can_write = utility::havePrivilege('circulation', 'w') || utility::havePrivilege('reporting', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
}

require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_element.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';
require SIMBIO.'simbio_UTILS/simbio_date.inc.php';
require MDLBS.'membership/member_base_lib.inc.php';
require MDLBS.'circulation/circulation_base_lib.inc.php';
require MDLBS.'reporting/report_dbgrid.inc.php';
require LIB.'date_format.inc.php';

$page_title = 'Overdued List Report';
$reportView = false;
$num_recs_show = 20;
if (isset($_GET['reportView'])) {
    $reportView = true;
}
if(isset($_POST['action']) && $_POST['action'] === 'addBaseFines') {
    $data = array(
        'fines_date' => date('Y-m-d'),
        'member_id' => $dbs->escape_string($_POST['memberID']),
        'debet' => 1.5,
        'credit' => 0,
        'description' => $dbs->escape_string($_POST['fineDescription'])
    );
     // create sql op object
    $sql_op = new simbio_dbop($dbs);
    if($sql_op->insert('fines', $data)) {
        utility::jsAlert('Grundmahngebühr eingetragen', utility::ALERT_TYPE_SUCCESS);
    } else {
        utility::jsAlert('Fehler beim Eintragen der Grundmahngebühr', utility::ALERT_TYPE_ERROR);
    }
    $reportView = true;
}

if (!$reportView) {
?>
    <!-- filter -->
    <fieldset>
    <div class="per_title">
    	<h2><?php echo __('Overdued List'); ?></h2>
    </div>
    <div class="infoBox">
    <?php echo __('Report Filter'); ?>
    </div>
    <div class="sub_section">
    <form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>" target="reportView">
    <div id="filterForm">
        <div class="divRow">
            <div class="divRowLabel"><?php echo __('Member ID').'/'.__('Member Name'); ?></div>
            <div class="divRowContent">
            <?php
            echo simbio_form_element::textField('text', 'id_name', '', 'style="width: 50%"');
            ?>
            </div>
        </div>
        <div class="divRow">
            <div class="divRowLabel"><?php echo __('Loan Date From'); ?></div>
            <div class="divRowContent">
            <?php
            echo simbio_form_element::dateField('startDate', '2000-01-01');
            ?>
            </div>
        </div>
        <div class="divRow">
            <div class="divRowLabel"><?php echo __('Loan Date Until'); ?></div>
            <div class="divRowContent">
            <?php
            echo simbio_form_element::dateField('untilDate', date('Y-m-d'));
            ?>
            </div>
        </div>
        <div class="divRow">
            <div class="divRowLabel"><?php echo __('Record each page'); ?></div>
            <div class="divRowContent"><input type="text" name="recsEachPage" size="3" maxlength="3" value="<?php echo $num_recs_show; ?>" /> <?php echo __('Set between 20 and 200'); ?></div>
        </div>
    </div>
    <div style="padding-top: 10px; clear: both;">
    <input type="button" name="moreFilter" class="button" value="<?php echo __('Show More Filter Options'); ?>" />
    <input type="submit" name="applyFilter" value="<?php echo __('Apply Filter'); ?>" />
    <input type="hidden" name="reportView" value="true" />
    </div>
    </form>
    </div>
    </fieldset>
    <!-- filter end -->
    <div class="dataListHeader" style="padding: 3px;"><span id="pagingBox"></span></div>
    <iframe name="reportView" id="reportView" src="<?php echo $_SERVER['PHP_SELF'].'?reportView=true'; ?>" frameborder="0" style="width: 100%; height: 500px;"></iframe>
<?php
} else {
  ob_start();
  // table spec
  $table_spec = 'member AS m
      LEFT JOIN loan AS l ON m.member_id=l.member_id';

  // create datagrid
  $reportgrid = new report_datagrid();
  $reportgrid->setSQLColumn('m.member_id AS \''.__('Member ID').'\'');
  $reportgrid->setSQLorder('MAX(l.due_date) DESC');
  $reportgrid->sql_group_by = 'm.member_id';

  $overdue_criteria = ' (l.is_lent=1 AND l.is_return=0 AND TO_DAYS(due_date) <= TO_DAYS(\''.date('Y-m-d').'\')) ';
  // is there any search
  if (isset($_GET['id_name']) AND $_GET['id_name']) {
      $keyword = $dbs->escape_string(trim($_GET['id_name']));
      $words = explode(' ', $keyword);
      if (count($words) > 1) {
          $concat_sql = ' (';
          foreach ($words as $word) {
              $concat_sql .= " (m.member_id LIKE '%$word%' OR m.member_name LIKE '%$word%') AND";
          }
          // remove the last AND
          $concat_sql = substr_replace($concat_sql, '', -3);
          $concat_sql .= ') ';
          $overdue_criteria .= ' AND '.$concat_sql;
      } else {
          $overdue_criteria .= " AND m.member_id LIKE '%$keyword%' OR m.member_name LIKE '%$keyword%'";
      }
  }
  // loan date
  if (isset($_GET['startDate']) AND isset($_GET['untilDate'])) {
      $date_criteria = ' AND (TO_DAYS(l.loan_date) BETWEEN TO_DAYS(\''.$_GET['startDate'].'\') AND
          TO_DAYS(\''.$_GET['untilDate'].'\'))';
      $overdue_criteria .= $date_criteria;
  }
  if (isset($_GET['recsEachPage'])) {
      $recsEachPage = (integer)$_GET['recsEachPage'];
      $num_recs_show = ($recsEachPage >= 5 && $recsEachPage <= 200)?$recsEachPage:$num_recs_show;
  }
  $reportgrid->setSQLCriteria($overdue_criteria);

  // set table and table header attributes
  $reportgrid->table_attr = 'align="center" class="dataListPrinted" cellpadding="5" cellspacing="0"';
  $reportgrid->table_header_attr = 'class="dataListHeaderPrinted"';
  $reportgrid->column_width = array('1' => '80%');

  // callback function to show overdued list
  function showOverduedList($obj_db, $array_data)
  {
      global $date_criteria;
      global $sysconf;

      $circulation = new circulation($obj_db, $array_data[0]);
      $circulation->ignore_holidays_fine_calc = $sysconf['ignore_holidays_fine_calc'];
      $circulation->holiday_dayname = $_SESSION['holiday_dayname'];
      $circulation->holiday_date = $_SESSION['holiday_date'];

      $sumOfFines = 0;

      // member name
      $member_q = $obj_db->query('SELECT member_name, member_email, member_phone, member_mail_address, member_address FROM member WHERE member_id=\''.$obj_db->escape_string($array_data[0]).'\'');
      $member_d = $member_q->fetch_row();
      $member_name = $member_d[0];
      if(empty($member_d[3])) {
        $member_mail_address = $member_d[4];
      } else {
        $member_mail_address = $member_d[3];
      }
      $member_mail_address = str_replace("\n", '<br />', $member_mail_address);
      unset($member_q);

      $ovd_title_q = $obj_db->query('SELECT l.item_code, i.price, i.price_currency,
          b.title, l.loan_date,
          l.due_date, (TO_DAYS(DATE(NOW()))-TO_DAYS(due_date)) AS \'Overdue Days\',
          l.loan_id
          FROM loan AS l
              LEFT JOIN item AS i ON l.item_code=i.item_code
              LEFT JOIN biblio AS b ON i.biblio_id=b.biblio_id
          WHERE (l.is_lent=1 AND l.is_return=0 AND TO_DAYS(due_date) <= TO_DAYS(\''.date('Y-m-d').'\')) AND l.member_id=\''.$obj_db->escape_string($array_data[0]).'\''.( !empty($date_criteria)?$date_criteria:'' ));
      $_buffer_member_details = '<h3 style="font-weight: bold; color: black;">'.$member_name.' '.$array_data[0].'</h3>';
      $_buffer_member_details .= '<div style="color: black; font-size: 10pt; margin-bottom: 3px;">'.$member_mail_address.'</div>';
      $_buffer_member_details .= '<div style="font-size: 10pt; margin-bottom: 3px;"><div id="'.$array_data[0].'emailStatus"></div>'.__('E-mail').': <a href="mailto:'.$member_d[1].'">'.$member_d[1].'</a> - <a class="usingAJAX" href="'.MWB.'membership/overdue_mail.php'.'" postdata="memberID='.$array_data[0].'" loadcontainer="'.$array_data[0].'emailStatus">' . __('Send Notification e-mail') . '</a> - '.__('Phone Number').': '.$member_d[2].'</div>';

      $_buffer_overdue_games = '<h4>Überfällige Spiele:</h4>';
      $_buffer_overdue_games .= '<table width="100%" cellspacing="0">';
      $titleList = array();
      while ($ovd_title_d = $ovd_title_q->fetch_assoc()) {
          $overdue = $circulation->countOverdueValue($ovd_title_d['loan_id'], date('Y-m-d'));
          $titleList[] = $ovd_title_d['title'] . ' (' . $ovd_title_d['item_code'] . ')';
          if(!$overdue) {
              $overdue = array('value' => 0, 'days' => 0);
          }
          $sumOfFines += $overdue['value'];
          $_buffer_overdue_games .= '<tr>';
          $_buffer_overdue_games .= '<td valign="top" width="5%">'.$ovd_title_d['item_code'].'</td>';
          $_buffer_overdue_games .= '<td valign="top" width="30%">'.$ovd_title_d['title'].'</td>';
          $_buffer_overdue_games .= '<td width="20%"><table><tr><td>'.__('Overdue').':</td><td>'.$ovd_title_d['Overdue Days'].' '.__('day(s)').' inkl. Ferien<br/>'.$overdue['days'].' Wochen (ohne Ferien)</td></tr></table></td>';
          $_buffer_overdue_games .= '<td width="25%">Vorläufige Mahngebühren: '.number_format ($overdue['value'], 2,',', '.').' €</td>';
          $_buffer_overdue_games .= '<td width="15%">'.__('Loan Date').': '.slims_date_format($ovd_title_d['loan_date']).' &nbsp; '.__('Due Date').': '.slims_date_format($ovd_title_d['due_date']).'</td>';
          $_buffer_overdue_games .= '</tr>';
      }
      $_buffer_overdue_games .= '</table>';

      $_buffer_unpaid_fines = '<h4>Unbezahlte Mahngebühren:</h4>';
      $unpaid_fines_q = $obj_db->query('SELECT * FROM fines WHERE debet > credit AND member_id = "' . $obj_db->escape_string($array_data[0]) .'"');
      if($unpaid_fines_q->num_rows > 0)
      {
        $_buffer_unpaid_fines .= '<table width="100%">';
        while ($unpaid_fines_d = $unpaid_fines_q->fetch_assoc()) {
            $_buffer_unpaid_fines .= '<tr><td>' . htmlspecialchars($unpaid_fines_d['description'], ENT_QUOTES | ENT_HTML5) . '</td><td>' . number_format($unpaid_fines_d['debet'] - $unpaid_fines_d['credit'], 2,',', '.') . ' €</td><td>' . slims_date_format($unpaid_fines_d['fines_date']) . '</td></tr>';
            $sumOfFines += $unpaid_fines_d['debet'] - $unpaid_fines_d['credit'];
        }
        $_buffer_unpaid_fines .= '</table>';
      } else {
        $_buffer_unpaid_fines .= 'Es sind keine unbezahlten Gebühren eingetragen. '
                . '<form method="post" action="' . $_SERVER['PHP_SELF'] . '" target="reportView">'
                . '<input type="hidden" name="action" value="addBaseFines">'
                . '<input type="hidden" name="memberID" value="' . $array_data[0] . '">'
                . '<input type="hidden" name="fineDescription" value="Grundmahngebühren für ' . htmlspecialchars(implode(', ', $titleList), ENT_QUOTES) . '">'
                . '<input name="addBaseFines" value="Grundmahngebühren eintragen" type="submit" style="color: #fff; background-color: #337ab7; border-color: #2e6da4;">'
                . '</form>';
      }
      $_buffer_total_fines = '<h4 style="font-weight: bold;">Summe der aktuell aufgelaufenen Mahngebühren: ' . number_format($sumOfFines, 2,',', '.')  . ' €</h4>';
      return $_buffer_member_details . $_buffer_total_fines . $_buffer_overdue_games . $_buffer_unpaid_fines;
  }
  // modify column value
  $reportgrid->modifyColumnContent(0, 'callback{showOverduedList}');

  // put the result into variables
  echo $reportgrid->createDataGrid($dbs, $table_spec, $num_recs_show);

  ?>
  <script type="text/javascript" src="<?php echo JWB.'jquery.js'; ?>"></script>
  <script type="text/javascript" src="<?php echo JWB.'updater.js'; ?>"></script>
  <script type="text/javascript">
  // registering event for send email button
  $(document).ready(function() {
      parent.$('#pagingBox').html('<?php echo str_replace(array("\n", "\r", "\t"), '', $reportgrid->paging_set) ?>');
      $('a.usingAJAX').click(function(evt) {
          evt.preventDefault();
          var anchor = $(this);
          // get anchor href
          var url = anchor.attr('href');
          var postData = anchor.attr('postdata');
          var loadContainer = anchor.attr('loadcontainer');
          if (loadContainer) { container = jQuery('#'+loadContainer); }
          // set ajax
          if (postData) {
              container.simbioAJAX(url, {method: 'post', addData: postData});
          } else {
              container.simbioAJAX(url, {addData: {ajaxload: 1}});
          }
      });
  });
  </script>
  <?php

  $content = ob_get_clean();
  // include the page template
  require SB.'/admin/'.$sysconf['admin_template']['dir'].'/printed_page_tpl.php';
}
