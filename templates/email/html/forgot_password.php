<?php

use Cake\Core\Configure;

$code = $data['code'];
$name = $user->username;
?>

<!doctype html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=unicode">
    <title><?= Configure::read('custom.site_name') ?></title>
</head>
<body>
    <table border="0" cellspacing="0" cellpadding="0" width="100%">
        <tr>
            <td style="text-align: center;padding: 20px 0;font-size: 20px;">
                Dear <?= $name; ?>, here is your recovery code:
            </td>
        </tr>
        <tr>
            <td style="font-size: 30px;">
                <div style="margin: 0 auto;width: fit-content;border: 1px solid black;padding: 10px 40px;">
                    <?= $code; ?>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
