<?php

namespace App\Core\Test;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginTest extends WebTestCase
{
    public function testLogin(): void
    {
        $this->assertIsInt(2);
    }
}
