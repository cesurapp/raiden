<?php

namespace App\Admin\Core\Command;

use App\Admin\Core\Permission\PermissionManager;
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

#[AsCommand(name: 'user:role', description: 'Change User Role')]
class UserRoleCommand extends Command
{
    public function __construct(private UserRepository $userRepo, private PermissionManager $permissions)
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

        $roles = $this->permissions->getPermissionsFlatten($user->getType());
        if (!$roles) {
            throw new \RuntimeException('No permissions were found for this user type.');
        }

        $selected = $helper->ask($input, $output, (new ChoiceQuestion('Select Role: ', $roles))->setMultiselect(true));
        $user->setRoles($this->permissions->getPermissionToEnum($selected));
        $this->userRepo->add($user);

        (new SymfonyStyle($input, $output))->success('Role Changed!');

        return Command::SUCCESS;
    }
}
