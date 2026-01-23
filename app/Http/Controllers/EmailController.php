<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailController extends Controller
{
    public function enviarCorreoPHPMailer($to, $subject, $message)
{
    $mail = new PHPMailer(true);

    try {
        $emisor = 'comunica@beebuscr.com';
        $mail->isSMTP();
        $mail->Host = 'mail.beebuscr.com';
        $mail->SMTPAuth = true;
        $mail->Username = $emisor;
        $mail->Password = 'mombebes04';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($emisor, 'Beebus');

        $mail->addAddress($to);
        
        $mail->CharSet = 'UTF-8';

        $htmlBody = view('emails.sin-credito', ['message' => $message])->render();

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;

        // Enviar el correo
        $mail->send();
        return ['status' => 'success', 'message' => 'Correo enviado exitosamente'];
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => "Error al enviar el correo: {$mail->ErrorInfo}"];
    }
}


    public function executeSendEmail()
    {
        $to = 'francia24vs@gmail.com';
        $subject = 'Asunto del correo';
        $message = "<p>Este es el mensaje en <b>HTML</b></p>";
        $headers = ['Reply-To' => 'otroemail@tudominio.com'];
        $resultado = $this->enviarCorreoPHPMailer($to, $subject, $message, $headers);
        print_r($resultado);
    }
}
