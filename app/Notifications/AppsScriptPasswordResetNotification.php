<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class AppsScriptPasswordResetNotification extends Notification
{
    protected string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function via($notifiable): array
    {
        return [AppsScriptChannel::class];
    }

    public function toAppsScript($notifiable): array
    {
        $email = $notifiable->getEmailForPasswordReset();

        $resetUrl = route('password.reset', [
            'token' => $this->token,
            'email' => $email,
        ]);

        return [
            'to' => $email,
            'subject' => 'Reset Password RESQNET',
            'body' => '
                <div style="font-family:Arial,sans-serif;max-width:600px;margin:auto;padding:24px;">
                    <h2 style="color:#dc2626;">RESQNET</h2>
                    <h3>Permintaan Reset Password</h3>
                    <p>Halo,</p>
                    <p>Kami menerima permintaan untuk mengatur ulang password akun RESQNET Anda.</p>
                    <p>
                        <a href="' . e($resetUrl) . '"
                           style="display:inline-block;background:#dc2626;color:white;padding:12px 22px;text-decoration:none;border-radius:6px;">
                            Reset Password
                        </a>
                    </p>
                    <p>Link ini mengikuti masa berlaku token reset password RESQNET.</p>
                    <p>Jika Anda tidak meminta reset password, abaikan email ini.</p>
                    <p>Tim RESQNET</p>
                </div>
            ',
        ];
    }
}
