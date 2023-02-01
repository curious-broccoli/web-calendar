<?php
declare(strict_types=1);
require_once __DIR__ . "/../src/EventClass.php";
use PHPUnit\Framework\TestCase;

// maybe do the array filling in setUp method?
final class EventCreationTest extends TestCase {
    private function getDbh() : PDO {
        $db_path = __DIR__ . "/../database/calendar.sqlite";
        $dbh = new PDO("sqlite:$db_path");
        $dbh->exec("PRAGMA foreign_keys = ON;");
        return $dbh;
    }

    private function initPost($name, $location, $description, $start, $end, $series) : void {
        $_POST = array(); // clears array
        $_POST["name"] = $name;
        $_POST["location"] = $location;
        $_POST["description"] = $description;
        $_POST["datetime_start"] = $start;
        $_POST["datetime_end"] = $end;
        $_POST["series"] = $series;
    }

    private function initSession() : void {
        $_SESSION = array();
    }

    public function testCorrectSimpleInput(): void {
        $this->initPost("testName", "testLocation", "", "2000-12-12T12:12:12.000Z", "2023-12-12T12:12:12.000Z", "");
        $this->initSession();
        $event = new Event($this->getDbh());
        $this->assertSame($event->getName(), "testName");
        $this->assertSame($event->getDescription(), "");
        $this->assertSame($event->getLocation(), "testLocation");
        $this->assertSame($event->getStart(), "2000-12-12T12:12:12.000Z");
        $this->assertSame($event->getEnd(), "2023-12-12T12:12:12.000Z");
        // can't test the exact value of creation date
        // -> test format at least
        $create_date = DateTime::createFromFormat(Event::DATEFORMAT, $event->getCreateDate());
        $this->assertSame($create_date->format(EVENT::DATEFORMAT), $event->getCreateDate());
        $this->assertSame($event->getSeriesId(), null);
        $this->assertSame($event->getUserId(), 1);
        $this->assertSame($event->getApprovalState(), 0);
        $this->assertSame($event->getApprovedBy(), null);
    }

    public function testEndNotSmallerThanStart() : void {
        // test that end is set = start if smaller than start
        $this->initPost("<b>name", "<script>location</script>", "testDesc", "2000-12-12T12:12:12.000Z", "1999-12-12T12:12:12.000Z", "");
        $this->initSession();
        $event = new Event($this->getDbh());
        $this->assertSame($event->getEnd(), $event->getStart());
    }

    public function testMissingString(): void {
        // test that name must exist
        $this->initPost("", "testLocation", "testDesc", "2000-12-12T12:12:12.000Z", "1999-12-12T12:12:12.000Z", "");
        $this->initSession();
        $this->expectException(Exception::class);
        // if message contains
        $this->expectExceptionMessage("name value");
        $event = new Event($this->getDbh());
    }

    public function testHtmlStrip() : void {
        // test that not whole string is deleted
        $this->initPost("<b>name", "<script>location</script>", "testDesc", "2000-12-12T12:12:12.000Z", "1999-12-12T12:12:12.000Z", "");
        $this->initSession();
        $event = new Event($this->getDbh());
        $this->assertSame($event->getName(), "name");
        $this->assertSame($event->getLocation(), "location");

        // test that description allows HTML
        $this->initPost("<b>name", "<script>location</script>", "<script>alert(1);</script>", "2000-12-12T12:12:12.000Z", "1999-12-12T12:12:12.000Z", "");
        $this->initSession();
        $event = new Event($this->getDbh());
        $this->assertSame($event->getDescription(), "<script>alert(1);</script>");

        // test that it throws an exception after stripping the whole location
        $this->initPost("<b>name", "<script></script>", "testDesc", "2000-12-12T12:12:12.000Z", "1999-12-12T12:12:12.000Z", "");
        $this->initSession();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("location value");
        $event = new Event($this->getDbh());
    }

    public function testMissingPostKey() : void {
        $_POST = array();
        $this->expectError();
        $this->expectErrorMessage('key "name"');
        $event = new Event($this->getDbh());
    }

    public function testInvalidDate() : void {
        // test that invalid dates aren't accepted
        $this->initPost("name", "location", "testDesc", "2000-12-32T12:12:12.000Z", "1999-12-12T12:12:12.000Z", "");
        $this->initSession();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("start and end value");
        $event = new Event($this->getDbh());
    }

    public function testTrimString() : void {
        // test that too long name is trimmed
        $length = 150;
        $tooLongText = str_repeat("x", $length + 1);
        $this->initPost($tooLongText, "testLocation", "", "2000-12-12T12:12:12.000Z", "2023-12-12T12:12:12.000Z", "");
        $this->initSession();
        $event = new Event($this->getDbh());
        $this->assertSame($length, strlen($event->getName()));

        // test that short enough strings work
        $length = 100;
        $tooLongText = str_repeat("x", $length);
        $this->initPost($tooLongText, "testLocation", "", "2000-12-12T12:12:12.000Z", "2023-12-12T12:12:12.000Z", "");
        $this->initSession();
        $event = new Event($this->getDbh());
        $this->assertSame($length, strlen($event->getName()));

        // test that too long description is trimmed
        $length = 1000;
        $tooLongText = str_repeat("x", $length + 1);
        $this->initPost($tooLongText, "testLocation", $tooLongText, "2000-12-12T12:12:12.000Z", "2023-12-12T12:12:12.000Z", "");
        $this->initSession();
        $event = new Event($this->getDbh());
        $this->assertSame($length, strlen($event->getDescription()));
    }

    public function testApprovalState() : void {
        // test default
        $this->initPost("testName", "testLocation", "", "2000-12-12T12:12:12.000Z", "2023-12-12T12:12:12.000Z", "");
        $this->initSession();
        $event = new Event($this->getDbh());
        $this->assertSame($event->getApprovalState(), 0);
    }

    public function testSeries() : void {
        $this->initPost("testName", "testLocation", "", "2000-12-12T12:12:12.000Z", "2023-12-12T12:12:12.000Z", "");
        $this->initSession();
        $event = new Event($this->getDbh());
        $this->assertSame($event->getSeriesId(), null);

        $this->initPost("testName", "testLocation", "", "2000-12-12T12:12:12.000Z", "2023-12-12T12:12:12.000Z", "1");
        $this->initSession();
        $event = new Event($this->getDbh());
        $this->assertSame($event->getSeriesId(), 1);
    }

    public function testUser() : void {
        // test default
        $this->initPost("testName", "testLocation", "", "2000-12-12T12:12:12.000Z", "2023-12-12T12:12:12.000Z", "");
        $this->initSession();
        $event = new Event($this->getDbh());
        $this->assertSame($event->getUserId(), 1);
    }

    public function testApprovedBy(): void {
        // test default
        $this->initPost("testName", "testLocation", "", "2000-12-12T12:12:12.000Z", "2023-12-12T12:12:12.000Z", "");
        $this->initSession();
        $event = new Event($this->getDbh());
        $this->assertSame($event->getApprovedBy(), null);
    }

    // TODO:
    // test user id
    // test approval state with session's user id
    // test approved by with session's user id
}
