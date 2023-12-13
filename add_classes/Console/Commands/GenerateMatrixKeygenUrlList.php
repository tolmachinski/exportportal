<?php

namespace App\Console\Commands;

use App\Bridge\Matrix\Console\MatrixCommand;
use App\Common\Console\Command\Command;
use App\Common\Encryption\MasterKeyAwareTrait;
use Symfony\Component\Console\Input\InputOption;
use ParagonIE\Halite\Asymmetric\Crypto;
use RuntimeException;

class GenerateMatrixKeygenUrlList extends MatrixCommand
{
    use MasterKeyAwareTrait;

    /**
     * {@inheritdoc}
     */
    protected $name = 'matrix:keygen:generate-url';

    /**
     * {@inheritdoc}
     */
    protected $description = 'The command that generates the list of URLs used to spawn E2E matrix keys';

    /**
     * {@inheritdoc}
     */
    protected $help = <<<'HELP'
    The <info>%command.name%</info> command generates the list of matrix E2E keys spawn URLs for user.

        <info>php %command.full_name% <user-id></info>

    To generate URLs for multiple users at once, pass each user ID:

        <info>php %command.full_name% <user-id-1> <user-id-2></info>

    Use the --all option generate URLs for all users from the application:

        <info>php %command.full_name% --all</info>

        <info>php %command.full_name% -a</info>

    Use the --groups option to generate URLs only for users that belongs to the certain group (you must use group ID here):

        <info>php %command.full_name% --all --group=1 --group=2</info>

        <info>php %command.full_name% --all -G 1 -G 2</info>

    Use the --group-alias option to generate URLs only for users that belongs to the certain group (you must use group alias here):

        <info>php %command.full_name% --all --group-alias=buyer --group-alias=seller</info>

        <info>php %command.full_name% --all -A "buyer" -A "seller"</info>

    Use the --status option to generate URLs only for users that have a certain status:

        <info>php %command.full_name% --all --status=new --status=pending</info>

        <info>php %command.full_name% --all -S "new" -S "pending"</info>

    Use the --limit option to indicate the limit of users:

        <info>php %command.full_name% <user-id-1> <user-id-2> --limit=1000</info>

        <info>php %command.full_name% <user-id-1> <user-id-2> -L 1000</info>

    Use the --cycle option to override the export cycle (by default one from ENV is used). <comment>Warning!</comment> This is
    dangerous option and you must use it at your own discretion. Ideally, you must never use it in production mode:

        <info>php %command.full_name% <user-id-1> <user-id-2> --cycle=r0</info>

        <info>php %command.full_name% <user-id-1> <user-id-2> -c r0</info>

    Use the --list option to print the URLs into the console:

        <info>php %command.full_name% <user-id-1> <user-id-2> --list</info>

        <info>php %command.full_name% <user-id-1> <user-id-2> -l</info>

    Use the --output option to indicate the output path for generated file with URLs:

        <info>php %command.full_name% <user-id-1> <user-id-2> --output</info>

        <info>php %command.full_name% <user-id-1> <user-id-2> -o</info>

    Use the --domain option to override the domain for URLs:

        <info>php %command.full_name% <user-id-1> <user-id-2> --domain http://localhost</info>

        <info>php %command.full_name% <user-id-1> <user-id-2> -D http://localhost</info>
    HELP;

    /**
     * {@inheritDoc}
     */
    protected function handle()
    {
        $input = $this->getInput();
        $output = $this->getOutput();
        // Fetch users
        $users = $this->findUsers($input, false, true);
        if (empty($users)) {
            $output->success('No users to generate keys.');

            return Command::SUCCESS;
        }

        // Generate the URL
        $urls = [];
        foreach ($users as $user) {
            $urls[] = \sprintf(
                '%s/matrix_chat/keygen_start?userId=%s&verificationCode=%s',
                \rtrim($this->option('domain'), '/'),
                $user['idu'],
                Crypto::sign($user['matrix_reference']['username'], $this->getMasterKey()->getSecretKey())
            );
        }

        if ($this->option('list')) {
            foreach ($urls as $url) {
                $output->write($url);
                $output->newLine();
            }
        } else {
            $outputPath = $this->option('output');
            $outputDirectory = \realpath(\dirname($outputPath)) ?: \APP_ROOT;
            $outputFile = \basename($outputPath) ?: 'keygen-urls.txt';
            if (!\file_exists($outputDirectory)) {
                \mkdir($outputDirectory, 0755, true);
            }
            if (false === \file_put_contents("{$outputDirectory}/{$outputFile}", \implode("\n", $urls))) {
                throw new RuntimeException('Failed to save the list of URLs into the provided path.');
            }
        }

        return Command::SUCCESS;
    }

    /**
     * {@inheritDoc}
     */
    protected function getOptions()
    {
        return \array_merge(
            [
                ['domain', 'D', InputOption::VALUE_REQUIRED, 'Determines the domain for URLs', \rtrim(__SITE_URL, '/')],
                ['output', 'o', InputOption::VALUE_REQUIRED, 'Write file into provided path', './keygen-urls.txt'],
                ['list', 'l', InputOption::VALUE_NONE, 'List all of the URLs without saving it into the files'],
            ],
            parent::getOptions()
        );
    }
}
