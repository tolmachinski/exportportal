<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Email_Hash model.
 */
class Email_Hash_Model extends Model
{
    /**
     * The name of the "created at" column.
     *
     * @var null|string
     */
    protected const CREATED_AT = 'date_checked';

    /**
     * {@inheritdoc}
     */
    protected string $table = 'email_hash';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id';

    /**
     * {@inheritdoc}
     */
    protected bool $timestamps = true;

    /**
     * {@inheritdoc}
     */
    protected array $guarded = [
        'id',
        self::CREATED_AT,
    ];

    /**
     * {@inheritdoc}
     */
    protected array $casts = [
        'id'                    => Types::INTEGER,
        'rechecked'             => Types::INTEGER,
        'email_status_response' => Types::JSON,
    ];

    /**
     * {@inheritdoc}
     */
    protected array $nullable = [
        'email_status',
        'email_status_response',
        'to_verify',
    ];

    /**
     * Get bad emails.
     */
    public function get_bad_emails(array $badEmailsList): array
    {
        $emailAndHash = [];
        foreach ($badEmailsList as $bad) {
            $emailAndHash[getEncryptedEmail($bad)] = $bad;
        }

        $emails = $this->findRecords(
            null,
            $this->getTable(),
            null,
            [
                'conditions' => [
                    'email_status' => 'Bad',
                    'email_hashes' => array_keys($emailAndHash),
                ],
            ]
        );
        if (empty($emails)) {
            return [];
        }

        return array_map(fn ($hashedBad) => $emailAndHash[$hashedBad], array_column($emails, 'email_hash'));
    }

    /**
     * Scopes query by email status.
     */
    protected function scopeEmailStatus(QueryBuilder $builder, string $status): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'email_status',
                $builder->createNamedParameter($status, ParameterType::STRING, $this->nameScopeParameter('emailStatus'))
            )
        );
    }

    /**
     * Scopes query by email hash string.
     */
    protected function scopeEmailHash(QueryBuilder $builder, string $email_hash): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'email_hash',
                $builder->createNamedParameter($email_hash, ParameterType::STRING, $this->nameScopeParameter('emailHash'))
            )
        );
    }

    /**
     * Scopes query by email hashes.
     */
    protected function scopeEmailHashes(QueryBuilder $builder, array $hashes): void
    {
        if (empty($hashes)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in(
                'email_hash',
                array_map(
                    fn (int $index, $hashe) => $builder->createNamedParameter(
                        (string) $hashe,
                        ParameterType::STRING,
                        $this->nameScopeParameter("emailHash{$index}")
                    ),
                    array_keys($hashes),
                    $hashes
                )
            )
        );
    }

    /**
     * Scopes query by to_verify is null.
     */
    protected function scopeEmailStatusNotNull(QueryBuilder $builder): void
    {
        $builder->andWhere(
            $builder->expr()->isNotNull('`email_status`')
        );
    }

    /**
     * Scopes query by to_verify is null.
     */
    protected function scopeToVerifyNull(QueryBuilder $builder): void
    {
        $builder->andWhere(
            $builder->expr()->isNull('`to_verify`')
        );
    }

    /**
     * Scopes query by to_verify is not null.
     */
    protected function scopeToVerifyNotNull(QueryBuilder $builder): void
    {
        $builder->andWhere(
            $builder->expr()->isNotNull('`to_verify`')
        );
    }

    /**
     * Scopes query by to_verify is not null.
     */
    protected function scopeRecheckedNotMoreThan(QueryBuilder $builder, int $max): void
    {
        $builder->andWhere(
            $builder->expr()->lt(
                '`rechecked`',
                $builder->createNamedParameter($max, ParameterType::INTEGER, $this->nameScopeParameter('rechecked'))
            )
        );
    }

    // public function hash_users(){
    //     $this->db->select('email, email_status, email_status_response');
    //     $this->db->from('users');
    //     $all_emails = $this->db->query_all();

    //     dump(array_unique(array_column($all_emails, 'email')));

    //     $result = array_reverse(array_values(array_column(
    //         array_reverse($all_emails),
    //         null,
    //         'email'
    //     )));

    //     foreach($result as $key => $em){
    //         $result[$key]['email_hash'] = getEncryptedEmail($em['email']);
    //         //$result[$key]['to_verify'] = $em['email'];
    //         unset($result[$key]['email']);
    //     }

    //     //dump($result);
    //     $this->insertMany($result);
    // }
}

// End of file email_hash_model.php
// Location: /tinymvc/myapp/models/email_hash_model.php
