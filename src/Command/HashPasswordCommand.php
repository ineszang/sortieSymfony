<?php
// src/Command/HashPasswordCommand.php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\Participant;

class HashPasswordCommand extends Command
{
    protected static $defaultName = 'app:hash-password';
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->passwordHasher = $passwordHasher;
    }

    protected function configure()
    {
        $this
            ->setDescription('Hashes a password to be used in the database')
            ->addArgument('password', InputArgument::REQUIRED, 'The password to hash');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $password = $input->getArgument('password');
        $user = new Participant();
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);

        $output->writeln('Hashed Password: ' . $hashedPassword);

        return Command::SUCCESS;
    }
}