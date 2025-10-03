<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CodigoVerificacionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $codigo;
    public $nombre;

    public function __construct($codigo, $nombre)
    {
        $this->codigo = $codigo;
        $this->nombre = $nombre;
    }

    public function build()
    {
        return $this->subject('Código de verificación')
                    ->view('emails.codigo_verificacion');
    }
}
