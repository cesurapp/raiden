<?php

namespace App\Admin\Core\Command;

use App\Admin\Core\Entity\User;
use App\Admin\Core\Permission\UserType;
use App\Admin\Core\Repository\UserRepository;
use App\Admin\Core\Service\PermissionManager;
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
    public function __construct(private readonly UserRepository $userRepo, private readonly PermissionManager $permissions)
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
            return sprintf('%s:%s:%s', $u->getId()->toString(), $u->getType()->value, $u->getEmail());
        }, $findUsers), UserType::USER->value));
        $selectedUser = $this->userRepo->find(explode(':', $selectedUser)[0]);

        $roles = $this->permissions->getPermissionsFlatten($selectedUser->getType());
        if ($roles) {
            $selected = $helper->ask($input, $output, (new ChoiceQuestion('Select Role: ', $roles))->setMultiselect(true));
            $selectedUser->setRoles($this->permissions->getPermissionToEnum($selected));
        }
        $this->userRepo->add($selectedUser);

        (new SymfonyStyle($input, $output))->success('Role Changed!');

        return Command::SUCCESS;
    }
}
