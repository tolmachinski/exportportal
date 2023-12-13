<?php

namespace App\Console\Commands;

use App\Common\Console\Command\Command;
use App\Common\Validation\ConstraintList;
use App\Common\Validation\Constraints\Ip;
use App\Common\Validation\NestedValidationData;
use App\Common\Validation\ValidationException;
use App\Common\Validation\Validator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MaintenanceCommand extends Command
{
    protected $name = 'app:maintenance';

    protected $description = 'The command that enables the maintenance.';

    protected $argumentsList = array(
        array('switch', InputArgument::REQUIRED, 'The flag that indicates if the maintenance enabled or disabled.'),
        array('trusted-proxies', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'The list of the IP addresses that are allowed to bypass the maintenance mode.'),
    );

    protected $optionsList = array(
        array('root-dir', 'r', InputOption::VALUE_OPTIONAL, 'The root directory where lies the index file.', null),
    );

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (null === $input->getArgument('switch')) {
            $switch = $this->ask(
                $this->getDefinition()->getArgument('switch')->getDescription()
            );
            $input->setArgument('switch', $switch);
        }

        if (isset($switch) && $switch) {
            $isFirstProxy = true;
            $trustedProxies = $input->getArgument('trusted-proxies');
            while (true) {
                $newProxy = $this->askForNewTrustedProxy(new SymfonyStyle($input, $output), $isFirstProxy);
                $isFirstProxy = false;
                if (null === $newProxy) {
                    break;
                }

                if (is_array($newProxy)) {
                    $trustedProxies = array_merge($trustedProxies, $newProxy);
                } else {
                    $trustedProxies[] = $newProxy;
                }
            }

            $input->setArgument('trusted-proxies', $trustedProxies);
        }
    }

    protected function handle()
    {
        $rootDir = $this->option('root-dir');
        $rootDir = $rootDir ? realpath(APP_ROOT . $rootDir) : realpath(APP_ROOT);
        $htaccessFile = "{$rootDir}/.htaccess";
        $htaccessOrigFile = "{$rootDir}/.htaccess.orig";
        $htaccessContent = $htaccessOrigContent = file_exists($htaccessOrigFile) ? file_get_contents($htaccessOrigFile) : file_get_contents($htaccessFile);
        $modeValue = $this->argument('switch');
        if ('0' !== $modeValue && '1' !== $modeValue) {
            return $this->error('Enter value 0 or 1');
        }

        $isModeEnabled = filter_var($modeValue, FILTER_VALIDATE_BOOLEAN);
        $trustedProxies = $this->argument('trusted-proxies');
        if ($isModeEnabled && empty($trustedProxies)) {
            $trustedProxies = $this->getDefaultTrustedProxies();
            $this->validateTrustedProxies($trustedProxies);
        }

        if ($isModeEnabled) {
            $htaccessContent = $this->getMaintenanceScript($trustedProxies) . $htaccessOrigContent;
            file_put_contents($htaccessOrigFile, $htaccessOrigContent);
        } else {
            if (file_exists($htaccessOrigFile)) {
                unlink($htaccessOrigFile);
            }
        }
        file_put_contents($htaccessFile, $htaccessContent);

        $this->warn($isModeEnabled ? 'The maitenance mode is enabled now.' : 'The maitenance mode is disabled now.');
    }

    private function getMaintenanceScript(array $trustedProxies = array())
    {
        $proxyRules = null;
        if (!empty($trustedProxies)) {
            $proxyRules = implode("\n\t", array_map(
                function ($proxy) {
                    if (false !== strpos($proxy, '/')) {
                        return "RewriteCond expr \"-R '{$proxy}'\"";
                    }

                    return "RewriteCond %{REMOTE_ADDR} !{$proxy}";
                },
                $trustedProxies
            ));
        }

        return <<<EOPHP
        <IfModule mod_rewrite.c>
            RewriteEngine On
            #    RewriteCond %{REMOTE_ADDR} !^(89\.28\.49\.94|192\.168\.1\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)) [OR]
            #    RewriteCond %{HTTP:X-Forwarded-For} !^(89\.28\.49\.94|192\.168\.1\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?))
            RewriteCond expr "!(-R '192.168.1.0/24' && req_novary('X-Forwarded-For') -ipmatch '192.168.1.0/24')"
            RewriteCond expr "!(-R '89.28.49.94' && req_novary('X-Forwarded-For') -ipmatch '89.28.49.94')"
            RewriteRule ^\$ maintenance/ [L]
            RewriteRule (.*) maintenance/\$1 [L]
        </IfModule>


        EOPHP;
    }

    private function getDefaultTrustedProxies()
    {
        if (!isset($_ENV['MAINTENANCE_BYPASS_PROXIES'])) {
            return array();
        }

        return array_filter(
            array_map(
                'trim',
                explode(',', $_ENV['MAINTENANCE_BYPASS_PROXIES'])
            )
        );
    }

    private function validateTrustedProxies(array $trustedProxies = array())
    {
        foreach ($trustedProxies as $proxy) {
            try {
                // $validator = new Validator();
                // $validator->assert(
                //     new NestedValidationData(array(Ip::IP_ALIAS => $proxy)),
                //     new ConstraintList(array(new Ip()))
                // );
            } catch (ValidationException $exception) {
                // throw new \InvalidArgumentException(\sprintf("The '%s' is not a valid IP address.", $proxy), 0, $exception);
            }
        }
    }

    private function askForNewTrustedProxy(SymfonyStyle $io, $isFirstProxy)
    {
        if ($isFirstProxy) {
            $default_proxies = $this->getDefaultTrustedProxies();
            $questionText = 'Trusted proxy (press <return> to stop adding roles)';
        } else {
            $default_proxies = null;
            $questionText = 'Add another trusted proxy? Enter the trusted proxy name (or press <return> to stop adding proxies)';
        }

        $trustedProxy = $io->ask(
            $questionText,
            is_array($default_proxies) ? implode(', ', $default_proxies) : null,
            function ($proxies) {
                // allow it to be empty
                if (!$proxies) {
                    return $proxies;
                }
                $proxies = array_filter(array_map('trim', explode(',', $proxies)));
                $this->validateTrustedProxies($proxies);

                return $proxies;
            }
        );

        if (!$trustedProxy) {
            return null;
        }

        return $trustedProxy;
    }
}
