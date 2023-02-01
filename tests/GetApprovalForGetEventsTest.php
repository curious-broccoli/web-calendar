<?php
declare(strict_types=1);
require_once __DIR__ . "/../site/get-events.php";
use PHPUnit\Framework\TestCase;

final class GetApprovalForGetEventsTest extends TestCase {
    private function getDbh(): PDO {
        $db_path = __DIR__ . "/../database/calendar.sqlite";
        $dbh = new PDO("sqlite:$db_path");
        $dbh->exec("PRAGMA foreign_keys = ON;");
        return $dbh;
    }

    private function initGet($start, $end, $state): void {
        $_GET = array(); // clears array
        $_GET["start"] = $start;
        $_GET["end"] = $end;
        $_GET["state"] = $state;
    }

    private function initSession($user): void {
        $_SESSION = array();
        $_SESSION["userid"] = $user;
    }

    public function testApprovalGuest() : void {
        $this->initGet("2000-12-12T12:12:12.000Z", "2000-12-12T12:12:12.000Z", "2");
        $this->initSession(null);
        $this->assertSame(get_approval_state($this->getDbh()), ApprovalState::Approved);
    }

    // needs user 2 with default role
    public function testApprovalDefaultUser(): void {
        $userid = 2;
        $this->initGet("2000-12-12T12:12:12.000Z", "2000-12-12T12:12:12.000Z", "invalid");
        $this->initSession($userid);
        $this->assertSame(get_approval_state($this->getDbh()), ApprovalState::Approved);

        $this->initGet("2000-12-12T12:12:12.000Z", "2000-12-12T12:12:12.000Z", "2");
        $this->initSession($userid);
        $this->assertSame(get_approval_state($this->getDbh()), ApprovalState::Approved);

        $this->initGet("2000-12-12T12:12:12.000Z", "2000-12-12T12:12:12.000Z", "1");
        $this->initSession($userid);
        $this->assertSame(get_approval_state($this->getDbh()), ApprovalState::Approved);
    }


    // needs user 3 with approver role
    public function testApprovalApproverUser(): void {
        $userid = 3;
        $this->initGet("2000-12-12T12:12:12.000Z", "2000-12-12T12:12:12.000Z", "invalid");
        $this->initSession($userid);
        $this->assertSame(get_approval_state($this->getDbh()), ApprovalState::Approved);

        $this->initGet("2000-12-12T12:12:12.000Z", "2000-12-12T12:12:12.000Z", "2");
        $this->initSession($userid);
        $this->assertSame(get_approval_state($this->getDbh()), ApprovalState::Approved);

        $this->initGet("2000-12-12T12:12:12.000Z", "2000-12-12T12:12:12.000Z", "1");
        $this->initSession($userid);
        $this->assertSame(get_approval_state($this->getDbh()), ApprovalState::Approved);
    }

    // needs user 6 with moderator role
    public function testApprovalModeratorUser(): void {
        $userid = 6;
        $this->initGet("2000-12-12T12:12:12.000Z", "2000-12-12T12:12:12.000Z", "invalid");
        $this->initSession($userid);
        $this->assertSame(get_approval_state($this->getDbh()), ApprovalState::Approved);

        $this->initGet("2000-12-12T12:12:12.000Z", "2000-12-12T12:12:12.000Z", "2");
        $this->initSession($userid);
        $this->assertSame(get_approval_state($this->getDbh()), ApprovalState::Rejected);

        $this->initGet("2000-12-12T12:12:12.000Z", "2000-12-12T12:12:12.000Z", "1");
        $this->initSession($userid);
        $this->assertSame(get_approval_state($this->getDbh()), ApprovalState::Approved);

        $this->initGet("2000-12-12T12:12:12.000Z", "2000-12-12T12:12:12.000Z", "0");
        $this->initSession($userid);
        $this->assertSame(get_approval_state($this->getDbh()), ApprovalState::Waiting);
    }

    public function testApprovalNonExistentUser(): void {
        $this->initGet("2000-12-12T12:12:12.000Z", "2000-12-12T12:12:12.000Z", "0");
        $this->initSession(-1);
        $this->expectError();
        $this->assertSame(get_approval_state($this->getDbh()), ApprovalState::Approved);
    }
}
