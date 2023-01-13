<?php
declare(strict_types=1);
require_once __DIR__ . "/../src/EventClass.php";

use PHPUnit\Framework\TestCase;

final class EventCreationTest extends TestCase {
    private function initPost($name, $location, $description, $start, $end, $series) : void {
        $_POST = array(); // clear it
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
        $event = new Event();
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
        $event = new Event();
        $this->assertSame($event->getEnd(), $event->getStart());
    }

    public function testMissingString(): void {
        // test that name must exist
        $this->initPost("", "testLocation", "testDesc", "2000-12-12T12:12:12.000Z", "1999-12-12T12:12:12.000Z", "");
        $this->initSession();
        $this->expectException(Exception::class);
        // if message contains
        $this->expectExceptionMessage("name value");
        $event = new Event();
    }

    public function testHtmlStrip() : void {
        // test that not whole string is deleted
        $this->initPost("<b>name", "<script>location</script>", "testDesc", "2000-12-12T12:12:12.000Z", "1999-12-12T12:12:12.000Z", "");
        $this->initSession();
        $event = new Event();
        $this->assertSame($event->getName(), "name");
        $this->assertSame($event->getLocation(), "location");

        // test that description allows HTML
        $this->initPost("<b>name", "<script>location</script>", "<script>alert(1);</script>", "2000-12-12T12:12:12.000Z", "1999-12-12T12:12:12.000Z", "");
        $this->initSession();
        $event = new Event();
        $this->assertSame($event->getDescription(), "<script>alert(1);</script>");

        // test that it throws an exception after stripping the whole location
        $this->initPost("<b>name", "<script></script>", "testDesc", "2000-12-12T12:12:12.000Z", "1999-12-12T12:12:12.000Z", "");
        $this->initSession();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("location value");
        $event = new Event();        
    }

    public function testMissingPostKey() : void {
        $_POST = array();
        $this->expectError();
        $this->expectErrorMessage('key "name"');
        $event = new Event();
    }

    public function testInvalidDate() : void {
        // test that invalid dates aren't accepted
        $this->initPost("name", "location", "testDesc", "2000-12-32T12:12:12.000Z", "1999-12-12T12:12:12.000Z", "");
        $this->initSession();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("start and end value");
        $event = new Event();
    }

    public function testTrimString() : void {
        // test that too long name is trimmed
        $tooLongName = str_repeat("x", 151);
        $this->initPost($tooLongName, "testLocation", "", "2000-12-12T12:12:12.000Z", "2023-12-12T12:12:12.000Z", "");
        $this->initSession();
        $event = new Event();
        $this->assertSame(150, strlen($event->getName()));

        // test that too long description is trimmed
        $tooLongName = str_repeat("x", 1001);
        $this->initPost($tooLongName, "testLocation", "", "2000-12-12T12:12:12.000Z", "2023-12-12T12:12:12.000Z", "");
        $this->initSession();
        $event = new Event();
        $this->assertSame(1000, strlen($event->getDescription()));
    }

    // test false input
    // test user id
    // test approval state
    // test approved by
    // test series?
}
