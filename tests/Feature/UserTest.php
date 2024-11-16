<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testRegisterSuccess(): void
    {
        $this->post('/api/users', [
            'username' => 'Chieru16',
            'email' => 'rifqiraehan86@gmail.com',
            'password' => 'rahasia'
        ])->assertStatus(201)
            ->assertJson([
                'data' => [
                    'username' => 'Chieru16',
                    'email' => 'rifqiraehan86@gmail.com'
                ]
            ]);
    }

    public function testRegisterFailed(): void
    {
        $this->post('/api/users', [
            'username' => '',
            'email' => '',
            'password' => ''
        ])->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'username' => ['The username field is required.'],
                    'email' => ['The email field is required.'],
                    'password' => ['The password field is required.']
                ]
            ]);
    }

    public function testRegisterUsernameAlreadyExists(): void
    {
        $this->testRegisterSuccess();
        $this->post('/api/users', [
            'username' => 'Chieru16',
            'email' => 'rifqiraehan86@gmail.com',
            'password' => 'rahasia'
        ])->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'username' => ['Username already exists']
                ]
            ]);
    }

    public function testRegisterEmailAlreadyExists(): void
    {
        $this->testRegisterSuccess();
        $this->post('/api/users', [
            'username' => 'chieru',
            'email' => 'rifqiraehan86@gmail.com',
            'password' => 'rahasia'
        ])->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'email' => ['Email already exists']
                ]
            ]);
    }

    public function testLoginSuccess() {
        $this->seed([UserSeeder::class]);

        $this->post('/api/users/login', [
            'username' => 'Chieru16',
            'password' => 'rahasia'
        ])->assertStatus(200)
            ->assertJson([
                'data' => [
                    'username' => 'Chieru16',
                    'email' => 'rifqiraehan86@gmail.com'
                ]
            ]);

        $user = User::where('username', 'Chieru16')->first();
        self::assertNotNull($user->token);
    }

    public function testLoginFailedUsernameNotFound() {
        $this->seed([UserSeeder::class]);

        $this->post('/api/users/login', [
            'username' => 'Chieru126',
            'password' => 'rahasia'
        ])->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'message' => ['Username or password wrong']
                ]
            ]);
    }

    public function testLoginFailedPasswordWrong() {
        $this->seed([UserSeeder::class]);

        $this->post('/api/users/login', [
            'username' => 'Chieru16',
            'password' => 'salah'
        ])->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'message' => ['Username or password wrong']
                ]
            ]);
    }

    public function testGetSuccess() {
        $this->seed([UserSeeder::class]);

        $this->get('api/users', [
            'Authorization' => 'test'
        ])->assertStatus(200)
            ->assertJson([
                'data' => [
                    'username' => 'Chieru16',
                    'email' => 'rifqiraehan86@gmail.com'
                ]
            ]);
    }

    public function testGetUnathorized() {
        $this->seed([UserSeeder::class]);

        $this->get('api/users')
            ->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'message' => ['Unauthorized']
                ]
            ]);
    }

    public function testGetInvalidToken() {
        $this->seed([UserSeeder::class]);

        $this->get('api/users', [
            'Authorization' => 'salah'
        ])->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'message' => ['Unauthorized']
                ]
            ]);
    }

    public function testUpdateUsernameSuccess() {
        $this->seed([UserSeeder::class]);
        $oldUser = User::where('username', 'Chieru16')->first();

        $this->patch('/api/users',
        [
            'username' => 'Akebi'
        ],
        [
            'Authorization' => 'test'
        ])->assertStatus(200)
            ->assertJson([
                'data' => [
                    'username' => 'Akebi',
                    'email' => 'rifqiraehan86@gmail.com'
                ]
            ]);

        $newUser = User::where('username', 'Akebi')->first();
        self::assertNotEquals($oldUser->username, $newUser->username);
    }

    public function testUpdateEmailSuccess() {
        $this->seed([UserSeeder::class]);
        $oldUser = User::where('username', 'Chieru16')->first();

        $this->patch('/api/users',
        [
            'email' => 'akebi@gmail.com'
        ],
        [
            'Authorization' => 'test'
        ])->assertStatus(200)
            ->assertJson([
                'data' => [
                    'username' => 'Chieru16',
                    'email' => 'akebi@gmail.com'
                ]
            ]);

        $newUser = User::where('username', 'Chieru16')->first();
        self::assertNotEquals($oldUser->email, $newUser->email);
    }

    public function testUpdatePasswordSuccess() {
        $this->seed([UserSeeder::class]);
        $oldUser = User::where('username', 'Chieru16')->first();

        $this->patch('/api/users',
        [
            'password' => 'baru'
        ],
        [
            'Authorization' => 'test'
        ])->assertStatus(200)
            ->assertJson([
                'data' => [
                    'username' => 'Chieru16',
                    'email' => 'rifqiraehan86@gmail.com'
                ]
            ]);

        $newUser = User::where('username', 'Chieru16')->first();
        self::assertNotEquals($oldUser->password, $newUser->password);
    }

    public function testUpdateUsernameFailed() {
        $this->seed([UserSeeder::class]);

        $this->patch('/api/users',
        [
            'username' => 'a'.str_repeat('a', 255)
        ],
        [
            'Authorization' => 'test'
        ])->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'username' => ["The username field must not be greater than 255 characters."]
                ]
            ]);
    }

    public function testUpdateEmailFailed() {
        $this->seed([UserSeeder::class]);

        $this->patch('/api/users',
        [
            'email' => 'a'.str_repeat('a', 255)
        ],
        [
            'Authorization' => 'test'
        ])->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'email' => ["The email field must not be greater than 255 characters."]
                ]
            ]);
    }

    public function testLogoutSuccess() {
        $this->seed([UserSeeder::class]);

        $this->post('/api/users/logout',  [], [
            'Authorization' => 'test'
        ])->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);

        $user = User::where('username', 'Chieru16')->first();
        self::assertNull($user->token);
    }

    public function testLogoutFailed() {
        $this->seed([UserSeeder::class]);

        $this->post('/api/users/logout',  [], [
            'Authorization' => 'salah'
        ])->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'message' => ['Unauthorized']
                ]
            ]);
    }
}