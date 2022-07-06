<?php

namespace App\Admin\Core\Command;

use App\Admin\Core\Enum\UserType;
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

#[AsCommand(name: 'user:type', description: 'Change User Type')]
class UserTypeCommand extends Command
{
    public function __construct(private UserRepository $userRepo)
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

        $user
            ->setType(
                UserType::from($helper->ask($input, $output, new ChoiceQuestion('Select Type: ', UserType::values())))
            )
            ->setRoles([]);
        $this->userRepo->add($user);

        (new SymfonyStyle($input, $output))->success('Type Changed!');

        return Command::SUCCESS;
    }
}
