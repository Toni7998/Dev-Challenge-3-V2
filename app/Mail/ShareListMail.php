<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class ShareListMail extends Mailable
{
    use Queueable, SerializesModels;

    public $listId;
    public $ownerId;
    public $acceptUrl;

    public function __construct($listId, $ownerId)
    {
        $this->listId = $listId;
        $this->ownerId = $ownerId;

        // Generar la URL correctamente
        $this->acceptUrl = route('shopping_list.accept', [
            'ownerId' => $this->ownerId,
            'listId' => $this->listId
        ]);
    }

    public function build()
    {
        return $this->subject('Te han compartido una lista de compras')
            ->view('emails.share_list')
            ->with([
                'acceptUrl' => $this->acceptUrl,
            ]);
    }
}
