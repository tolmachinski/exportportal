<?php

namespace App\Plugins\EPDocs\Credentials;

use App\Plugins\EPDocs\Credentials;

class JwtCredentials extends Credentials
{
    /**
     * The username.
     *
     * @var string
     */
    private $username;

    /**
     * The password.
     *
     * @var string
     */
    private $password;

    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Retrieve an external iterator.
     *
     * @throws \Exception on failure
     *
     * @return \Traversable
     */
    public function getIterator()
    {
        yield 'username' => $this->username;
        yield 'password' => $this->password;
    }
}
