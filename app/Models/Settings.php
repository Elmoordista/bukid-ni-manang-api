<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'settings',
    ];

    public function getMailConfig($type)
    {
        $setting = $this->where('type', $type)->first();

        if ($setting) {
            $config = json_decode($setting->settings, true);
            $email_smtp = $config['email'] ?? [];
            return [
                'driver' => 'smtp',
                'host' => $email_smtp['smtpHost'], // Use the endpoint from Amazon SES config
                'port' => $email_smtp['smtpPort'],
                'username' => $email_smtp['smtpUsername'],
                'password' => $email_smtp['smtpPassword'],
                'sender' => $email_smtp['fromEmail'], // Assuming this is the verified email
                'encryption' => 'tls', // Assuming TLS is used, adjust if necessary
            ];
        }

        return [];
    }
}
