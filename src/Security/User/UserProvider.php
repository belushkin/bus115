<?php

// src/Security/User/UserProvider.php
namespace Bus115\Security\User;

use Bus115\Entity\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

use Doctrine\ORM\EntityManagerInterface;

class UserProvider implements UserProviderInterface
{

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function loadUserByUsername($username)
    {
        return $this->fetchUser($username);
    }

    public function loadUserByApiKey($apiKey)
    {
        return $this->fetchUser($apiKey);
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->fetchUser($user->getApiKey());
    }

    public function supportsClass($class)
    {
        return User::class === $class;
    }

    private function fetchUser($apiKey)
    {
        // make a call to your webservice here
        $userData = $this->em->getRepository('Bus115\Entity\User')->findOneBy(
            array('apiKey' => $apiKey)
        );

        if ($userData) {
            return $userData;
        }

        throw new UsernameNotFoundException(
            sprintf('Username "%s" does not exist.', $username)
        );
    }
}