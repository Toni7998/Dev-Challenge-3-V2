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
    public $listName;
    public $shoppingListUrl;

    public function __construct($listId, $ownerId, $listName)
    {
        $this->listId = $listId;
        $this->ownerId = $ownerId;
        $this->listName = $listName;

        // URL para acceder a la lista de compras
        $this->shoppingListUrl = route('shopping_list.index');
    }

    public function build()
    {
        return $this->subject('Te han compartido una lista de compras: ' . $this->listName)
            ->view('emails.share_list')
            ->with([
                'listName' => $this->listName,
                'shoppingListUrl' => $this->shoppingListUrl,
            ]);
    }
}
