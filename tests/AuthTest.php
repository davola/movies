<?php

namespace App\Tests;

class AuthTest extends AbstractTest
{
    public function testLoginUser1(): void
    {
        $response = $this->getClient()->request('POST', '/api/login_check', ['json' => [
            'username' => 'user1@example.com',
            'password' => 'user1',
        ]]);

        $this->assertResponseIsSuccessful();
        $this->assertObjectHasAttribute('token', json_decode($response->getContent()));
    }
}
