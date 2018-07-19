<?php

// src/Security/User/UserProvider.php
namespace Bus115\Security\User;

use Bus115\Entities\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class UserProvider implements UserProviderInterface
{
    public function loadUserByUsername($username)
    {
        return $this->fetchUser($username);
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->fetchUser($username);
    }

    public function supportsClass($class)
    {
        return User::class === $class;
    }

    private function fetchUser($username)
    {
        // make a call to your webservice here
        $userData = 2;//...
        // pretend it returns an array on success, false if there is no user

        if ($userData) {
            $password = '...';

            // ...

            return new User($username, $password, $salt, $roles);
        }

        throw new UsernameNotFoundException(
            sprintf('Username "%s" does not exist.', $username)
        );
    }
}