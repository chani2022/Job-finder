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

    public function testSupportsUser(): void
    {
        $attribute = UserVoter::VIEW;
        $subject = new User();
        $methodSupports = new ReflectionMethod($this->userVoter, 'supports');
        $methodSupports->setAccessible(true);

        $res = $methodSupports->invoke($this->userVoter, $attribute, $subject);
        $this->assertTrue($res);
    }
    /**
     * @dataProvider getUser
     */
    public function testUserVoteOnAttribute(string $roles, bool $can_read): void
    {
        $subject = $this->all_fixtures['user_adm_society'];
        /** @var User */
        $user_access_resource = match ($roles) {
            'super-admin' => $this->all_fixtures['super'],
            'admin-owner-user' => $this->all_fixtures['admin_adm_society'],
            'user-owner' => $this->all_fixtures['user_adm_society'],
            'admin-other' => $this->all_fixtures['admin_1'],
            'user-other' => $this->all_fixtures['user_1'],
            default => null
        };
        $token = new UsernamePasswordToken($user_access_resource ?? new User(), 'api', $user_access_resource ? $user_access_resource->getRoles() : []);

        $attribute = UserVoter::VIEW;
        $methodSupports = new ReflectionMethod($this->userVoter, 'voteOnAttribute');
        $methodSupports->setAccessible(true);

        $res = $methodSupports->invoke($this->userVoter, $attribute, $subject, $token);

        $this->assert($res, $can_read);
    }

    private function assert(bool $excepted, $can_read): void
    {
        if ($can_read) {
            $this->assertTrue($excepted);
        } else {
            $this->assertFalse($excepted);
        }
    }

    public static function getUser(): array
    {
        return [
            "super-admin" => ['roles' => 'super-admin', 'can_read' => true],
            "admin-owner-user" => ['roles' => 'admin-owner-user', 'can_read' => true],
            "user-owner" => ['roles' => 'user-owner', 'can_read' => true],
            "admin-other" => ['roles' => 'admin-other', 'can_read' => false],
            "user-other" => ['roles' => 'user-other', 'can_read' => false],
            "anonymous" => ['roles' => 'anonymous', 'can_read' => false]
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->userVoter = null;
    }
}
