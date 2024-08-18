<?php

namespace App\Admin\Core\Command;

use App\Admin\Core\Entity\User;
use App\Admin\Core\Permission\UserType;
use App\Admin\Core\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\SymfonyQuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'user:create', description: 'User Create')]
class UserCreateCommand extends Command
{
    public function __construct(private UserRepository $userRepo, private UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var SymfonyQuestionHelper $helper */
        $helper = $this->getHelper('question');

        $data = [
            'email' => $helper->ask($input, $output, new Question('Email: ', 'demo@demo.com')),
            'firstName' => $helper->ask($input, $output, new Question('First Name: ', 'Demo')),
            'lastName' => $helper->ask($input, $output, new Question('Last Name: ', 'LastName')),
            'password' => $helper->ask($input, $output, new Question('Password: ', '123123123')),
            'type' => $helper->ask(
                $input,
                $output,
                new ChoiceQuestion('Type: ', UserType::values(), UserType::USER->value)
            ),
        ];

        // Create User
        $user = (new User())
            ->setEmail($data['email'])
            ->setFirstName($data['firstName'])
            ->setLastName($data['lastName'])
            ->setPassword($data['password'], $this->passwordHasher)
            ->setRoles([])
            ->setEmailApproved(true)
            ->setType(UserType::from($data['type']));

        $this->userRepo->add($user);

        $output = new SymfonyStyle($input, $output);
        $output->horizontalTable(['Email', 'First Name', 'Last Name', 'Password', 'Type'], [
            [
                $user->getEmail(),
                $user->getFirstName(),
                $user->getLastName(),
                $data['password'],
                $user->getType()->name,
            ],
        ]);
        $output->success('User Created!');

        return Command::SUCCESS;
    }
}
