<?php
require_once __DIR__ . "/../src/dbconnection.php";
require_once __DIR__ . "/../src/flash_message.php";

define("FLASH_MESSAGE_NAME", "create_event_error_message");
define("ERROR_REDIRECT_LOCATION", "/new_event.php");

enum ApprovalState: int {
    case Waiting = 0;
    case Approved = 1;
    case Rejected = 2;
}

class Event {
    private const DATEFORMAT = "Y-m-d\TH:i:s.v\Z";

    private string $name;
    private string $location;
    private string $description;
    // maybe string for dates is even better in this case?
    private DateTime $start;
    private DateTime $end;
    private int $user_id;
    private ApprovalState $approval_state;
    private int|null $series_id;
    private int|null $approved_by;
    // TODO:
    // submit time
    // ...

    public function __construct() {
        $this->setName();
        $this->setLocation();
        $this->setDescription();
        $this->setStartEnd();
        $this->setUserId();
        $this->setApprovalState();
        $this->setSeries();
        $this->setApprovedBy();
    }
    
    private function setName() : void {
        $this->name = $this->getSanitizedString("name");
    }

    private function setLocation(): void {
        $this->location = $this->getSanitizedString("location");
    }

    private function setDescription(): void {
        $this->description = $this->getHtmlString("description");
    }

    private function getSanitizedString(string $var_name) : string {
        // TODO: add a maxLength parameter and maybe merge getSanitized and getHtml
        $string = $_POST[$var_name];
        if (empty($string)) {
            throw new Exception("Please enter a " . $var_name . " value!");
        }
        $sanitized = strip_tags($string);
        // checks if whole string got deleted by stripping
        if (empty($sanitized)) {
            throw new Exception("Please enter a " . $var_name . " value without HTML!");
        }
        return $sanitized;
    }

    private function getHtmlString(string $var_name) : string {
        // TODO: add a maxLength parameter
        $string = $_POST[$var_name];
        return $string;
    }

    private function setStartEnd() : void {
        $this->start = $this->parseDate("datetime_start");
        $this->end = $this->parseDate("datetime_end");
        if ($this->end < $this->start) {
            $this->end = $this->start;
        }
    }

    private function parseDate(string $var_name) : DateTime {
        $date_string = $_POST[$var_name];
        if ($this->isValidDate($date_string)) {
            // maybe return the DateTime from isValidDate() so I don't need
            // to parse it again?
            return DateTime::createFromFormat(self::DATEFORMAT, $date_string);
        }
        else {
            throw new Exception("Please enter valid start and end values!");
        }
    }

    /**
     * Checks if string is valid date in the format
     * 
     * Checks if the date string is a valid date in the format
     * without overflowing
     * e.g. "2023-02-30T13:24:00.000Z" would not be valid
     */
    private function isValidDate(string $date) : bool {
        $d = DateTime::createFromFormat(self::DATEFORMAT, $date);
        // The Y ( 4 digits year ) returns TRUE for any integer with any number
        // of digits so changing the comparison from == to === fixes the issue.
        return $d && $d->format(self::DATEFORMAT) === $date;
    }

    private function getStartDateString() : string {
        return $this->start->format(self::DATEFORMAT);
    }

    private function getEndDateString(): string {
        return $this->end->format(self::DATEFORMAT);
    }

    private function setUserId() : void {
        $var_name = "userid";
        if (isset($_SESSION[$var_name])) {
            $user_id = $_SESSION[$var_name];
        } else {
            $user_id = 1;
        }
        $this->user_id = $user_id;
    }

    private function setApprovalState() : void {
        // TODO: if moderator created the event, set approval state to approved
        $this->approval_state = ApprovalState::Waiting;
    }

    private function setSeries() : void {
        $var_name = "series";
        if (empty($_POST[$var_name])) {
            $series = null;
        }
        elseif (is_numeric($_POST[$var_name])) {
            // TODO: maybe check if int is a valid seriesid in the db
            // or is it not necessary because if the GUI is used it will
            // (likely) return an existing series id
            $series = intval($_POST[$var_name]);
        } else {
            error_and_redirect("The event series value is invalid! This shouldn't happen.");
        }
        $this->series_id = $series;
    }

    private function setApprovedBy() : void {
        if ($this->approval_state == ApprovalState::Waiting) {
            $this->approved_by = null;
        }
        else {
            // TODO: should be the moderator's userid
            // (if it auto approves when they create one)
            $this->approved_by = $this->user_id;
        }
    }

    public function insert_db(PDO $dbh) : void {
        $stmt = $dbh->prepare("
        INSERT INTO event (
        name, description, datetime_start, datetime_end, location,
        created_by, approval_state, approved_by, event_series) VALUES (
        :name, :description, :start, :end, :location, :created_by,
        :approval_state, :approved_by, :series);");
        
        try {
            $stmt->execute(array(
                ":name" => $this->name,
                ":description" => $this->description,
                ":start" => $this->getStartDateString(),
                ":end" => $this->getEndDateString(),
                ":location" => $this->location,
                ":created_by" => $this->user_id,
                ":approval_state" => $this->approval_state->value,
                ":approved_by" => $this->approved_by,
                ":series" => $this->series_id
            ));
            echo "Successfully submitted event!<br/>";
        } catch (PDOException $e) {
            // printing detailed error to user might be bad, change this later?
            //die("Error!: " . $e->getMessage() . "<br/>");
            die("Sorry, something went wrong! Please try again.");
        }
    }
}

session_start();
try {
    $event = new Event();
} catch (Exception $e) {
    error_and_redirect($e->getMessage());
}
catch (Error $e) {
    // TODO: don't give details to user
    //error_and_redirect("Fatal error!");
    error_and_redirect("Fatal error: " . $e->getMessage());
}
$event->insert_db($dbh);

?>