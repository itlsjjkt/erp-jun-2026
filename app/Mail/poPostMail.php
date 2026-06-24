<?php

namespace App\Mail;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Dompdf\Options;

class poPostMail extends Mailable
{
    use Queueable, SerializesModels;
    public $email_data;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email_data)
    {
        $this->email_data = $email_data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */

    public function build()
    {
        // Membuat objek email terlebih dahulu
        $email = $this->subject($this->email_data['subject'])
            ->from($this->email_data['sender_email'])
            ->cc($this->email_data['cc_emails'])
            ->attachData($this->email_data['pdf']->output(), $this->email_data['file_items']['po']->doc_no .'.pdf', [
                'mime' => 'application/pdf',
            ]);
        
        if ($this->email_data['file_items']['history_supplier'] < 1) {
            $email->attach(public_path('docs/ContohSuratPernyataanRekening.pdf'), [
                'as' => 'ContohSuratPernyataanRekening.pdf', 
                'mime' => 'application/pdf',
            ]);
        }
            

        return $email->view('purchase.po.email.bodyPo', $this->email_data['file_items']);
    }



}
