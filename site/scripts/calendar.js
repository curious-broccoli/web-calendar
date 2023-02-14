import * as helper from "./helper.js";

function eventsLoaded () {
    if (this.status === 200) {
        sessionStorage.setItem("events", this.responseText);
    }
    else {
        console.log("Failed loading events from server");
    }
    start();
}

//EVENTS SHOULD maybe ONLY BE REQUESTED THE FIRST TIME AND ON EXPLICIT REFRESH REQUEST IN GUI
// by default should only get from past two and next 12 months
// bigger (different?) range when needed
// maybe move this into a method requestEvents() of the view class
// to then use the view's selected date after creating view instance
const req = new XMLHttpRequest();
req.addEventListener("load", eventsLoaded);
// if it's async it will try to draw before loading
const params = new URLSearchParams();
params.set("state", 1);
req.open("GET", "get-events.php?" + params);
req.send();

// I would put the functions below for popovers in the View class but
// they would have to be called with this and this points to the html element
// for the popover instead
function createListElement(value, classList = [], allowHtml = false) {
    const li = document.createElement("li");
    if (classList.length > 0) {
        li.classList.add(...classList);
    }
    if (allowHtml) {
        // to have HTML except malicious and <img> in description
        const sanitizeOptions = {USE_PROFILES: {html: true}, FORBID_TAGS: ["img"]};
        li.innerHTML = DOMPurify.sanitize(value, sanitizeOptions);
    }
    else {
        li.textContent = value;
    }
    return li;
}

function formatDate(dateString) {
    // for display in popover
    const date = new Date(dateString);
    const options = {
        dateStyle: "full",
        timeStyle: "short"
    };
    const locale = navigator.language;
    return date.toLocaleString(locale, options);
}

Date.prototype.getDaysInMonth = function () {
    // getting day 0 of next month gives the last day of current month
    const d = new Date(this.getFullYear(), this.getMonth() + 1, 0);
    return d.getDate();
}

class View {
    // class names and ids of the html classes for the main sections
    // can be class name and id at the same time
    static calendarHeader = "calendar-header";
    static calendarGrid = "calendar-grid";
    static gridHeader = "grid-header";
    static gridContent = "grid-content";

    constructor(date) {
        this.selectedDate = new Date(date + "T00:00:00.000Z");
    }

    static resetView() {
        const grid = document.querySelector("#" + View.calendarGrid);
        grid.replaceChildren();

        // how can I call a popover's methods like hide() -> bootstrap.popover.getInstance()?
        document.querySelectorAll(".popover").forEach((popover) => popover.remove());
    }
}

class MonthView extends View {
    drawCalendarHeader() {
        const locale = navigator.language;
        const options = { month: "long", year: "numeric" };
        const headerText = document.querySelector("#calendar-header-text");
        headerText.textContent = this.selectedDate.toLocaleDateString(locale, options);

        const leftButton = document.querySelector("#date-arrow-left");
        leftButton.addEventListener("click", this.#changeDate);
        const rightButton = document.querySelector("#date-arrow-right");
        rightButton.addEventListener("click", this.#changeDate);
    }

    #changeDate(e) {
        const datePicker = document.querySelector("#date-picker");
        // selectedDate is local timezone
        const selectedDate = new Date(datePicker.value + "T00:00");
        // number determines if it will decrement or increment
        let number = -1;
        if (e.currentTarget.id == "date-arrow-right") {
            number = 1;
        }
        // setting the 2nd argument of setMonth() to 1 seems to prevent problems
        selectedDate.setMonth(selectedDate.getMonth() + number, 1);
        // Swedish locale easily sets it to the ISO 8601 format
        // which is used by the "date" input element
        const locale = "sv";
        datePicker.value = selectedDate.toLocaleDateString(locale);
        draw();
    }

    drawGridHeader() {
        const locale = navigator.language;
        const options = { weekday: "long" };
        const monday = new Date(Date.UTC(2017, 0, 2));
        const days = [];
        for (let i = 0; i < 7; i++) {
            days.push(monday.toLocaleDateString(locale, options));
            monday.setDate(monday.getDate() + 1);
        }
        const grid = document.querySelector("#" + View.calendarGrid);
        days.forEach(day => {
            const li = document.createElement("li");
            li.className += View.gridHeader;
            li.textContent = day;
            grid.appendChild(li);
        });
    }

    #drawOtherDays(grid, start, end, class_string, text) {
    for (let i = start; i < end; i++) {
            const day = document.createElement("li");
            day.classList.add(class_string, View.gridContent);
            const span = document.createElement("span");
            span.textContent = text;
            day.appendChild(span);
            grid.appendChild(day);
        }
    }

    //can timezones cause problems here? (for checking if it is today)
    #isSameDay(date, day_number) {
        //the 3 conditions should be sorted by the highest chance to fail first
        return date.getDate() === day_number &&
            date.getMonth() === this.selectedDate.getMonth() &&
            date.getFullYear() === this.selectedDate.getFullYear();
    }

    #getEventsForDay(events, day_number) {
        return events.filter((e) => this.#isSameDay(e.datetime_start, day_number))
    }

    #createEventElement(e) {
        const div = document.createElement("div");
        div.className += "event";
        div.setAttribute("data-eventid", e.eventid);
        div.setAttribute("data-bs-toggle", "popover");
        const locale = navigator.language;
        const options = { timeStyle: "short" };
        div.textContent = e.datetime_start.toLocaleTimeString(locale, options) + " " + e.name;
        return div;
    }

    drawGrid() {
        const numberOfDays = this.selectedDate.getDaysInMonth();
        // Monday - Sunday : 0 - 6
        const firstDayOn = (() => {
            const firstDay = new Date(this.selectedDate.getFullYear(), this.selectedDate.getMonth(), 1);
            return firstDay.getDay() == 0 ? 6 : firstDay.getDay() - 1;
        })();

        const totalDaysShown = (() => {
            const minDays = numberOfDays + firstDayOn;
            const daysPerRow = 7;
            if (minDays == 28) {
                return daysPerRow * 4;
            }
            else if (minDays <= 35) {
                return daysPerRow * 5;
            }
            else {
                return daysPerRow * 6;
            }
        })();

        const grid = document.querySelector("#" + View.calendarGrid);
        // number of rows is needed for grid row height
        grid.setAttribute("grid-rows", totalDaysShown / 7);
        this.#drawOtherDays(grid, 0, firstDayOn, "month-prev", "prev");
        const today = new Date();
        const events = helper.getEvents();
        for (let i = 1; i <= numberOfDays; i++) {
            const day = document.createElement("li");
            day.className += View.gridContent;
            if (this.#isSameDay(today, i)) {
                day.id = "today";
            }
            const span = document.createElement("span");
            span.textContent = i;
            span.className = "day-header";
            day.appendChild(span);
            // the container is used to make only the events in it scrollable
            // and keep the day header in place
            const container = document.createElement("div");
            container.className = "event-container";
            const events_today = this.#getEventsForDay(events, i)
            events_today.forEach((e) => container.appendChild(this.#createEventElement(e)));
            day.appendChild(container);
            grid.appendChild(day);
        }
        this.#drawOtherDays(grid, numberOfDays + firstDayOn, totalDaysShown, "month-next", "next");
    }

    // trigger: click means you have to press on the trigger element again to close it and multiple popovers can be opened
    // trigger: focus means you clicking in the popover closes it (bad, probably)
    // -> maybe read https://stackoverflow.com/questions/8947749/how-can-i-close-a-twitter-bootstrap-popover-with-a-click-from-anywhere-else-on
    // BETTER: https://stackoverflow.com/a/15945492/15707077
    makePopovers() {
        const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
        const popoverList = [...popoverTriggerList].map((popoverTriggerEl) => {
            const event = helper.getEventById(+popoverTriggerEl.dataset.eventid);
            const options = {
                html: true,
                placement: "auto",
                trigger: "click",
                //trigger: "hover focus",
                title: function () {
                    return document.createTextNode(event.name);
                },
                content: function () {
                    const data = {
                        name: {
                            label: "Title", value: event.name, allowHtml: false
                        },
                        start: {
                            label: "Start", value: formatDate(event.datetime_start), allowHtml: false
                        },
                        end: {
                            label: "End", value: formatDate(event.datetime_end), allowHtml: false
                        },
                        location: {
                            label: "Location", value: event.location, allowHtml: false
                        },
                        description: {
                            label: "Description", value: event.description, allowHtml: true
                        }
                    };
                    const list = document.createElement("ol");
                    list.className = "popover-grid";
                    Object.values(data).forEach(property => {
                        // the label should never/can't be HTML data so I do not pass in
                        // the allowHtml argument
                        const label = createListElement(property.label + ":", ["popover-data-left"]);
                        list.appendChild(label);
                        const value = createListElement(property.value, ["popover-data-right"], property.allowHtml);
                        list.appendChild(value);
                    })
                    return list;
                },
            }

            new bootstrap.Popover(popoverTriggerEl, options);
        });
    }
}

function draw() {
    View.resetView();
    const datePicker = document.querySelector("#date-picker");
    //should check if it is valid date (some browsers might default to text input)
    //else maybe set as today
    const v = new MonthView(datePicker.value);
    v.drawCalendarHeader();
    v.drawGridHeader();
    v.drawGrid();
    v.makePopovers();
}

//rename to something better
function start() {
    draw();
    const datePicker = document.querySelector("#date-picker");
    datePicker.addEventListener("change", () => draw());
}








