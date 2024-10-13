<?php

namespace App\Admin\Core\Controller;

use App\Admin\Core\Dto\CredentialsDto;
use App\Admin\Core\Dto\CredentialsOtpDto;
use App\Admin\Core\Entity\User;
use App\Admin\Core\Enum\OtpType;
use App\Admin\Core\Repository\OtpKeyRepository;
use App\Admin\Core\Repository\UserRepository;
use Cesurapp\ApiBundle\AbstractClass\ApiController;
use Cesurapp\ApiBundle\Response\ApiResponse;
use Cesurapp\ApiBundle\Thor\Attribute\Thor;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class CredentialsController extends ApiController
{
    #[Thor(
        stack: 'Approve Credentials',
        title: 'Change or Approve Email|Phone Request',
        dto: CredentialsDto::class,
        order: 1,
    )]
    #[Route(path: '/v1/main/credentials', methods: ['PUT'])]
    public function request(#[CurrentUser] User $user, CredentialsDto $dto, OtpKeyRepository $otpKeyRepo, UserRepository $userRepo): ApiResponse
    {
        $dto->setProp('id', $user->getId())->validate();

        $username = $dto->validated('email') ?? $dto->validated('phone');

        if (in_array(strtolower($username), [strtolower($user->getEmail()), $user->getPhone()], false)) {
            /*
             * Same Username
             * Check Approve Status and Send Code
             */
            if ($user->isEmailApproved() && $user->isPhoneApproved()) {
                throw $this->createAccessDeniedException('Your account has been verified.');
            }
        } elseif ($userRepo->loadUserByIdentifier($username)) {
            /*
             * Different Username
             * Check Same Username and Send Code
             */
            throw $this->createAccessDeniedException('This value is currently in use.');
        }

        $otpKeyRepo->create($user, OtpType::CREDENTIALS, $username, $dto->validated('phone_country'));

        return ApiResponse::create()->addMessage('One-time approve code has been sent');
    }

    #[Thor(
        stack: 'Approve Credentials',
        title: 'Change or Approve Email|Phone',
        dto: CredentialsOtpDto::class,
        order: 2,
    )]
    #[Route(path: '/v1/main/credentials', methods: ['POST'])]
    public function approve(#[CurrentUser] User $user, CredentialsOtpDto $dto, OtpKeyRepository $otpRepo): ApiResponse
    {
        $dto->setProp('id', $user->getId())->validate();

        $username = $dto->validated('email') ?? $dto->validated('phone');

        if (!$otpRepo->check($user, OtpType::CREDENTIALS, $dto->validated('otp_key'), $username)) {
            throw new BadCredentialsException('Wrong OTP key!', 403);
        }

        // Update
        if (is_numeric($username)) {
            $user->setPhone($username)
                ->setPhoneCountry($dto->validated('phone_country'))
                ->setPhoneApproved(true);
        } else {
            $user->setEmail($username)->setEmailApproved(true);
        }

        $otpRepo->add($user);

        return ApiResponse::create()->addMessage('Verification completed');
    }
}
