function reqListener () {
    sessionStorage.setItem("events", this.responseText);
}

//EVENTS SHOULD ONLY BE REQUESTED THE FIRST TIME AND ON EXPLICIT REFRESH REQUEST IN GUI
//should only get from past two and next 12 months
const req = new XMLHttpRequest();
req.addEventListener("load", reqListener);
//should it be called async or not?
req.open("GET", "get-events.php", true);
req.send();

const getEvents = () => {
    let events;
    if(sessionStorage.getItem("events") === null){
        events = [];
    }else {
        events = JSON.parse(sessionStorage.getItem("events"), (key, value) => 
            key === "datetime_end" || key === "datetime_start"
            ? new Date(value)
            : value
        );
    }
    return events;
}

Date.prototype.getDaysInMonth = function () {
    // getting day 0 of next month gives the last day of current month
    const d = new Date(this.getFullYear(), this.getMonth() + 1, 0);
    return d.getDate();
}

class View {
    // names of the html classes for the main sections
    static calendarHeader = "calendar-header";
    static gridHeader = "grid-header";
    static grid = "grid";

    constructor(date) {
        this.selectedDate = new Date(date + "T00:00:00.000Z");
    }

    // remove all elements that are added in a view so appending again won't duplicate
    static resetView() {
        const calendarHeaderText = document.querySelector("." + View.calendarHeader).firstChild;
        calendarHeaderText.textContent = "";
        const gridHeader = document.querySelector("." + View.gridHeader);
        gridHeader.replaceChildren();
        const grid = document.querySelector("." + View.grid);
        grid.replaceChildren();
    }
    
}

// maybe getLocale function?
class MonthView extends View {
    #property;
    drawCalendarHeader() {
        const locale = navigator.language;
        const options = { month: "long", year: "numeric" };
        const header = document.querySelector("." + View.calendarHeader);
        header.insertAdjacentText("afterbegin", this.selectedDate.toLocaleDateString(locale, options));
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
        const header = document.querySelector("." + View.gridHeader);
        days.forEach(day => {
            const li = document.createElement("li");
            li.innerText = day;
            header.appendChild(li);
        });
    }

    #drawOtherDays(grid, start, end, class_string, text) {
    for (let i = start; i < end; i++) {
            const day = document.createElement("li");
            day.setAttribute("class", class_string);
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
        div.setAttribute("class", "event");
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
        
        const grid = document.querySelector(".grid");
        this.#drawOtherDays(grid, 0, firstDayOn, "month-prev", "prev"); 
        const today = new Date();
        const events = getEvents();
        console.log(events);
        for (let i = 1; i <= numberOfDays; i++) {
            const day = document.createElement("li");
            if (this.#isSameDay(today, i)) {
                day.setAttribute("id", "today");
            }
            const span = document.createElement("span");
            span.textContent = i;
            day.appendChild(span);
            const events_today = this.#getEventsForDay(events, i)
            events_today.forEach((e) => day.appendChild(this.#createEventElement(e)));
            grid.appendChild(day);
        }

        this.#drawOtherDays(grid, numberOfDays + firstDayOn, totalDaysShown, "month-next", "next");
    }
}

function draw() {
    View.resetView();
    const datePicker = document.querySelector("#date-picker");
    //must check if it is valid date (some browsers might default to text input), else maybe set as today
    const v = new MonthView(datePicker.value);
    v.drawCalendarHeader();
    v.drawGridHeader();    
    v.drawGrid();    
}

if (document.readyState === "loading") {  // Loading hasn't finished yet
        document.addEventListener("DOMContentLoaded", start);
}
else {
    start();
}

//rename to something better
function start() {
    draw();
    const datePicker = document.querySelector("#date-picker");
    datePicker.addEventListener("change", () => draw());
}








  