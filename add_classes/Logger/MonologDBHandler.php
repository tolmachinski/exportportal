<?php

declare(strict_types=1);

namespace App\Logger;

use App\Common\Exceptions\QueryException;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use TinyMVC_PDO;

class MonologDBHandler extends AbstractProcessingHandler
{
    private $pdo;

    public function __construct(string $table, TinyMVC_PDO $pdo, $level = Logger::ERROR, bool $bubble = true)
    {
        parent::__construct($level, $bubble);

        $this->pdo = $pdo;
        $this->table = $table;
    }

    protected function write(array $record): void
    {
        try {
            $this->pdo->insert(
                $this->table,
                [
                    'message'    => $record['message'],
                    'context'    => json_encode($record['context']),
                    'level'      => $record['level'],
                    'level_name' => $record['level_name'],
                ]
            );
        } catch (\Throwable $th) {
            throw QueryException::executionFailed($this->pdo, $th);
        }
    }
}
