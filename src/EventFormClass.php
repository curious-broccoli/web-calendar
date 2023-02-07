<?php

// should it use a private variable instead of buffer?
class EventForm {
    private PDO $dbh;
    private string $target;
    private string $action;
    private bool $showCaptcha;
    private string $classes;

    /**
     * @param string $target either "create" or "edit"
     */
    public function __construct(PDO $dbh, string $target, string $action, bool $showCaptcha, string $classes) {
        $allowed_targets = ["create", "edit"];
        if (in_array($target, $allowed_targets)) {
            $this->target = $target;
        }
        else {
            throw new InvalidArgumentException("Please enter a valid target");
        }
        // no value checking implemented here
        $this->dbh = $dbh;
        $this->action = $action;
        $this->showCaptcha = $showCaptcha;
        $this->classes = $classes;
    }

    private function makeFormStart() : void { ?>
        <form action="<?=$this->action?>" method="post" class="<?=$this->classes?>">
    <?php
    }

    private function makeNormalInputs() : void { ?>
        <input type="text" name="name" placeholder="Event name" required autofocus /><br />
        <input type="text" name="location" placeholder="Location" required /><br />
        Start<br />
        <input type="date" id="date_start" name="date_start" required />
        <input type="time" id="time_start" name="time_start" required /><br />
        End<br />
        <input type="date" id="date_end" name="date_end" required />
        <input type="time" id="time_end" name="time_end" required /><br />
        <textarea name="description" rows="5" cols="31" placeholder="Description"></textarea><br />
    <?php
    }

    private function makeSeriesInput() : void {
        $series = $this->dbh->query("SELECT seriesid, name FROM event_series;");?>

        <select name="series">
            <option value="">Event series</option>
            <?php
            foreach ($series as $row) { ?>
            <option value="<?= $row["seriesid"] ?>"><?= $row["name"] ?></option>
            <?php
            }
            ?>
        </select><br />
    <?php
    }

    private function makeCaptcha() : void {
        if (!isset($_SESSION["userid"]) && $this->showCaptcha) { ?>
            Put security question or captcha here later.<br />
        <?php
        }
    }

    private function makeButtons() : void { ?>
        <input type="submit" name="submit" value="Submit!" />
    <?php
        // TODO: wrapper and other button?
    }

    private function makeFormEnd() : void { ?>
        </form>
    <?php
    }

    public function getHtml() : string {
        ob_start();

        $this->makeFormStart();
        $this->makeNormalInputs();
        $this->makeSeriesInput();
        $this->makeCaptcha();
        $this->makeButtons();
        $this->makeFormEnd();

        return ob_get_clean();
    }
}

?>