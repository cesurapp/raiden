<?php

namespace App\Tests\Setup;

use Ahc\Jwt\JWT;
use App\Admin\Core\Entity\Organization;
use App\Admin\Core\Entity\User;
use App\Admin\Core\Permission\UserType;
use Cesurapp\SwooleBundle\Task\TaskWorker;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Uid\Ulid;

trait AppTrait
{
    public array $server = [];

    /**
     * Set Login Header Params.
     */
    public function login(?User $user = null): self
    {
        if ($user) {
            $token = static::getContainer()->get(JWT::class)->encode(['id' => $user->getId()->toBase32()]);
            $this->server = ['HTTP_AUTHORIZATION' => 'Bearer '.$token];
        }

        return $this;
    }

    /**
     * Create App User.
     */
    public function getAdmin(bool $createOrganization = false, string $password = '123123123'): User
    {
        return $this->getUser($createOrganization, $password)->setType(UserType::ADMIN);
    }

    public function getUser(bool $createOrganization = false, string $password = '123123123'): User
    {
        return (new User())
            ->setOrganization($createOrganization ? (new Organization())->setName('Test Org') : null)
            ->setLanguage('tr')
            ->setEmailApproved(true)
            ->setFirstName('John')
            ->setLastName('Doe')
            ->setPhone(random_int(905400000000, 905499999900))
            ->setPhoneCountry('TR')
            ->setPhoneApproved(true)
            ->setEmail(Ulid::generate().'@test.com')
            ->setEmailApproved(true)
            ->setPassword($password, static::getContainer()->get('security.user_password_hasher'));
    }

    /**
     * Mock TaskWorker.
     */
    public function mockTaskWorker(callable $returnCallback): TaskWorker|MockObject
    {
        $worker = $this->getMockBuilder(TaskWorker::class)
            ->disableOriginalConstructor()
            ->getMock();

        $worker->method('handle')->willReturnCallback($returnCallback);

        if (isset(self::$client)) {
            self::$client->disableReboot();
        }

        self::getContainer()->set(TaskWorker::class, $worker);

        return $worker;
    }
}
