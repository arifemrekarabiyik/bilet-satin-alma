<?php
session_start();
require_once 'database.php';
require_once('tcpdf/tcpdf.php');


date_default_timezone_set('Europe/Istanbul');



function hex2rgb($hex)
{
    $hex = str_replace("#", "", $hex);
    if (strlen($hex) == 3) {
        $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
        $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
        $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
    } else {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    }
    return array('r' => $r, 'g' => $g, 'b' => $b);
}


if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'yolcu' || !isset($_GET['bilet_id'])) {
    die("Yetkisiz erişim veya eksik bilgi.");
}

$bilet_id = $_GET['bilet_id'];
$yolcu_id = $_SESSION['user_id'];

$sql = "
    SELECT 
        u.username AS yolcu_adi, u.email AS yolcu_email,
        b.alis_tarihi, b.koltuk_no,
        s.kalkis_yeri, s.varis_yeri, s.sefer_tarihi,
        f.firma_adi
    FROM biletler AS b
    JOIN users AS u ON b.yolcu_id = u.id
    JOIN seferler AS s ON b.sefer_id = s.id
    JOIN firmalar AS f ON s.firma_id = f.id
    WHERE b.id = ? AND b.yolcu_id = ?
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$bilet_id, $yolcu_id]);
$bilet = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bilet) {
    die("Bilet bulunamadı veya bu bileti görme yetkiniz yok.");
}


$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor($bilet['firma_adi']);
$pdf->SetTitle('Yolcu Bileti - ' . $bilet['yolcu_adi']);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(TRUE, 10);

$pdf->SetFont('dejavusans', '', 10);
$pdf->AddPage();


$color_primary = '#ee6700';
$color_text_light = '#FFFFFF';
$color_text_dark = '#333333';
$color_border = '#DDDDDD';
$color_bg_light = '#F9F9F9';


$html = '
<style>
    body { 
        font-family: "dejavusans", sans-serif; 
        color: ' . $color_text_dark . '; 
    }
    .wrapper { 
        border: 1px solid ' . $color_border . ';
        width: 100%; 
    }
    .header-table { 
        background-color: ' . $color_primary . '; 
        color: ' . $color_text_light . '; 
    }
    .header-table h1 { 
        font-size: 18px; 
        margin: 0; 
        padding: 0; 
        color: ' . $color_text_light . '; 
    }
    .header-table .ticket-info { 
        font-size: 10px; 
        text-align: right; 
    }
    .header-table .logo { 
        width: 30mm; 
    }
    .journey-table { 
        background-color: ' . $color_bg_light . '; 
        padding: 15px 0; 
    }
    .journey-table .city { 
        font-size: 22px; 
        font-weight: bold; 
        color: ' . $color_primary . '; 
    }
    .journey-table .arrow { 
        font-size: 24px; 
        font-weight: bold; 
        color: ' . $color_text_dark . '; 
    }
    .journey-date { 
        font-size: 14px; 
        font-weight: bold; 
        text-align: center; 
        padding-bottom: 15px; 
        background-color: ' . $color_bg_light . '; 
    }
    .details-table { 
        width: 100%; 
        border-collapse: collapse; 
    }
    .details-table td { 
        padding: 12px; 
        border-bottom: 1px solid ' . $color_border . '; 
        font-size: 11px; 
    }
    .details-table tr:last-child td { 
        border-bottom: none; 
    }
    .details-table .label { 
        width: 30%; 
        font-weight: bold; 
        color: #555; 
    }
    .details-table .data { 
        width: 70%; 
        font-weight: bold; 
        font-size: 12px; 
    }
    .details-table .data.seat { 
        font-size: 22px; 
        color: ' . $color_primary . '; 
        font-weight: bold; 
    }
</style>

<div class="wrapper">

    <table class="header-table" width="100%" cellpadding="10" cellspacing="0" style="background-color: ' . $color_primary . '; color: ' . $color_text_light . ';">
        <tr>
            <td width="20%" class="logo" style="text-align:left;">
                <img src="/var/www/html/otobus_projesi/images/recepivediklogo.jpg" width="113" />
            </td>
            <td width="50%" style="vertical-align:middle;">
                <h1 style="color: ' . $color_text_light . ';">' . htmlspecialchars($bilet['firma_adi']) . '</h1>
            </td>
            <td width="30%" class="ticket-info" style="vertical-align:middle; text-align:right; font-size:10px; color: ' . $color_text_light . ';">
                <b>E-BİLET / YOLCU BİLETİ</b><br>
                Bilet ID: #' . $bilet_id . '
            </td>
        </tr>
    </table>

    <table class="journey-table" width="100%" cellpadding="10" cellspacing="0" style="background-color: ' . $color_bg_light . ';">
        <tr>
            <td width="45%" style="text-align:right;">
                <span style="font-size:10px; color:#777;">KALKIŞ YERİ</span><br>
                <span class="city" style="color: ' . $color_primary . ';">' . htmlspecialchars($bilet['kalkis_yeri']) . '</span>
            </td>
            <td width="10%" style="text-align:center;">
                <span class="arrow">&rarr;</span>
            </td>
            <td width="45%" style="text-align:left;">
                <span style="font-size:10px; color:#777;">VARIŞ YERİ</span><br>
                <span class="city" style="color: ' . $color_primary . ';">' . htmlspecialchars($bilet['varis_yeri']) . '</span>
            </td>
        </tr>
    </table>
    <div class="journey-date" style="background-color: ' . $color_bg_light . ';">
        ' . date('d F Y, H:i', strtotime($bilet['sefer_tarihi'])) . '
    </div>

    <table class="details-table" cellpadding="10" cellspacing="0" width="100%">
        <tr>
            <td class="label">Yolcu Adı Soyadı</td>
            <td class="data">' . htmlspecialchars($bilet['yolcu_adi']) . '</td>
        </tr>
        <tr>
            <td class="label">Koltuk Numarası</td>
            <td class="data seat" style="color: ' . $color_primary . ';">' . htmlspecialchars($bilet['koltuk_no']) . '</td>
        </tr>
        <tr>
            <td class="label">Yolcu E-posta</td>
            <td class="data">' . htmlspecialchars($bilet['yolcu_email']) . '</td>
        </tr>
        <tr>
            <td class="label">Satın Alma Tarihi</td>
            <td class="data" style="font-size:10px;">' . date('d.m.Y H:i', strtotime($bilet['alis_tarihi'])) . '</td>
        </tr>
    </table>

</div>
';


$pdf->writeHTML($html, true, false, true, false, '');


$pdf->SetY($pdf->GetY() + 10); 
$qr_data = "BiletID:{$bilet_id}|Yolcu:{$bilet['yolcu_adi']}|Koltuk:{$bilet['koltuk_no']}";
$qr_style = array(
    'border' => 1,
    'vpadding' => 2,
    'hpadding' => 2,
    'fgcolor' => array(0, 0, 0),
    'bgcolor' => false,
    'module_width' => 1,
    'module_height' => 1
);
$qr_y_konumu = $pdf->GetY();
$pdf->write2DBarcode($qr_data, 'QRCODE,M', 150, $qr_y_konumu, 45, 45, $qr_style, 'N');
$pdf->SetFont('dejavusans', '', 9);
$pdf->SetTextColor(80, 80, 80);
$pdf->MultiCell(130, 40, "İyi yolculuklar dileriz!\n\nBu bileti okutarak otobüse hızlı giriş yapabilirsiniz.\nBilet Oluşturma: " . date('d.m.Y H:i:s'), 0, 'L', 0, 1, 10, $qr_y_konumu + 5);


$pdf->Output('bilet_' . $bilet_id . '.pdf', 'D');

?>