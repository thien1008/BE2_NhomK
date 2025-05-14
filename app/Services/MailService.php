<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Log;

class MailService
{
    protected $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);

        // Cấu hình PHPMailer
        try {
            // Cài đặt server
            $this->mailer->isSMTP();
            $this->mailer->Host = env('MAIL_HOST', 'smtp.mailtrap.io');
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = env('MAIL_USERNAME', '');
            $this->mailer->Password = env('MAIL_PASSWORD', '');
            $this->mailer->SMTPSecure = env('MAIL_ENCRYPTION', 'tls');
            $this->mailer->Port = env('MAIL_PORT', 2525);

            // Cấu hình người gửi
            $this->mailer->setFrom(env('MAIL_FROM_ADDRESS', 'no-reply@example.com'), env('MAIL_FROM_NAME', 'Laravel App'));
            $this->mailer->CharSet = 'UTF-8';
        } catch (Exception $e) {
            Log::error('PHPMailer configuration error: ' . $e->getMessage());
        }
    }

    public function send($to, $subject, $body, $isHtml = true)
    {
        try {
            // Thêm người nhận
            $this->mailer->addAddress($to);

            // Nội dung email
            $this->mailer->isHTML($isHtml);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;

            // Gửi email
            $this->mailer->send();
            Log::info('Email sent successfully to: ' . $to);
            return true;
        } catch (Exception $e) {
            Log::error('Error sending email: ' . $e->getMessage());
            return false;
        }
    }
}