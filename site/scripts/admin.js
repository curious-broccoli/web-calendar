import * as helper from "./helper.js";

function reqListener() {
    if (this.status === 200) {
        sessionStorage.setItem("events", this.responseText);
    }
    start();
}

const req = new XMLHttpRequest();
req.addEventListener("load", reqListener);
const params = new URLSearchParams();
params.set("state", 0);
req.open("GET", "get-events.php?" + params, false);
req.send();


// load event
function transferComplete(e) {
    if (this.status === 200) {
        // TODO: react
        console.log(200);
    } else if (this.status === 409) {
        // TODO: react
        console.log(409);
    } else {
        // TODO: generic error and refresh?
        console.log(this.status + ": unexpected error!");
    }
}

function getCurrentEventParameters(eventid, action) {
    const event = helper.getEventById(eventid);
    const params = new URLSearchParams();
    // for (const [key, value] of Object.entries(event)) {
    //  // const dates = ["datetime_end", "datetime_start", "last_change", "datetime_creation"];
    //     if (key === "datetime_end" || key === "datetime_start") {
    //         params.set(key, value.toISOString());
    //     } else {
    //         params.set(key, value);
    //     }
    // }
    params.set("last_change", event.last_change.toISOString());
    params.set("eventid", eventid); // remove if I later get it from the edit form
    params.set("action", action);
    return params.toString();
}

// click event
function processEvent() {
    const req = new XMLHttpRequest();
    req.open("POST", "process-event.php?");
    req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    req.addEventListener("load", transferComplete);

    const action = this.classList.contains("button-yes") ? "approve" : "reject";
    const eventid = Number(this.parentNode.dataset.eventid);
    const params = getCurrentEventParameters(eventid, action);
    req.send(params);
}

function makeProcessButton(type) {
    const button = document.createElement("button");
    button.addEventListener("click", processEvent);
    let text;
    let classes;
    if (type === "approve") {
        classes = ["button-yes"];
        text = "Approve";
    }
    else {
        classes = ["button-no"];
        text = "Reject";
    }
    button.textContent = text;
    button.classList.add(classes);
    return button;
}

function formatDateRange(startString, endString) {
    const start = new Date(startString);
    const end = new Date(endString);
    const dateTimeFormat = new Intl.DateTimeFormat(navigator.language, {
        dateStyle: "short",
        timeStyle: "short"
    });
    // only shows as much as necessary 😍
    return dateTimeFormat.formatRange(start, end);
}

function makeDateEl(start, end) {
    const dateEl = document.createElement("span");
    dateEl.textContent = formatDateRange(start, end);
    return dateEl;
}

// should it be a toggle?
function showForm() {
    // TODO:
    // if moderator but not if approver?
    // show form
    // FormData API ?
    // fill with values

    // should the calendar event be passed to here as argument or should
    // I get it using its ID?
    alert("button clicked");
}

function makeNameEl(name) {
    const nameEl = document.createElement("button");
    nameEl.classList.add("unprocessed-button");
    nameEl.textContent = name;
    nameEl.addEventListener("click", showForm);
    return nameEl;
}

function makeUserEl(name) {
    const userEl = document.createElement("span");
    userEl.textContent = `[by ${name}]`; // should it be in squared braces?
    return userEl;
}

function start() {
    const listEl = document.querySelector("#unprocessed-container");
    const events = helper.getEvents();

    events.forEach(event => {
        const eventEl = document.createElement("div");
        eventEl.setAttribute("data-eventid", event.eventid);
        eventEl.classList.add(["unprocessed-event"]);
        // IMPORTANT INFO
        // location
        // description (careful, HTML!, but I might want to show as normal text)
        // "and link to?"
        eventEl.appendChild(makeDateEl(event.datetime_start, event.datetime_end));
        eventEl.appendChild(makeNameEl(event.name));
        eventEl.appendChild(document.createElement("br"));

        // more here
        const placeholderEl = document.createElement("spann");
        placeholderEl.textContent = "placeholder";
        eventEl.appendChild(placeholderEl);

        eventEl.appendChild(makeUserEl(event.username));
        eventEl.appendChild(makeProcessButton("approve"));
        eventEl.appendChild(makeProcessButton("reject"));
        listEl.appendChild(eventEl);
    });
}

