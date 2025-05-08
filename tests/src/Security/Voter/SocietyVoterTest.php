<?php

namespace App\Tests\src\Security\Voter;

use App\Entity\Society;
use App\Entity\User;
use App\Security\Voter\SocietyVoter;
use App\Traits\FixturesTrait;
use App\Traits\LogUserTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use ReflectionMethod;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class SocietyVoterTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use FixturesTrait;
    use LogUserTrait;

    private SocietyVoter $societyVoter;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $this->societyVoter = new SocietyVoter();
    }
    /**
     * @dataProvider getGranted
     */
    public function testSocietySupports(string $granted): void
    {
        $subject = new Society();

        $methodSupports = new ReflectionMethod($this->societyVoter, 'supports');
        $methodSupports->setAccessible(true);

        $res = $methodSupports->invoke($this->societyVoter, $granted, $subject);
        $this->assertTrue($res);
    }

    public static function getGranted(): array
    {
        return [
            "view" => ["SOCIETY_VIEW"],
            "edit" => ["SOCIETY_EDIT"]
        ];
    }
    /**
     * @dataProvider getUsers
     */
    public function testSocietyVoteOnAttribute(string $roles, array $attributes_to_access): void
    {

        $this->loadFixturesTrait();
        /** @var User */
        $user_load = $this->all_fixtures['admin'];
        $subject = $user_load->getSociety();

        $user_load = match ($roles) {
            'super' => $this->all_fixtures['super'],
            'admin' => $user_load,
            'admin_not_access' => $this->all_fixtures['admin_1'],
            'user' => $this->all_fixtures['user_1']
        };

        $methodSupports = new ReflectionMethod($this->societyVoter, 'voteOnAttribute');
        $methodSupports->setAccessible(true);

        $token = new UsernamePasswordToken($user_load, 'api', $user_load->getRoles());

        foreach ($attributes_to_access as $attribute) {
            $excepted = $methodSupports->invoke($this->societyVoter, $attribute, $subject, $token);
            $this->assert($roles, $attribute, $excepted);
        }
    }

    public static function getUsers(): array
    {
        return [
            "super_admin" => [
                "roles" => "super",
                "attributes_to_access" => ["SOCIETY_VIEW", "SOCIETY_EDIT"]
            ],
            "admin" => [
                "roles" => "admin",
                "attributes_to_access" => ["SOCIETY_VIEW", "SOCIETY_EDIT"]
            ],
            "admin_not_access" => [
                "roles" => "admin_not_access",
                "attributes_to_access" => ["SOCIETY_VIEW", "SOCIETY_EDIT"]
            ],
            "user" => [
                "roles" => "user",
                "attributes_to_access" => ["SOCIETY_VIEW", "SOCIETY_EDIT"]
            ],
        ];
    }

    private function assert(string $roles, string $attribute, bool $excepted)
    {
        if ($attribute == "SOCIETY_VIEW") {
            if ($roles == "super" or $roles == "admin") {
                $this->assertTrue($excepted);
            } else {
                $this->assertFalse($excepted);
            }
        } else if ($attribute == "SOCIETY_EDIT") {
            if ($roles == "admin") {
                $this->assertTrue($excepted);
            } else {
                $this->assertFalse($excepted);
            }
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // $this->societyVoter = null;
    }
}
