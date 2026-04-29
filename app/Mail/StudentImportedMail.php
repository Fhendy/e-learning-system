<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StudentImportedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $student;
    public $password;

    public function __construct(User $student, $password = null)
    {
        $this->student = $student;
        $this->password = $password;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Akun E-Learning Anda Telah Dibuat',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.student-imported',
        );
    }
}