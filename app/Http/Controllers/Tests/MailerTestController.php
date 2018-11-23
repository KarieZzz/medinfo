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
        $emails = [ 'shameev@miac-io.ru', 'im@miacmo.ru'] ;
        try {
            \Mail::send('emails.test', $for_mail_body, function ($m) use ($emails) {
                $m->from('noreply@miacmo.ru', 'Тестовое email сообщение Мединфо');
                $m->to($emails)->subject('Тестовое сообщение');
            });
        } catch(\Exception $e){
            dd($e);
        }
        echo '<p>' . $remark . '</p>';
        if( count(\Mail::failures()) > 0 ) {
            foreach (\Mail::failures() as $email_address) {
                echo '<p>Не доставлено ' . $email_address . '</p> ';
            }
        } else {
            echo 'Доставлено';
        }
    }
}