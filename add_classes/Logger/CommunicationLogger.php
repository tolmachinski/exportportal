<?php

declare(strict_types=1);

namespace App\Logger;

use App\Common\Exceptions\QueryException;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use TinyMVC_PDO;

class CommunicationLogger extends AbstractProcessingHandler
{
    /**
     * The pdo object for connecting.
     */
    private $pdo;

    /**
     * Constructing the CommunicationLogger from AbstractProcessingHandler.
     *
     * @param string      $table  - the database table name
     * @param TinyMVC_PDO $pdo    - the pdo object for connecting
     * @param int         $level  - the error number
     * @param bool        $bubble - whether the messages that are handled can bubble up the stack or not
     */
    public function __construct(string $table, TinyMVC_PDO $pdo, $level = Logger::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);

        $this->pdo = $pdo;
        $this->table = $table;
    }

    /**
     * Method used for inserting the log into the database.
     *
     * @param array $record - the array with the data for inserting. Need to have such a structure:
     *                      [
     *                      'message'     => 'Log message',
     *                      'id_resource' => 123,
     *                      'id_user'     => 100,
     *                      'type'        => 'action' (or 'notification' or 'email'),
     *                      'context'     => [] (array with all the extra details that will be saved as json)
     *                      ]
     */
    protected function write(array $record): void
    {
        try {
            $contex = $record['context'] ?? [];
            $this->pdo->insert(
                $this->table,
                [
                    'type'        => $contex['type'],
                    'message'     => $record['message'],
                    'context'     => json_encode($contex['details']),
                    'id_resource' => (int) ($contex['id_resource'] ?? null),
                    'id_user'     => (int) ($contex['id_user'] ?? null),
                ]
            );
        } catch (\Exception $e) {
            QueryException::executionFailed($this->pdo, $e);
        }
    }
}
