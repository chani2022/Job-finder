<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\User;

final class UserVoter extends Voter
{
    public const GET_VIEW = 'GET_VIEW';
    public const COLLECTION_VIEW = 'COLLECTION_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::GET_VIEW, self::COLLECTION_VIEW])
            && ($subject instanceof User || $subject == null);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User */
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::GET_VIEW:
                if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
                    return true;
                } else {
                    if ($subject->getId() == $user->getId()) {
                        return true;
                    } else {
                        if (in_array('ROLE_ADMIN', $user->getRoles())) {
                            if ($subject?->getSociety()->getId() == $user->getSociety()->getId()) {
                                return true;
                            }
                        }
                    }
                }
                break;
            case self::COLLECTION_VIEW:
                // dd($user);
                if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
                    return true;
                }
                break;
        }

        return false;
    }
}
