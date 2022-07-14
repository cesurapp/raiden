<?php

namespace App\Admin\Core\Command;

use App\Admin\Core\Repository\UserRepository;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\SymfonyQuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'user:password', description: 'Change User Password')]
class UserPasswordCommand extends Command
{
    public function __construct(private UserRepository $userRepo, private UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var SymfonyQuestionHelper $helper */
        $helper = $this->getHelper('question');

        $user = $helper->ask($input, $output, new Question('Find User Email|Phone: ', 'demo@demo.com'));
        if (!$user = $this->userRepo->loadUserByIdentifier($user)) {
            throw new EntityNotFoundException('User not found!');
        }
        $user->setPassword(
            $helper->ask($input, $output, new Question('Password: ', '123123')),
            $this->passwordHasher
        );
        $this->userRepo->add($user);

        (new SymfonyStyle($input, $output))->success('Password Changed!');

        return Command::SUCCESS;
    }
}
