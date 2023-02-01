<?php
declare(strict_types=1);
require_once __DIR__ . "/../src/Role.php";
use PHPUnit\Framework\TestCase;

final class GetRoleTest extends TestCase {
    private function getDbh(): PDO {
        $db_path = __DIR__ . "/../database/calendar.sqlite";
        $dbh = new PDO("sqlite:$db_path");
        $dbh->exec("PRAGMA foreign_keys = ON;");
        return $dbh;
    }

    public function testRoleGuest() : void {
        // because guest user should never have a userid set in session
        $_SESSION = array();
        $this->expectError();
        // fails because of undefined array key now, fails with TypeError if I pass null
        get_user_role($_SESSION["userid"], $this->getDbh());
    }

    // this causes deprecation notice
    public function testRoleGuestId(): void {
        // in reality the userid should never be 1
        $_SESSION["userid"] = 1;
        $this->expectError();
        // fails because guest has role null
        get_user_role($_SESSION["userid"], $this->getDbh());
    }

    // needs user 2 with default role
    // user 3 with approver role
    // and user 6 with moderator role
    public function testRoleExistingUser(): void {
        $_SESSION["userid"] = 2;
        $this->assertSame(get_user_role($_SESSION["userid"], $this->getDbh()), Role::Default);

        $_SESSION["userid"] = 3;
        $this->assertSame(get_user_role($_SESSION["userid"], $this->getDbh()), Role::Approver);

        $_SESSION["userid"] = 6;
        $this->assertSame(get_user_role($_SESSION["userid"], $this->getDbh()), Role::Moderator);
    }

    public function testRoleNonexistentUser(): void {
        $_SESSION["userid"] = -1;
        $this->expectError();
        // fails because no row returned from DB
        get_user_role($_SESSION["userid"], $this->getDbh());
    }
}
