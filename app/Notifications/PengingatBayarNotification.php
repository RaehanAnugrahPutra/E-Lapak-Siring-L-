<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Penyewaan;

class PengingatBayarNotification extends Notification
{
    public function __construct(public Penyewaan $penyewaan) {}

    public function via($notifiable)
    {
        return ['mail', 'database']; // email + simpan di tabel notifications [web:306]
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Pengingat Pembayaran Sewa Stand')
            ->greeting('Halo '.$notifiable->name.'!')
            ->line('Ini adalah pengingat untuk pembayaran sewa stand '.$this->penyewaan->stand->kode_stand)
            ->line('Periode: '.$this->penyewaan->tanggal_mulai_sewa.' s/d '.$this->penyewaan->tanggal_selesai_sewa)
            ->line('Total yang harus dibayar: Rp '.number_format($this->penyewaan->total_pembayaran, 0, ',', '.'))
            ->line('Silakan segera melakukan pembayaran sebelum masa 7 hari berakhir.')
            ->line('Terima kasih telah menggunakan layanan kami!');
    }

    public function toDatabase($notifiable)
    {
        return [
            'tipe'   => 'pengingat_bayar',
            'pesan'  => 'Pengingat pembayaran sewa stand '.$this->penyewaan->stand->kode_stand,
            'penyewaan_id' => $this->penyewaan->id,
        ];
    }
}
