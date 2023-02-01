<?php

enum ApprovalState: int {
    case Waiting = 0;
    case Approved = 1;
    case Rejected = 2;
}

class Event {
    public const DATEFORMAT = "Y-m-d\TH:i:s.v\Z";

    private string $name;
    private string $location;
    private string $description;
    // maybe string for dates is even better in this case?
    private DateTimeImmutable $start;
    private DateTimeImmutable $end;
    private int $user_id;
    private ApprovalState $approval_state;
    private int|null $approved_by;
    private int|null $series_id;
    private DateTimeImmutable $create_date;

    public function __construct($dbh) {
        // for creating a new event and not loading an event from DB
        // the order of execution matters
        $this->setName();
        $this->setLocation();
        $this->setDescription();
        $this->setStartEnd();
        $this->setUserId();
        $this->setApprovalState($dbh);
        $this->setSeries();
        $this->setApprovedBy();
        $this->setCreateDate();
    }

    public function getName(): string {
        return $this->name;
    }

    public function getLocation(): string {
        return $this->location;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getStart(): string {
        return $this->getDateString($this->start);
    }

    public function getEnd(): string {
        return $this->getDateString($this->end);
    }

    public function getCreateDate(): string {
        return $this->getDateString($this->create_date);
    }

    public function getUserId(): int {
        return $this->user_id;
    }

    public function getApprovalState(): int {
        return $this->approval_state->value;
    }

    public function getApprovedBy(): int|null {
        return $this->approved_by;
    }

    public function getSeriesId(): int|null {
        return $this->series_id;
    }

    private function setName() : void {
        $this->name = $this->processString("name", 150);
    }

    private function setLocation(): void {
        $this->location = $this->processString("location", 100);
    }

    private function setDescription(): void {
        $this->description = $this->processString("description", 1000, true, true);
    }

    private function processString(string $var_name, int $maxLength, bool $allowEmpty = false, bool $allowHtml = false) : string {
        // TODO: rename function probably
        // TODO: argument to strip like emojis and weird stuff (before or after html?)
        $string = $_POST[$var_name];
        if (empty($string) && !$allowEmpty) {
            throw new Exception("Please enter a " . $var_name . " value!");
        }
        if (!$allowHtml) {
            $string = strip_tags($string);
            // checks if whole string got deleted by stripping
            // so user would not get confused by more generic error
            if (empty($string) && !$allowEmpty) {
                throw new Exception("Please enter a " . $var_name . " value without HTML!");
            }
        }

        $string = substr($string, 0, $maxLength);
        return $string;
    }

    private function setStartEnd() : void {
        $this->start = $this->parseDate("datetime_start");
        $this->end = $this->parseDate("datetime_end");
        if ($this->end < $this->start) {
            $this->end = $this->start;
        }
    }

    private function parseDate(string $var_name) : DateTimeInterface {
        $date_string = $_POST[$var_name];
        if ($this->isValidDate($date_string)) {
            // maybe return the DateTime from isValidDate() so I don't need
            // to parse it again?
            return DateTimeImmutable::createFromFormat(self::DATEFORMAT, $date_string);
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
        $d = DateTimeImmutable::createFromFormat(self::DATEFORMAT, $date);
        // The Y ( 4 digits year ) returns TRUE for any integer with any number
        // of digits so changing the comparison from == to === fixes the issue.
        return $d && $d->format(self::DATEFORMAT) === $date;
    }

    private function getDateString(DateTimeImmutable $date) : string {
        return $date->format(self::DATEFORMAT);
    }

    private function setUserId() : void {
        $var_name = "userid";
        $user_id = $_SESSION[$var_name] ?? 1;
        $this->user_id = $user_id;
    }

    private function setApprovalState($dbh) : void {
        if ($this->user_id == 1) {
            $this->approval_state = ApprovalState::Waiting;
        }
        else {
            $role = get_user_role($_SESSION["userid"], $dbh);
            if (in_array($role, [Role::Approver, Role::Moderator], true)) {
                $this->approval_state = ApprovalState::Approved;
            }
            else {
                $this->approval_state = ApprovalState::Waiting;
            }
        }
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
        // no need to test for rejected because that cannot happen until here
        if ($this->approval_state == ApprovalState::Waiting) {
            $this->approved_by = null;
        }
        // if approved
        else {
            $this->approved_by = $this->user_id;
        }
    }

    private function setCreateDate(): void {
        // it will be UTC unless php.ini is set to anything else
        $this->create_date = new DateTimeImmutable("now");
    }
}

function insertEventInDb(PDO $dbh, Event $event) : void {
    try {
        $stmt = $dbh->prepare("
        INSERT INTO event (
        name, description, datetime_start, datetime_end, location,
        created_by, approval_state, approved_by, datetime_creation, event_series)
        VALUES (
        :name, :description, :start, :end, :location, :created_by,
        :approval_state, :approved_by, :creation_time, :series);");

        $stmt->execute(array(
            ":name" => $event->getName(),
            ":description" => $event->getDescription(),
            ":start" => $event->getStart(),
            ":end" => $event->getEnd(),
            ":location" => $event->getLocation(),
            ":created_by" => $event->getUserId(),
            ":approval_state" => $event->getApprovalState(),
            ":approved_by" => $event->getApprovedBy(),
            ":creation_time" => $event->getCreateDate(),
            ":series" => $event->getSeriesId()
        ));
        echo "Successfully submitted event!<br/>";
    } catch (PDOException $e) {
        // printing detailed error to user might be bad, change this later?
        die("Error!: " . $e->getMessage() . "<br/>");
        //die("Sorry, something went wrong! Please try again.");
    }
}

?>