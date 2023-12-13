<?php

namespace App\Common\Traits;

use App\Common\Contracts\HmacAccessDefinitionsInterface;

trait HmacAccessTrait
{
    /**
     * Guesses valid user id from provided variants.
     *
     * @param array       $variants
     * @param null|string $token
     */
    private function guessUserId(array $variants = array(), $token = null)
    {
        $user_id = $this->getLoggedUserId();
        if (
            null === $user_id
            && null !== $token
            && null !== ($context = $this->getHashContext())
            && !empty($variants)
        ) {
            foreach ($variants as $id) {
                if (hash_equals($token, hash_hmac(HmacAccessDefinitionsInterface::ALGORITHM, $context, (int) $id))) {
                    return (int) $id;
                }
            }
        }

        return $user_id;
    }

    /**
     * Returns the logged user ID (if exists).
     *
     * @return null|int
     */
    private function getLoggedUserId()
    {
        return logged_in() ? (int) privileged_user_id() : null;
    }

    /**
     * Returns the hash context.
     *
     * @return mixed
     */
    private function getHashContext()
    {
        return session()->{HmacAccessDefinitionsInterface::CONTEXT};
    }

    /**
     * Returns the hash context.
     *
     * @return mixed
     */
    private function getHashSecret()
    {
        return session()->{HmacAccessDefinitionsInterface::SECRET};
    }
}
