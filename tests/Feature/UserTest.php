<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserTest extends TestCase
{

    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_user_register()
    {
        $response = $this->json('POST', '/api/register', [
        'name' => 'Janusz', 
        'email' => 'janusz@janusz.pl', 
        'password' => 'JanuszJanusz', 
        'password_confirmation' => 'JanuszJanusz']);

        $response->assertStatus(200);
    }

    public function test_user_email_was_used()
    {
        $response = $this->json('POST', '/api/register', [
            'name' => 'Janusz', 
            'email' => 'janusz@janusz.pl', 
            'password' => 'JanuszJanusz', 
            'password_confirmation' => 'JanuszJanusz']);

        $response = $this->json('POST', '/api/register', [
            'name' => 'Adam', 
            'email' => 'janusz@janusz.pl', 
            'password' => 'AdamAdam', 
            'password_confirmation' => 'AdamAdam']);
    
        $response->assertStatus(422);
    }

    public function test_user_password_not_equal()
    {
        $response = $this->json('POST', '/api/register', [
            'name' => 'Adam', 
            'email' => 'janusz@janusz.pl', 
            'password' => 'AdamAdam', 
            'password_confirmation' => 'JanuszJanusz']);
    
        $response->assertStatus(422);
    }

    public function test_user_email_not_correct()
    {
        $response = $this->json('POST', '/api/register', [
            'name' => 'Adam', 
            'email' => 'januszjanusz.pl', 
            'password' => 'AdamAdam', 
            'password_confirmation' => 'JanuszJanusz']);
    
        $response->assertStatus(422);
    }

    public function test_user_name_is_empty()
    {
        $response = $this->json('POST', '/api/register', [
            'name' => '', 
            'email' => 'januszjanusz.pl', 
            'password' => 'AdamAdam', 
            'password_confirmation' => 'JanuszJanusz']);
    
        $response->assertStatus(422);
    }

    public function test_user_auth()
    {
        $response = $this->get('/api/user');
        $response->assertStatus(500);
    }

}
