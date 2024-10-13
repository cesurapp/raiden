<?php

namespace App\Admin\Core\Command;

use App\Admin\Core\Entity\User;
use App\Admin\Core\Permission\UserType;
use App\Admin\Core\Repository\UserRepository;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\SymfonyQuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
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

        $username = $helper->ask($input, $output, new Question('Find User Email|Phone: ', 'demo@demo.com'));
        $findUsers = $this->userRepo->findBy(['email' => $username]);
        if (!$findUsers) {
            throw new EntityNotFoundException('User not found!');
        }

        $selectedUser = $helper->ask($input, $output, new ChoiceQuestion('Type: ', array_map(static function (User $u) {
            return sprintf('%s:%s:%s', $u->getId()->toBase32(), $u->getType()->value, $u->getEmail());
        }, $findUsers), UserType::USER->value));

        $selectedUser = $this->userRepo->find(explode(':', $selectedUser)[0]);
        $selectedUser->setPassword(
            $helper->ask($input, $output, new Question('Password: ', '123123')),
            $this->passwordHasher
        );
        $this->userRepo->add($selectedUser);

        (new SymfonyStyle($input, $output))->success('Password Changed!');

        return Command::SUCCESS;
    }
}
