<?php

namespace App\Http\Controllers\Tests;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class MailerTestController extends Controller
{
    //
    public function testmail()
    {
        $remark = "Тестовое сообщение";
        $for_mail_body = compact('remark');
        $emails = [ 'shameev@miac-io.ru', 'shameev38@gmail.com'] ;
        try {
            \Mail::send('emails.test', $for_mail_body, function ($m) use ($emails) {
                $m->from(config('medinfo.server_email'), 'Тестовое email сообщение Мединфо');
                $m->to($emails)->subject('Тестовое сообщение');
            });
        } catch(\Exception $e){
            dd($e);
        }
        echo '<p>' . $remark . '</p>';
        echo '<p> От' . config('medinfo.server_email') . '</p>';
        if( count(\Mail::failures()) > 0 ) {
            foreach (\Mail::failures() as $email_address) {
                echo '<p>Не доставлено ' . $email_address . '</p> ';
            }
        } else {
            echo 'Доставлено';
        }
    }
}
