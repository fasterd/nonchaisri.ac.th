<?php
/**
 * contact.php — โรงเรียนบ้านโนนไชยศรี
 * ส่งอีเมลผ่าน SMTP z.com โดยใช้ PHPMailer
 */

date_default_timezone_set('Asia/Bangkok');

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: https://nonchaisri.ac.th');
header('Access-Control-Allow-Methods: POST');

// รับเฉพาะ POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// ============================================================
// ตั้งค่า SMTP — แก้ค่าให้ตรงกับบัญชี z.com ของโรงเรียน
// ============================================================
define('SMTP_HOST',      'smtp.z.com');
define('SMTP_PORT',      587);
define('SMTP_USER',      'info@nonchaisri.ac.th');
define('SMTP_PASS',      'YOUR_PASSWORD_HERE');
define('MAIL_FROM',      'info@nonchaisri.ac.th');
define('MAIL_FROM_NAME', 'โรงเรียนบ้านโนนไชยศรี');
define('MAIL_TO',        'info@nonchaisri.ac.th');
// ============================================================

// Sanitize input
$name    = htmlspecialchars(trim($_POST['name']    ?? ''), ENT_QUOTES, 'UTF-8');
$contact = htmlspecialchars(trim($_POST['contact'] ?? ''), ENT_QUOTES, 'UTF-8');
$subject = htmlspecialchars(trim($_POST['subject'] ?? ''), ENT_QUOTES, 'UTF-8');
$message = htmlspecialchars(trim($_POST['message'] ?? ''), ENT_QUOTES, 'UTF-8');

// Validate ครบทุกช่อง
if (!$name || !$contact || !$subject || !$message) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
    exit;
}

// Validate ความยาว
if (mb_strlen($name) > 200 || mb_strlen($message) > 5000) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ข้อมูลยาวเกินกำหนด']);
    exit;
}

// ป้องกัน spam เบื้องต้น (ห้ามมี HTML link หรือ URL ในข้อความ)
if (preg_match('/<a[\s>]/i', $message) || preg_match('/https?:\/\//i', $message)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ข้อความไม่ถูกต้อง กรุณาอย่าใส่ลิงก์']);
    exit;
}

// โหลด PHPMailer — ใช้ standalone ไฟล์ใน /phpmailer/src/
require __DIR__ . '/phpmailer/src/Exception.php';
require __DIR__ . '/phpmailer/src/PHPMailer.php';
require __DIR__ . '/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailerException;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);

    // ReplyTo เฉพาะเมื่อ contact เป็นอีเมล (ถ้าเป็นเบอร์โทรข้ามไป)
    if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
        $mail->addReplyTo($contact, $name);
    }

    $mail->addAddress(MAIL_TO, 'โรงเรียนบ้านโนนไชยศรี');

    $mail->isHTML(true);
    $mail->Subject = '=?UTF-8?B?' . base64_encode('[แบบฟอร์มติดต่อ] ' . $subject) . '?=';
    $mail->Body    = buildEmailHTML($name, $contact, $subject, $message);
    $mail->AltBody = buildEmailText($name, $contact, $subject, $message);

    $mail->send();

    echo json_encode(['success' => true, 'message' => 'ส่งข้อความเรียบร้อยแล้ว โรงเรียนจะติดต่อกลับโดยเร็ว']);

} catch (MailerException $e) {
    error_log('[contact.php] Mailer error: ' . $mail->ErrorInfo);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'ไม่สามารถส่งข้อความได้ กรุณาลองใหม่หรือโทรติดต่อโดยตรง']);
}

// ─────────────────────────────────────────────
// HTML email template
// ─────────────────────────────────────────────
function buildEmailHTML(string $name, string $contact, string $subject, string $message): string
{
    $date = date('d/m/Y H:i');
    // $message ผ่าน htmlspecialchars แล้ว แปลง newline เพื่อแสดงใน HTML
    $msgHtml = nl2br($message);

    return <<<HTML
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<style>
  body{font-family:Arial,sans-serif;background:#f4f6f9;margin:0;padding:20px}
  .wrap{max-width:600px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.1)}
  .hdr{background:#1a5276;color:#fff;padding:28px 32px}
  .hdr h2{margin:0;font-size:20px;font-family:Arial,sans-serif}
  .hdr p{margin:6px 0 0;font-size:13px;opacity:.8}
  .bdy{padding:32px}
  .row{margin-bottom:20px}
  .lbl{font-size:11px;font-weight:700;color:#f39c12;text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px}
  .val{font-size:15px;color:#2c3e50;line-height:1.6}
  .msgbox{background:#f4f6f9;border-left:4px solid #1a5276;padding:16px;border-radius:0 8px 8px 0;font-size:15px;color:#2c3e50;line-height:1.8}
  .ftr{background:#f4f6f9;padding:14px 32px;font-size:12px;color:#95a5a6;border-top:1px solid #eee}
</style>
</head>
<body>
<div class="wrap">
  <div class="hdr">
    <h2>&#128231; ข้อความจากแบบฟอร์มติดต่อ</h2>
    <p>โรงเรียนบ้านโนนไชยศรี | nonchaisri.ac.th</p>
  </div>
  <div class="bdy">
    <div class="row"><div class="lbl">ชื่อ-นามสกุล</div><div class="val">{$name}</div></div>
    <div class="row"><div class="lbl">ติดต่อกลับ</div><div class="val">{$contact}</div></div>
    <div class="row"><div class="lbl">เรื่อง</div><div class="val">{$subject}</div></div>
    <div class="row"><div class="lbl">รายละเอียด</div><div class="msgbox">{$msgHtml}</div></div>
    <div class="row"><div class="lbl">วันที่ส่ง</div><div class="val">{$date} น.</div></div>
  </div>
  <div class="ftr">อีเมลอัตโนมัติจาก nonchaisri.ac.th — กรุณาอย่า reply โดยตรงที่อีเมลนี้</div>
</div>
</body>
</html>
HTML;
}

// ─────────────────────────────────────────────
// Plain-text fallback
// ─────────────────────────────────────────────
function buildEmailText(string $name, string $contact, string $subject, string $message): string
{
    $date = date('d/m/Y H:i');
    return implode("\n", [
        'ข้อความจากแบบฟอร์มติดต่อ — nonchaisri.ac.th',
        str_repeat('-', 40),
        "ชื่อ      : {$name}",
        "ติดต่อกลับ: {$contact}",
        "เรื่อง    : {$subject}",
        "วันที่    : {$date} น.",
        str_repeat('-', 40),
        $message,
    ]);
}
