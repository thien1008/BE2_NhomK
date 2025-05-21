<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * The password reset token.
     *
     * @var string
     */
    public $token;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $resetUrl = url(route('password.reset', ['token' => $this->token]));
        
        return (new MailMessage)
            ->subject('Đặt lại mật khẩu')
            ->line('Xin chào,')
            ->line('Bạn đã yêu cầu đặt lại mật khẩu. Vui lòng click vào link dưới đây để đặt lại mật khẩu:')
            ->action('Đặt lại mật khẩu', $resetUrl)
            ->line('Link này sẽ hết hạn sau 1 giờ.')
            ->line('Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.')
            ->salutation('Trân trọng, Đội ngũ hỗ trợ');
    }
}