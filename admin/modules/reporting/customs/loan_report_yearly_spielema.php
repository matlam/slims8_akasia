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

/*  */

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
// privileges checking
$can_read = utility::havePrivilege('reporting', 'r');
$can_write = utility::havePrivilege('reporting', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
}

require SIMBIO.'simbio_GUI/form_maker/simbio_form_element.inc.php';

$page_title = 'Spielebericht SpieleMA';
$reportView = false;
if (isset($_GET['reportView'])) {
    $reportView = true;
}

if (!$reportView) {
?>
 <div class="per_title">
        <h2>Ausleihbericht (3 Jahre)</h2>
    </div>
    <div class="infoBox">
    Alle Spiele die in den letzten drei Jahren nicht ausgeliehen wurden sind hier aufgelistet. 
    Wichtig der Start des Ausleihsystemes war erst am 01.01.2017 wird aber im Bericht berücksichtigt.
    </div>
    </fieldset>
    <iframe name="reportView" id="reportView" src="<?php echo $_SERVER['PHP_SELF'].'?reportView=true'; ?>" frameborder="0" style="width: 100%; height: 500px;"></iframe>
<?php
} else {
    ob_start();
    
    // table start
    $row_class = 'alterCellPrinted';
    
    $output = '<table align="center" class="border" style="width: 100%;" cellpadding="3" cellspacing="0">';

    // header
    $output .= '<tr>';
    $output .= '<td class="dataListHeaderPrinted">Kategorie</td>';
    $output .= '<td class="dataListHeaderPrinted">Titel</td>';
    $output .= '<td class="dataListHeaderPrinted">Artikelcode</td>';
    $output .= '<td class="dataListHeaderPrinted">Letzte Ausleihe</td>';
    $output .= '<td class="dataListHeaderPrinted">Veröffentlichungsjahr</td>';
    $output .= '</tr>';

    //Suche der Exemplare welche in den letzten drei Jahren nicht ausgeliehen wurden und deren Datum vor Systemstart 01.01.2017 liegt
    //es werden nur Exemplare mit dem Standort Ausleihe aufgelistet
    $_q = $dbs->query(" SELECT DISTINCT STAMMDATEN.Titel, loan.loan_date,STAMMDATEN.Jahr,STAMMDATEN.Id,STAMMDATEN.Kategorie
                        FROM 
                        (
                            SELECT biblio.title AS Titel, biblio.publish_year AS Jahr,item.item_code AS Id,biblio.classification AS Kategorie,item.input_date AS Erfassungsdatum
                            FROM item
                            INNER JOIN biblio
                            ON item.biblio_id=biblio.biblio_id
                            WHERE item.location_id='AUS'
                        ) AS STAMMDATEN
                        LEFT JOIN loan
                        ON loan.item_code=STAMMDATEN.Id
                        Where (loan.loan_date IS NULL OR loan.loan_date < DATE_SUB(NOW(), INTERVAL 36 MONTH)) AND (STAMMDATEN.Erfassungsdatum<'2017-1-1' OR STAMMDATEN.Erfassungsdatum<DATE_SUB(NOW(), INTERVAL 36 MONTH))
                        ORDER BY STAMMDATEN.Kategorie,STAMMDATEN.Id,STAMMDATEN.Jahr");

    $r = 1;
    //Ausgabe der einzelnen Spiele Items
    while ($_d = $_q->fetch_row()) {
        $row_class = ($r%2 == 0)?'alterCellPrinted':'alterCellPrinted2';
        $output .= '<tr>';
        $output .= '<td class="'.$row_class.'">'.$_d[4].'</td>'."\n";
        $output .= '<td class="'.$row_class.'">'.$_d[0].'</td>'."\n";
        $output .= '<td class="'.$row_class.'">'.$_d[3].'</td>'."\n";
        $output .= '<td class="'.$row_class.'">'.$_d[1].'</td>'."\n";
        $output .= '<td class="'.$row_class.'">'.$_d[2].'</td>'."\n";
        $output .= '<tr>';
        $output .= '</tr>';
        $r++;
    }

    $output .= '</table>';

    // print out
    echo '<div class="printPageInfo"> <a class="printReport" onclick="window.print()" href="#">'.__('Print Current Page').'</a></div>'."\n";
    echo $output;

    $content = ob_get_clean();
    // include the page template
    require SB.'/admin/'.$sysconf['admin_template']['dir'].'/printed_page_tpl.php';
}
