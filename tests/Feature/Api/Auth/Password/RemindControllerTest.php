<?php

namespace Tests\Feature\Api\Auth\Password;

use Mail;
use Tests\Feature\ApiTestCase;
use App\Mail\ResetPassword;
use App\Models\User;

class RemindControllerTest extends ApiTestCase
{
    
    public function test_test_send_password_reminder()
    {
        $this->setSettings(['forgot_password' => true]);

        Mail::fake();

        $user = User::factory()->create(['email' => 'test@test.com']);

        $this->postJson('api/password/remind', ['email' => 'test@test.com'])
            ->assertOk();

        Mail::assertQueued(ResetPassword::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    
    public function test_test_password_reminder_with_wrong_email()
    {
        $this->setSettings(['forgot_password' => true]);

        $this->postJson('api/password/remind', ['email' => 'test@test.com'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }
}
