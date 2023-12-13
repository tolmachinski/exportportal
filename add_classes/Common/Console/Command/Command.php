<?php

namespace App\Common\Console\Command;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * An extension of the Symfony's Command class.
 * Inspired by Laravel Command class.
 */
class Command extends SymfonyCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description;

    /**
     * The console command help.
     *
     * @var string
     */
    protected $help;

    /**
     * Indicates if the command must be hidden.
     *
     * @var bool
     */
    protected $hidden = false;

    /**
     * The default verbosity of output commands.
     *
     * @var int
     */
    protected $verbosity = OutputInterface::VERBOSITY_NORMAL;

    /**
     * The mapping between human readable verbosity levels and Symfony's OutputInterface.
     *
     * @var array
     */
    protected $verbosityMap = [
        'v'       => OutputInterface::VERBOSITY_VERBOSE,
        'vv'      => OutputInterface::VERBOSITY_VERY_VERBOSE,
        'vvv'     => OutputInterface::VERBOSITY_DEBUG,
        'quiet'   => OutputInterface::VERBOSITY_QUIET,
        'debug'   => OutputInterface::VERBOSITY_DEBUG,
        'verbose' => OutputInterface::VERBOSITY_VERBOSE,
        'normal'  => OutputInterface::VERBOSITY_NORMAL,
    ];

    /**
     * The console command arguements.
     *
     * @var array[]
     */
    protected $argumentsList = [];

    /**
     * The console command options.
     *
     * @var array[]
     */
    protected $optionsList = [];

    /**
     * The input handler.
     *
     * @var InputInterface
     */
    private $input;

    /**
     * The output handler.
     *
     * @var SymfonyStyle
     */
    private $output;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct($this->name);

        $this->setHelp($this->help ?? '');
        $this->setDescription($this->description ?? '');
        $this->setHidden($this->isHidden());
        $this->configureCommand();
    }

    /**
     * @return null|string The default command name or null when no default name is set
     */
    public static function getDefaultName()
    {
        $class = static::class;
        $reflector = new \ReflectionClass($class);

        return $reflector->getDefaultProperties()['name'] ?? parent::getDefaultName();
    }

    /**
     * Call another console command.
     *
     * @param string $command
     *
     * @return mixed
     */
    public function call($command, array $arguments = [])
    {
        $arguments['command'] = $command;

        return $this->getApplication()->find($command)->run(
            $this->createInput($arguments),
            $this->output
        );
    }

    /**
     * Call another console command silently.
     *
     * @param string $command
     *
     * @return int
     */
    public function callSilent($command, array $arguments = [])
    {
        $arguments['command'] = $command;

        return $this->getApplication()->find($command)->run(
            $this->createInput($arguments),
            new NullOutput()
        );
    }

    /**
     * Determine if the given argument is present in the command input.
     *
     * @param int|string $name
     *
     * @return bool
     */
    public function hasArgument($name)
    {
        return $this->input->hasArgument($name);
    }

    /**
     * Get the value of a command argument.
     *
     * @param null|string $key
     *
     * @return null|mixed
     */
    public function argument($key)
    {
        if (null === $key) {
            return $this->input->getArguments();
        }

        return $this->input->getArgument($key);
    }

    /**
     * Get the value of a command argumens.
     *
     * @return array
     */
    public function arguments()
    {
        return $this->input->getArguments();
    }

    /**
     * Determine if the given option is present in the command input.
     *
     * @param int|string $name
     *
     * @return bool
     */
    public function hasOption($name)
    {
        return $this->input->hasOption($name);
    }

    /**
     * Get the value of a command option.
     *
     * @param null|string $key
     *
     * @return null|mixed
     */
    public function option($key)
    {
        if (null === $key) {
            return $this->input->getOptions();
        }

        return $this->input->getOption($key);
    }

    /**
     * Get the value of a command options.
     *
     * @return array
     */
    public function options()
    {
        return $this->input->getOptions();
    }

    /**
     * Write a string as standard output.
     *
     * @param string          $string
     * @param string          $style
     * @param null|int|string $verbosity
     */
    public function line($string, $style = null, $verbosity = null)
    {
        $this->output->writeln(
            null !== $style ? "<{$style}>{$string}</{$style}>" : $string,
            $this->normalizeVerbosity($verbosity)
        );
    }

    /**
     * Write a string as information output.
     *
     * @param string          $string
     * @param null|int|string $verbosity
     */
    public function info($string, $verbosity = null)
    {
        $this->line($string, 'info', $verbosity);
    }

    /**
     * Write a string as comment output.
     *
     * @param string          $string
     * @param null|int|string $verbosity
     */
    public function comment($string, $verbosity = null)
    {
        $this->line($string, 'comment', $verbosity);
    }

    /**
     * Write a string as question output.
     *
     * @param string          $string
     * @param null|int|string $verbosity
     */
    public function question($string, $verbosity = null)
    {
        $this->line($string, 'question', $verbosity);
    }

    /**
     * Write a string as error output.
     *
     * @param string          $string
     * @param null|int|string $verbosity
     */
    public function error($string, $verbosity = null)
    {
        $this->line($string, 'error', $verbosity);
    }

    /**
     * Write a string as warning output.
     *
     * @param string          $string
     * @param null|int|string $verbosity
     */
    public function warn($string, $verbosity = null)
    {
        if (!$this->output->getFormatter()->hasStyle('warning')) {
            $this->output->getFormatter()->setStyle('warning', new OutputFormatterStyle('yellow'));
        }

        $this->line($string, 'warning', $verbosity);
    }

    /**
     * Write a string in an alert box.
     *
     * @param string $string
     */
    public function alert($string)
    {
        $length = mb_strlen(strip_tags($string)) + 12;

        $this->comment(str_repeat('*', $length));
        $this->comment("*     {$string}     *");
        $this->comment(str_repeat('*', $length));
        $this->output->writeln('');
    }

    /**
     * Confirm a question with the user.
     *
     * @param string $question
     * @param bool   $default
     *
     * @return bool
     */
    public function confirm($question, $default = false)
    {
        return $this->output->confirm($question, $default);
    }

    /**
     * Prompt the user for input.
     *
     * @param string      $question
     * @param null|string $default
     *
     * @return mixed
     */
    public function ask($question, $default = null)
    {
        return $this->output->ask($question, $default);
    }

    /**
     * Prompt the user for input with auto completion.
     *
     * @param string      $question
     * @param null|string $default
     *
     * @return mixed
     */
    public function anticipate($question, array $choices, $default = null)
    {
        $question = new Question($question, $default);
        $question->setAutocompleterValues($choices);

        return $this->output->askQuestion($question);
    }

    /**
     * Prompt the user for input but hide the answer from the console.
     *
     * @param string $question
     * @param bool   $fallback
     *
     * @return mixed
     */
    public function secret($question, $fallback = true)
    {
        $question = new Question($question);
        $question->setHidden(true)->setHiddenFallback($fallback);

        return $this->output->askQuestion($question);
    }

    /**
     * Give the user a single choice from an array of answers.
     *
     * @param string      $question
     * @param null|string $default
     * @param null|mixed  $attempts
     * @param null|bool   $multiple
     *
     * @return string
     */
    public function choice($question, array $choices, $default = null, $attempts = null, $multiple = null)
    {
        $question = new ChoiceQuestion($question, $choices, $default);
        $question->setMaxAttempts($attempts)->setMultiselect($multiple);

        return $this->output->askQuestion($question);
    }

    /**
     * Format input to textual table.
     *
     * @param array  $headers
     * @param array  $rows
     * @param string $tableStyle
     */
    public function table($headers, $rows, $tableStyle = 'default', array $columnStyles = [])
    {
        $table = new Table($this->output);
        $table->setHeaders((array) $headers)->setRows((array) $rows)->setStyle($tableStyle);
        foreach ($columnStyles as $columnIndex => $columnStyle) {
            $table->setColumnStyle($columnIndex, $columnStyle);
        }
        $table->render();
    }

    /**
     * Get the input handler.
     *
     * @return \Symfony\Component\Console\Input\InputInterface
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Get the output handler.
     *
     * @return \Symfony\Component\Console\Style\SymfonyStyle
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * {@inheritdoc}
     */
    public function run(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = new SymfonyStyle($input, $output);

        return parent::run(
            $this->input,
            $this->output
        );
    }

    /**
     * Configure the command arguemtns and options.
     */
    protected function configureCommand()
    {
        foreach ($this->getArguments() as $argument) {
            $this->addArgument(...$argument);
        }
        foreach ($this->getOptions() as $option) {
            $this->addOption(...(is_callable($option) ? $option() : $option));
        }
    }

    /**
     * Get console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return $this->argumentsList;
    }

    /**
     * Get console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return $this->optionsList;
    }

    /**
     * Set the verbosity level.
     *
     * @param int|string $level
     */
    protected function setVerbosity($level)
    {
        $this->verbosity = $this->normalizeVerbosity($level);
    }

    /**
     * Get the verbosity level in terms of Symfony's OutputInterface level.
     *
     * @param null|int|string $level
     *
     * @return int
     */
    protected function normalizeVerbosity($level = null)
    {
        if (isset($this->verbosityMap[$level])) {
            $level = $this->verbosityMap[$level];
        } elseif (!is_int($level)) {
            $level = $this->verbosity;
        }

        return $level;
    }

    /**
     * Execute the console command.
     *
     * @return null|int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->handle() ?? SymfonyCommand::SUCCESS;
    }

    /**
     * Create an input instance from the given arguments.
     *
     * @return \Symfony\Component\Console\Input\ArrayInput
     */
    protected function createInput(array $arguments)
    {
        $input = new ArrayInput($arguments);
        if ($input->hasParameterOption(['--no-interaction'], true)) {
            $input->setInteractive(false);
        }

        return $input;
    }

    /**
     * Handle the command.
     *
     * @throws \LogicException if method is not implemented
     *
     * @return null|int null or 0 if everything went fine, or an error code
     */
    protected function handle()
    {
        throw new \LogicException('You must override the handle() method in the concrete command class.');
    }

    /**
     * Ensures that argument is present.
     */
    protected function ensureHasArgument(InputInterface $input, string $argumentName, ?string $questionText = null, ?string $default = null): void
    {
        if (empty($argument = $input->getArgument($argumentName))) {
            $argument = $this->ask(
                $questionText ?? $this->getDefinition()->getArgument($argumentName)->getDescription(),
                $default ?? $this->getDefinition()->getArgument($argumentName)->getDefault() ?? null
            );

            $input->setArgument($argumentName, $argument);
        }
    }

    /**
     * Ensures that option is present.
     */
    protected function ensureHasOption(InputInterface $input, string $optionName, ?string $questionText = null, ?string $default = null): void
    {
        if (empty($option = $input->getOption($optionName))) {
            $option = $this->ask(
                $questionText ?? $this->getDefinition()->getOption($optionName)->getDescription(),
                $default ?? $this->getDefinition()->getOption($optionName)->getDefault() ?? null
            );

            $input->setOption($optionName, $option);
        }
    }

    /**
     * Ensures that at least one option from lsit is present.
     */
    protected function ensureHasOptionFromList(
        InputInterface $input,
        string $optionName,
        ?string $firstQuestionText = null,
        ?string $questionText,
        ?int $limit = null
    ): void {
        $definition = $this->getDefinition()->getOption($optionName);
        if (!empty($option = $input->getOption($optionName)) || !$definition->isArray() || !$definition->acceptValue()) {
            return;
        }

        $tick = 0;
        $isFirst = true;
        $options = [];
        $isRequired = $definition->isValueRequired();
        $questionText = $questionText ?? $definition->getDescription();
        $firstQuestionText = $firstQuestionText ?? $definition->getDescription();

        if (null !== $limit) {
            while ($tick < $limit) {
                $option = $this->ask($isFirst ? $firstQuestionText : $questionText);
                if ($isRequired && null === $option) {
                    continue;
                }

                $options[] = $option;
                $isFirst = false;
                ++$tick;
            }
        } else {
            if ($isRequired) {
                while ($isFirst) {
                    if (null === ($option = $this->ask($firstQuestionText))) {
                        continue;
                    }

                    $options[] = $option;
                    $isFirst = false;
                }
            }

            while (null !== ($option = $this->ask($isFirst ? $firstQuestionText : $questionText))) {
                $options[] = $option;
                $isFirst = false;
            }
        }

        $input->setOption($optionName, $options);
    }

    /**
     * Ensures that at least one option from list is present.
     */
    protected function ensureHasArgumentFromList(
        InputInterface $input,
        string $argumentName,
        ?string $firstQuestionText = null,
        ?string $questionText,
        ?int $limit = null
    ): void {
        $definition = $this->getDefinition()->getArgument($argumentName);
        if (!empty($option = $input->getArgument($argumentName)) || !$definition->isArray()) {
            return;
        }

        $tick = 0;
        $isFirst = true;
        $arguments = [];
        $isRequired = $definition->isRequired();
        $questionText = $questionText ?? $definition->getDescription();
        $firstQuestionText = $firstQuestionText ?? $definition->getDescription();

        if (null !== $limit) {
            while ($tick < $limit) {
                $option = $this->ask($isFirst ? $firstQuestionText : $questionText);
                if ($isRequired && null === $option) {
                    continue;
                }

                $arguments[] = $option;
                $isFirst = false;
                ++$tick;
            }
        } else {
            if ($isRequired) {
                while ($isFirst) {
                    if (null === ($option = $this->ask($firstQuestionText))) {
                        continue;
                    }

                    $arguments[] = $option;
                    $isFirst = false;
                }
            }

            while (null !== ($option = $this->ask($isFirst ? $firstQuestionText : $questionText))) {
                $arguments[] = $option;
                $isFirst = false;
            }
        }

        $input->setArgument($argumentName, $arguments);
    }
}
