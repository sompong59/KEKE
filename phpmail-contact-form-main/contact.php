

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ... (ໂຄດ PHP ຂອງທ່ານ) ...
?>

<?php
// var_dump($_POST);
// var_dump($_FILES);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if (isset($_POST['originalLanguage']) &&
    isset($_POST['targetLanguage']) &&
    isset($_FILES['form_fields']) &&
    isset($_POST['name']) &&
    isset($_POST['email']) &&
    isset($_POST['phoneNamber']) &&
    isset($_POST['subject']) &&
    isset($_POST['text'])) {

    $originalLanguage = $_POST['originalLanguage'];
    $targetLanguage = $_POST['targetLanguage'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phoneNamber = $_POST['phoneNamber'];
    $subject = $_POST['subject'];
    $text = $_POST['text'];
    $files = $_FILES['form_fields'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response = ['success' => false, 'message' => 'Invalid email format'];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    if (empty($originalLanguage) || empty($targetLanguage) || empty($name) || empty($subject) || empty($text)) {
        $response = ['success' => false, 'message' => 'Fill out all required entry fields'];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'aaop8054@gmail.com';
        $mail->Password = 'ocpiocrrodwkdhoe';
        $mail->SMTPSecure = "ssl";
        $mail->Port = 465;
        $mail->setFrom($email, $name);
        $mail->addAddress('aaop8054@gmail.com');

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->CharSet = 'UTF-8';
        $mail->Body = "
            <div style='font-family: \"Noto Sans Lao\", sans-serif;'>
                <h3>ສະບາຍດີບໍລິສັດ Techart ຂ້າພະເຈົ້າສົນໃຈບໍລິການແປພາສາ</h3>
                <p><strong>ພາສາຕົ້ນສະບັບ</strong>: $originalLanguage</p>
                <p><strong>ພາສາເປົ້າໝາຍ</strong>: $targetLanguage</p>
                <p><strong>ຂ້າພະເຈົ້າຊື່</strong>: $name</p>
                <p><strong>Email</strong>: $email</p>
                <p><strong>ເບີໂທ</strong>: $phoneNamber</p>
                <p><strong>Subject</strong>: $subject</p>
                <p><strong>Message</strong>: $text</p>
                <h3>ເມືອທ່ານເຫັນຂໍ້ຄວາມນີ້ລົບກວນຕອບກັບຂ້າພະເຈົ້າດ້ວຍ</h3>
            </div>
        ";

        $fileCount = count($files['name']);
        for ($i = 0; $i < $fileCount; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $mail->addAttachment($files['tmp_name'][$i], $files['name'][$i]);
            }
        }

        $mail->send();
        $response = ['success' => true, 'message' => 'ສົ່ງຂໍ້ຄວາມສຳເລັດ'];
    } catch (Exception $e) {
        $response = ['success' => false, 'message' => 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo];
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>