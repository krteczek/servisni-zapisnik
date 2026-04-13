<?php
declare(strict_types=1);

$title = $title ?? '';
$content = $content ?? '';
?>

<!-- views/emails/layout.php -->

<!DOCTYPE html>
<html lang="cs">
<head>
<meta charset="UTF-8">
<title><?= $title ?></title>
</head>

<body style="font-family: Arial, sans-serif; line-height: 1.5;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8; padding:20px 0;">
  <tr>
    <td align="center">

      <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; padding:40px;">
        <tr>
          <td>

    <div style="max-width:600px;margin:auto;">
    <h1 style="margin-top:0; color:#333333;"><?= $title ?></h1>
        <?= $content ?>

        <hr style="margin-top:30px;">
        <p style="color:#777;font-size:12px;">
            Tým Bó systém
        </p>
    </div>
          </td>
        </tr>
      </table>

    </td>
  </tr>
</table>

</body>
</html>