"use strict";
import * as helper from "./helper.js";
import { startOfISOWeek, endOfISOWeek, addDays, addWeeks, addMonths } from 'https://esm.run/date-fns';

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

    /** section with the date picker and view picker */
    static calendarHeader = "calendar-header";
    /** section with the day elements and the day names */
    static calendarGrid = "calendar-grid";
    /** class for just the day names in the grid */
    static gridHeader = "grid-header";
    /** class for the days */
    static gridContent = "grid-content";

    constructor(date) {
        this.selectedDate = new Date(date);

        // is there a better place to add this listener?

        // use .onclick to overwrite all other events
        // else it will have changeDate from multiple view instances assigned
        document.querySelectorAll(".date-picker-arrow")
            .forEach((element) => element.onclick = this.changeDate);
    }

    draw() {
        this.drawCalendarHeader();
        this.drawGridHeader();
        this.drawGrid();
        this.makePopovers();
    }

    static resetGridClass() {
        const grid = document.querySelector("#" + View.calendarGrid);
        const viewClasses = ["list", "day", "workweek", "week", "month", "year"];
        viewClasses.forEach((className) => grid.classList.remove(className));
    }

    static resetView() {
        const grid = document.querySelector("#" + View.calendarGrid);
        grid.replaceChildren();
        View.resetGridClass();

        // how can I call a popover's methods like hide() -> bootstrap.popover.getInstance()?
        document.querySelectorAll(".popover").forEach((popover) => popover.remove());
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

class MonthView extends View {
    drawCalendarHeader() {
        const locale = navigator.language;
        const options = { month: "long", year: "numeric" };
        const headerTextEl = document.querySelector("#calendar-header-text");
        headerTextEl.textContent = this.selectedDate.toLocaleDateString(locale, options);
    }

    changeDate(e) {
        const datePicker = document.querySelector("#date-picker");
        const selectedDate = new Date(datePicker.value);
        const direction = e.currentTarget.id == "date-arrow-right" ? 1 : -1;

        const newDate = addMonths(selectedDate, direction);
        // Swedish locale easily sets it to the ISO 8601 format
        // which is used by the "date" input element
        const locale = "sv";
        datePicker.value = newDate.toLocaleDateString(locale);
        draw();
    }

    drawGridHeader() {
        const locale = navigator.language;
        const options = { weekday: "long" };
        const monday = new Date(Date.UTC(2017, 0, 2));
        const dayNames = [];
        for (let i = 0; i < 7; i++) {
            dayNames.push(monday.toLocaleDateString(locale, options));
            monday.setDate(monday.getDate() + 1);
        }
        const grid = document.querySelector("#" + View.calendarGrid);
        dayNames.forEach(dayName => {
            const li = document.createElement("li");
            li.className += View.gridHeader;
            li.textContent = dayName;
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
        grid.classList.add("month");
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
}

class WeekView extends View {
    constructor(date, workDays) {
        super(date);
        this.weekStart = startOfISOWeek(this.selectedDate);
        // 5 for workweek, 7 for whole week
        this.workDays = workDays;
        this.weekEnd = addDays(this.weekStart, this.workDays - 1);
    }

    drawCalendarHeader() {
        const headerTextEl = document.querySelector("#calendar-header-text");
        const dateTimeFormat = new Intl.DateTimeFormat(navigator.language, { dateStyle: "long" });
        headerTextEl.textContent = dateTimeFormat.formatRange(this.weekStart, this.weekEnd);;
    }

    changeDate(e) {
        const datePicker = document.querySelector("#date-picker");
        const selectedDate = new Date(datePicker.value);
        const direction = e.currentTarget.id == "date-arrow-right" ? 1 : -1;

        const newDate = addWeeks(selectedDate, direction);
        console.log(selectedDate);
        console.log(newDate);
        console.log(" ");
        // Swedish locale easily sets it to the ISO 8601 format
        // which is used by the "date" input element
        const locale = "sv";
        datePicker.value = newDate.toLocaleDateString(locale);
        draw();
    }

    drawGridHeader() {
        const locale = navigator.language;
        const options = {
            weekday: "long",
            day: "numeric",
            month: "short" };
        const dayNames = [];
        for (let i = 0; i < this.workDays; i++) {
            const day = addDays(this.weekStart, i);
            dayNames.push(day.toLocaleDateString(locale, options));
        }
        const grid = document.querySelector("#" + View.calendarGrid);
        dayNames.forEach(dayName => {
            const li = document.createElement("li");
            li.className += View.gridHeader;
            li.textContent = dayName;
            grid.appendChild(li);
        });
    }

    // TODO
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

    // TODO
    //can timezones cause problems here? (for checking if it is today)
    #isSameDay(date, day_number) {
        //the 3 conditions should be sorted by the highest chance to fail first
        return date.getDate() === day_number &&
            date.getMonth() === this.selectedDate.getMonth() &&
            date.getFullYear() === this.selectedDate.getFullYear();
    }

    // TODO
    #getEventsForDay(events, day_number) {
        return events.filter((e) => this.#isSameDay(e.datetime_start, day_number))
    }

    // TODO
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

    // TODO
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
        if (this.workDays === 7) {
            grid.classList.add("week");
        } else {
            grid.classList.add("workweek");
        }
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
}

/**
 *
 * @returns {View} subclass of View
 */
function getNewView() {
    //should check if it is valid date (some browsers might default to text input field)
    //else maybe set as today
    const selectedDate = document.querySelector("#date-picker").value;
    const selectedView = document.querySelector("#view-tabs input[type=radio]:checked").value;

    let newView;
    switch (selectedView) {
        case "list":
            newView = new ListView(selectedDate);
            break;
        case "day":
            newView = new DayView(selectedDate);
            break;
        case "workweek":
            newView = new WeekView(selectedDate, 5);
            break;
        case "week":
            newView = new WeekView(selectedDate, 7);
            break;
        case "month":
            newView = new MonthView(selectedDate);
            break;
        case "year":
            newView = new YearView(selectedDate);
            break;
    }
    return newView;
}

function draw() {
    View.resetView();
    const view = getNewView();
    console.log(view);
    view.draw();
}

//rename to something better
function start() {
    const datePicker = document.querySelector("#date-picker");
    datePicker.addEventListener("change", () => draw());
    // is a change event on the radio inputs's parent a good way to do it?
    // or should I add a listener to all radio inputs?
    const tabsEl = document.querySelector("#view-tabs");
    tabsEl.addEventListener("change", () => draw());

    draw();
}