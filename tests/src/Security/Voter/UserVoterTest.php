<?php

namespace App\Tests\src\Security\Voter;

use App\Entity\User;
use App\Security\Voter\UserVoter;
use App\Traits\FixturesTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use ReflectionMethod;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class UserVoterTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use FixturesTrait;

    private ?UserVoter $userVoter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userVoter = new UserVoter();
        static::bootKernel();
        $this->loadFixturesTrait();
    }
    /**
     * @dataProvider getSupportsUser
     */
    public function testSupportsUser(string $attribute): void
    {
        $subject = new User();
        $methodSupports = new ReflectionMethod($this->userVoter, 'supports');
        $methodSupports->setAccessible(true);

        $res = $methodSupports->invoke($this->userVoter, $attribute, $subject);
        $this->assertTrue($res);
    }
    /**
     * @dataProvider getUser
     */
    public function testUserVoteOnAttributeGetView(string $roles, bool $can_read, string $attribute): void
    {
        $subject = $this->all_fixtures['user_adm_society'];

        $res = $this->invokeMethod($roles, $subject, $attribute);

        $this->assert($res, $can_read);
    }
    /**
     * @dataProvider getUserToViewCollection
     */
    public function testUserVoteOnAttributeCollectionView(string $roles, bool $can_read, string $attribute): void
    {
        $subject = null;
        $res = $this->invokeMethod($roles, $subject, $attribute);

        $this->assert($res, $can_read);
    }
    /**
     * @return mixed
     */
    private function invokeMethod(string $roles, mixed $subject, string $attribute): mixed
    {
        /** @var User */
        $user_access_resource = match ($roles) {
            'super-admin' => $this->all_fixtures['super'],
            'admin-owner-user' => $this->all_fixtures['admin_adm_society'],
            'user-owner' => $this->all_fixtures['user_adm_society'],
            'admin-other' => $this->all_fixtures['admin_1'],
            'user-other' => $this->all_fixtures['user_1'],
            default => null
        };
        $methodVoteOnAttribute = new ReflectionMethod($this->userVoter, 'voteOnAttribute');
        $methodVoteOnAttribute->setAccessible(true);

        $token = new UsernamePasswordToken($user_access_resource ?? new User(), 'api', $user_access_resource ? $user_access_resource->getRoles() : []);
        return $methodVoteOnAttribute->invoke($this->userVoter, $attribute, $subject, $token);
    }

    private function assert(bool $excepted, bool $can_read): void
    {
        if ($can_read) {
            $this->assertTrue($excepted);
        } else {
            $this->assertFalse($excepted);
        }
    }

    public static function getSupportsUser(): array
    {
        return [
            "item" => ['attribute' => UserVoter::GET_VIEW],
            "collection" => ['attribute' => UserVoter::COLLECTION_VIEW]
        ];
    }

    public static function getUser(): array
    {
        return [
            "super-admin" => ['roles' => 'super-admin', 'can_read' => true, 'attribute' => UserVoter::GET_VIEW],
            "admin-owner-user" => ['roles' => 'admin-owner-user', 'can_read' => true, 'attribute' => UserVoter::GET_VIEW],
            "user-owner" => ['roles' => 'user-owner', 'can_read' => true, 'attribute' => UserVoter::GET_VIEW],
            "admin-other" => ['roles' => 'admin-other', 'can_read' => false, 'attribute' => UserVoter::GET_VIEW],
            "user-other" => ['roles' => 'user-other', 'can_read' => false, 'attribute' => UserVoter::GET_VIEW],
            "anonymous" => ['roles' => 'anonymous', 'can_read' => false, 'attribute' => UserVoter::GET_VIEW]
        ];
    }

    public static function getUserToViewCollection(): array
    {
        return [
            "super-admin" => ['roles' => 'super-admin', 'can_read' => true, 'attribute' => UserVoter::COLLECTION_VIEW],
            "admin-owner-user" => ['roles' => 'admin-owner-user', 'can_read' => false, 'attribute' => UserVoter::COLLECTION_VIEW],
            "user-owner" => ['roles' => 'user-owner', 'can_read' => false, 'attribute' => UserVoter::COLLECTION_VIEW],
            "admin-other" => ['roles' => 'admin-other', 'can_read' => false, 'attribute' => UserVoter::COLLECTION_VIEW],
            "user-other" => ['roles' => 'user-other', 'can_read' => false, 'attribute' => UserVoter::COLLECTION_VIEW],
            "anonymous" => ['roles' => 'anonymous', 'can_read' => false, 'attribute' => UserVoter::COLLECTION_VIEW]
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->userVoter = null;
    }
}
