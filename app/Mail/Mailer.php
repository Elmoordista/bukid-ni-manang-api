<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Mailer extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    
    public $data;
    public $subject;
    public $html;
    public $certificate;
    public $id;
    public $cc;
    public $bcc;
    public $attachmentFiles;
    public $template;
    public $emailFrom;
    public $messageId;
    public $reference;
    public $in_reply_to;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        $data, 
        $subject = null , 
        $template, 
        $attachmentFiles = null, 
        $emailFrom = 'bukidnimanang@gmail.com', 
        $messageId = null,
        $reference = null,
        $in_reply_to = null
    )
    {
        // $this->cc = $cc;
        $this->data = $data;
        // $this->from = $from;
        $this->emailFrom = $emailFrom;
        $this->subject = $subject;
        $this->attachmentFiles = $attachmentFiles;
        $this->template = $template;
        $this->cc = [];
        $this->bcc = [];
        $this->messageId = $messageId;
        $this->reference = $reference;
        $this->in_reply_to = $in_reply_to;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    
    public function build()
    {
        $mail = $this->view('emails.' . $this->template)
                ->with('data', $this->data)
                ->from($this->emailFrom)
                ->replyTo($this->emailFrom)
                ->subject($this->subject);

        if($this->attachmentFiles){
            foreach ($this->attachmentFiles as $attachment) {
                $mail->attach($attachment['path'], [
                    'as' => $attachment['name'],
                ]);
            }        
    
        }
        
        return $mail;
    }
}
