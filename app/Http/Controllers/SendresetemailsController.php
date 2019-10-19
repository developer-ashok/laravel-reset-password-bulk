<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Mail;

class SendresetemailsController extends Controller
{
    
    /**
     * Send emails to all users at once when you moved all users to other website to here so they can change password by link
     * @$hok : 19/Oct/2019
     */
    public function index(Request $request){

        $users = User::all();

        $sentEmails = [];
        foreach($users as $k=>$v){
            $broker = Password::broker();

            $sentEmails[] = $v->email;

            $user = User::where("email", $v->email)->first();

            $reset_token = $broker->createToken($user); // flat token

            $reset_link = url(config('app.url').route('password.reset', ['token' => $reset_token, 'email' => $user->email], false));

            $result = Mail::send('emails.password_reset_custom', [
                'fullname' => $user->name,
                'reset_url' => $reset_link,
                'line1' => 'You are receiving this email because we received a password reset request for your account.',
                'line2' => 'This password reset link will expire in :count minutes.', ['count' => config('auth.passwords.users.expire')],
                'line3' => 'If you did not request a password reset, no further action is required.',
                'line4' => 'If you’re having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:',
                'copyright' => '© '.date('Y').' '.env('APP_NAME').'. All rights reserved.'
            ], function($message) use ($user){
                $message->subject('Reset Password Notification');
                $message->to($user->email);
            });           

            //Remove below sleep() when you have proper smtp or adjust it based on your need
            sleep(2); // in cash time out issue
        }  

        echo "<pre>";
        echo "<h2>Reset password email sent to below users</h2>";
        print_r($sentEmails);
        exit;      
    }
}
