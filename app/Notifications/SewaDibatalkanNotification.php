<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Penyewaan;


// app/Notifications/SewaDibatalkanNotification.php
class SewaDibatalkanNotification extends Notification
{
    public function __construct(public Penyewaan $penyewaan) {}

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Pemberitahuan Pembatalan Sewa Stand')
            ->greeting('Halo '.$notifiable->name.'!')
            ->line('Sewa stand '.$this->penyewaan->stand->kode_stand.' telah dibatalkan oleh admin.')
            ->line('Alasan: '.$this->penyewaan->alasan_pembatalan)
            ->line('Periode sebelumnya: '.$this->penyewaan->tanggal_mulai_sewa.' s/d '.$this->penyewaan->tanggal_selesai_sewa)
            ->line('Terima kasih telah menggunakan layanan kami!');
    }

    public function toDatabase($notifiable)
    {
        return [
            'tipe'   => 'pembatalan_sewa',
            'pesan'  => 'Sewa stand '.$this->penyewaan->stand->kode_stand.' dibatalkan. Alasan: '.$this->penyewaan->alasan_pembatalan,
            'penyewaan_id' => $this->penyewaan->id,
        ];
    }
}
