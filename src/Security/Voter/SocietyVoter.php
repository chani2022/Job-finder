<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\User;

final class SocietyVoter extends Voter
{

    public const VIEW = 'SOCIETY_VIEW';
    public const EDIT = 'SOCIETY_EDIT';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::VIEW, self::EDIT])
            && $subject instanceof \App\Entity\Society;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User */
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        $authorized = false;

        switch ($attribute) {
            case self::VIEW:
                if (
                    in_array('ROLE_SUPER_ADMIN', $user->getRoles()) or
                    ($user->getSociety()->getId() == $subject->getId() and in_array('ROLE_ADMIN', $user->getRoles()))
                ) {
                    $authorized = true;
                }
                break;

            case self::EDIT:
                if ($society = $user->getSociety()) {
                    if ($society && ($society->getId() == $subject->getId())) {
                        $authorized = true;
                    }
                }
                break;
        };



        return $authorized;
    }
}
