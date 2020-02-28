<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Console\Arguments;
use Cake\Console\Command;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Mailer\MailerAwareTrait;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validation;

/**
 * EmailTest command.
 */
class EmailTestCommand extends Command
{
    use MailerAwareTrait;

    /**
     * Hook method for defining this command's option parser.
     *
     * @see https://book.cakephp.org/3.0/en/console-and-shells/commands.html#defining-arguments-and-options
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser)
    {
        $parser = parent::buildOptionParser($parser);

        $parser->addArguments([
            'email' => [
                'help' => 'The email address to send a test message to',
                'required' => true,
            ],
        ]);

        return $parser;
    }

    /**
     * Implement this method with your command's logic.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return null|int The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $email = $args->getArgument('email');

        if (!Validation::email($email)) {
            $io->error('That doesn\'t appear to be a valid email address.');

            return Command::CODE_ERROR;
        }

        $choice = $io->askChoice('\'s\'end immediately or add to job \'q\'ueue?', ['s', 'q'], 's');

        if ($choice === 's') {
            return $this->sendImmediately($email);
        }

        return $this->enqueueMessage($email);
    }

    /**
     * Immediately sends a test message
     *
     * @param string $email Email address
     * @return null
     */
    private function sendImmediately(string $email)
    {
        $this->getMailer('Test')->send('test', [$email]);

        return null;
    }

    /**
     * Adds a job to the job queue
     *
     * @param string $email Email address
     * @return null
     */
    private function enqueueMessage(string $email)
    {
        /** @var \Queue\Model\Table\QueuedJobsTable $queuedJobs */
        $queuedJobs = TableRegistry::getTableLocator()->get('Queue.QueuedJobs');
        $queuedJobs->createJob(
            'EmailTest',
            ['email' => $email],
            ['reference' => $email],
        );

        return null;
    }
}
