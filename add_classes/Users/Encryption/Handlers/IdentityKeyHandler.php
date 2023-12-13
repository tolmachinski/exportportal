<?php

declare(strict_types=1);

namespace App\Users\Encryption\Handlers;

use App\Common\Encryption\Handlers\AbstractKeyHandler;
use App\Common\Encryption\Storage\IdentifierGeneratorInterface;
use App\Common\Encryption\Storage\KeyPairStorageInterface;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\SignatureKeyPair;
use ParagonIE\HiddenString\HiddenString;

final class IdentityKeyHandler extends AbstractKeyHandler implements IdentityKeyHandlerlerInterface
{
    /**
     * The salt used in key generation.
     *
     * @var HiddenString
     */
    private $keySalt;

    /**
     * The key identifier generator.
     *
     * @var IdentifierGeneratorInterface
     */
    private $identifierGenerator;

    /**
     * Creates instance of the key handler.
     *
     * @param KeyPairStorageInterface $keyStorage
     * @param HiddenString            $salt
     */
    public function __construct(
        KeyPairStorageInterface $keyStorage,
        IdentifierGeneratorInterface $identifierGenerator,
        HiddenString $salt
    ) {
        parent::__construct($keyStorage);

        $this->keySalt = $salt;
        $this->identifierGenerator = $identifierGenerator;
    }

    /**
     * Returns the identity key pair.
     *
     * @param string $userSignature
     *
     * @return SignatureKeyPair
     */
    public function getIdenityKeyPair(string $userSignature): SignatureKeyPair
    {
        return $this->getOrCreateSignatureKeyPair(
            $this->identifierGenerator->createIdentifier(
                'users',
                $this->identifierGenerator->manglePart($userSignature),
                'identity.key'
            ),
            function () use ($userSignature) {
                return KeyFactory::deriveSignatureKeyPair(
                    new HiddenString((string) $userSignature),
                    $this->keySalt->getString()
                );
            }
        );
    }
}
