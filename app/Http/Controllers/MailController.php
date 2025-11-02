<?php

namespace App\Http\Controllers;

use App\Mail\Mailer;
use App\Models\Bookings;
use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    protected $settings;

    public function __construct(
        Settings $settings
    )
    {
        $this->settings = $settings;
    }
    
    public function newBooking($booking){
        return $this->getTemplate($booking, 'newBooking', [
            '{guest_name}' =>$booking['user']['full_name'],
        ]);
    }

    public function bookingConfirmation($booking){
        return $this->getTemplate($booking, 'bookingConfirmation', [
            '{guest_name}' =>$booking['user']['full_name'],
        ]);
    }

    public function bookingRejected($booking){
        return $this->getTemplate($booking, 'bookingCancellation', [
            '{guest_name}' =>$booking['user']['full_name'],
        ]);
    }

        
    public function paymentReceived($booking){
        return $this->getTemplate($booking, 'paymentReceived', [
            '{guest_name}' =>$booking['user']['full_name'],
        ]);
    }


    public function getTemplate($booking, $type = 'bookingConfirmation', $keys = []){
        $settings =  $this->settings->where('type','notifications')->first();
        if(!$settings){
            return false;
        }
        $email_template = json_decode($settings->settings, true) ?? null;
        $template = null;
        if($email_template){
           $bookingConfirmation = $email_template['notifications'][$type] ?? null;
           if($bookingConfirmation && $bookingConfirmation['enabled']){
                $template = $bookingConfirmation['emailTemplate'] ?? null;
           }
        }
        if($template){
            $content = $this->replaceParameters($keys , $template);
            if($content){
                return $this->sendMail([
                    'to' => $booking['user']['email'],
                    'subject' => 'Booking Confirmation - Bukid ni Manang',
                    'content' => $content,
                ], 'template');
            }
        }
    }

    public function checkInReminder(){

    }

    public function sendTestEmail($request)
    {
        $mail_config = $request['settings'];
        $to = $request['toEmail'];
        $originalConfig = config('mail');
        try {
            $tempConfig =  [
                'driver' => 'smtp',
                'host' => $mail_config['smtpHost'],
                'port' => 587,
                'username' => $mail_config['smtpUsername'],
                'password' => $mail_config['smtpPassword'],
                'encryption' => 'tls',
            ];

            config(['mail' => $tempConfig]);
            // Prepare test email data
            $data = ['content' => 'This is a test email'];

            // Send the test email
            Mail::to($to)->send(new Mailer(
                $data, 
                'Test Email from Bukid ni Manang', 
                'template', 
                [], 
                $mail_config['fromEmail']
            ));

            return "Test email sent successfully!";

        } catch (\Exception $e) {
            return "Error sending email: " . $e->getMessage();

        } finally {
            config(['mail' => $originalConfig]);
        }
    }


    public function sendMail($datas, $template = 'template')
    {
        $originalConfig = config('mail');
        $tempConfig = $this->settings->getMailConfig('notifications');
        $subject = $datas['subject'] ?? 'No Subject';
        $template = $template ?? 'template';
        config(['mail' => $tempConfig]);
        try {
            Mail::to($datas['to'])
                ->send(new Mailer(
                    $datas, 
                    $subject, 
                    $template, 
                    [], 
                    $tempConfig['username'], 
                ));
            return true;

        } finally {
            // Restore the original mail configuration
            config(['mail' => $originalConfig]);
        }
    }

     public function replaceParameters($keys , $content){
        foreach($keys as $symbol => $value)
        {
            $content = str_replace($symbol, $value , $content);
        }
        return $content;
    }
}
