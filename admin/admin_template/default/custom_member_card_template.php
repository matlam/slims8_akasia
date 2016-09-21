<!DOCTYPE html>
<html><head><title>SpieleMA Ausleihkarte</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Pragma" content="no-cache" /><meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, post-check=0, pre-check=0" /><meta http-equiv="Expires" content="Fry, 02 Oct 2012 12:00:00 GMT" />
<style type="text/css">
@media print {
	.printButton {
        display: none;
        }
    }
    @page { margin: 1cm; size: A4;}
    body { margin: 0}
    .container_div { width: 79mm; height: 48mm; position: relative;
                    border-style: solid; border-collapse: collapse; border-width: 1px; float: left;
    }
    .logo {width: 77mm; position: absolute; left: 1mm;top: 1mm; z-index: -1;}
    .bio_label {display: none}
    /*.barcode {position: absolute; left: 2mm; bottom: 2mm; width: 40mm;}*/
    .header { position: absolute; top: 15mm; right: 0mm; width: 51mm; font-size: 18pt; text-align: center;}
    .bio_parent { position: absolute; top: 15mm; margin: 2mm;}
    .bio_div {margin: auto; font-size: 10pt; margin: 0.5mm;}
    .barcode {float: right;width: 50%; margin-left: 0; margin-right: 0; }
    .bio_name { margin-bottom: 1mm; text-align:right;}
    .bio_birth_date {float: right}
    /*.bio_email { position: absolute; top: 18mm; right: 0.5mm; font-size: 9pt;}*/
    .bio_email { float: right;}
    .bio_address { float: left; padding-top: 2mm;}
    .oeffnungszeiten {float: left; font-size: 9pt;}
    .library_address {float: right ; font-size: 6pt; width: 58%;}
    .contact_info {float: left; font-size: 9pt; width: calc(100% - 1mm);}
   .back_side {
       /*position: absolute; left: 80mm;top 0mm;*/
       /* -moz-transform: rotate(180deg);
        -ms-transform: rotate(180deg);
        -o-transform: rotate(180deg);
        -webkit-transform: rotate(180deg);
        transform: rotate(180deg);
        */
    }
    .rules {font-size: 8pt;
           padding: 1mm;
    }
    .rules p { margin: 0.5mm;}
</style>
</head>
<body>
<a class="printButton" href="#" onclick="window.print()">Print Again</a><br class="printButton"><br class="printButton">
<?php   // loop the chunked arrays to row
foreach ($chunked_card_arrays as $membercard_rows): ?>
    <?php foreach ($membercard_rows as $card): ?>

        <div class="container_div front_side">
            <img class="logo" src="<?php echo SWB . $sysconf['template']['dir']; ?>/spielema/img/non-gpl/spielema_logo_ohne_farbverlauf_sw_cropped_smaller.png" alt="SpieleMA Logo">
        <!--<div class="header">Ausleihkarte</div>-->

        <div class = "bio_parent">
            <!--<div class="bio_div bio_id"><label class="bio_label">Nr.</label><?php echo $card['member_id']; ?></div>-->
            <div class="bio_div bio_name"><label class="bio_label">Name:</label><?php echo $card['member_name']; ?></div>
            <!--<div class="bio_div bio_birth_date"><label class="bio_label">Geburtsdatum:</label><?php echo $card['birth_date']; ?></div>
            <div class="bio_div bio_address"><label class="bio_label">Adresse:</label><?php echo str_replace("\n", "<br>", $card['member_address']); ?></div>
            <div class="bio_div bio_email"><label class="bio_label">E-Mail:</label><?php echo $card['member_email']; ?></div>-->

        <img class="bio_div barcode" alt="<?php echo $card['member_id']; ?>" src="<?php echo SWB.IMG.'/barcodes/'.str_replace(array(' '), '_', $card['member_id']).'.png'; ?>" >

        <div class="bio_div oeffnungszeiten"><b>Öffnungszeiten:</b><br>Dienstag 16 - 19 Uhr<br>Mittwoch 15 - 20 Uhr</div>
        <div class="bio_div contact_info"><span><b>Tel. 0621 / 293-7697</b></span><span class="bio_email">info@spielema.net</span><span><br>nur während der Öffnungszeiten</span></div>
        <!--<div class="bio_div bio_email">info@spielema.net</div>-->
        </div>
        </div>
        <div class="container_div back_side">
            <div class="rules">
                <p>Bei uns gelten <b>folgende Spielregeln:</b></p>
                <ol type="1">
                    <li>Dieser Ausweis ist nicht übertragbar. Bei Verlust bitten wir um
                    sofortige Mitteilung.</li>
                    <li>Die Benutzungsordnung ist verbindlich.</li>
                    <li>Die Ausleihzeit beträgt 4 Wochen,1x verlängern ist möglich.</li>
                    <li>Bei Überschreitung des Rückgabetermins wird kostenpflichtig gemahnt.</li>
                    <li>Die Spiele sind sorgsam und schonend zu behandeln. Bei Verlust und Beschädigung ist Ersatz zu leisten.</li>
                </ol>
            </div>
        </div>
    <?php endforeach; ?>
<?php endforeach; ?>
<script type="text/javascript">self.print();</script>
</body></html>