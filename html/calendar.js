function reqListener () {
    sessionStorage.setItem("events", this.responseText);
}

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
        events = JSON.parse(sessionStorage.getItem("events"));
    }
    return events;

}
getEvents();

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
        //must check for today here
        for (let i = 1; i <= numberOfDays; i++) {
            const day = document.createElement("li");
            const span = document.createElement("span");
            span.textContent = i;
            day.appendChild(span);
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








  