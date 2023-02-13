import * as helper from "./helper.js";

function reqListener() {
    if (this.status === 200) {
        sessionStorage.setItem("events", this.responseText);
    }
    else {
        console.log("Failed loading events from server");
    }
    start();
}

const req = new XMLHttpRequest();
//req.responseType = "json";
req.addEventListener("load", reqListener);
const params = new URLSearchParams();
params.set("state", 0);
req.open("GET", "get-events.php?" + params, false);
req.send();

function makeErrorEl(id, text) {
    const errorEl = document.createElement("span");
    errorEl.id = id;
    errorEl.textContent = text;
}

function hideErrors() {
    const eventErrorEl = document.querySelector("#state-error");
    eventErrorEl.textContent = "";
    eventErrorEl.classList.add("hidden");
    const formErrorEl = document.querySelector("#form-error");
    formErrorEl.textContent = "";
    formErrorEl.classList.add("hidden");
}

// load event
function changeRequestComplete(e) {
    // needs to tell which action failed or succeeded and which eventid
    const data = this.response;
    console.log(JSON.stringify(data));
    alert(JSON.stringify(data));
    return;
    if (this.status === 200) {
        // TODO: if no error, after hiding event data, load next event into form
        if (data.action === "approve" || data.action === "reject") {
            const eventEl = document.querySelector(`div[data-eventid="${data.eventid}"]`);
            if ("error" in data) {
                const errorEl = document.querySelector("#state-error");
                errorEl.textContent = data.error;
                errorEl.classList.remove("hidden");
            }
            else {
                eventEl.classList.add("hidden");
                // TODO: load next event into form
            }
        } else if (data.action.includes("edit")) {
            // TODO: hide spinner
            // if just edit, gotta reload or something

            if ("error" in data) {
                const errorEl = document.querySelector("#form-error");
                errorEl.textContent = data.error;
            } else {
                // TODO: also hide event data
            }
        } else {
            // show error to user?
            console.log(data);
        }
        console.log(data);
    } else {
        console.log(`Change request responded with error:\n${this.status} ${this.responseText}`);
    }
}

function postRequest(params) {
    // TODO: what if reject/approve is clicked while the form is open with another event
    const req = new XMLHttpRequest();
    req.open("POST", "process-event.php?");
    req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    req.responseType = "json";
    req.addEventListener("load", changeRequestComplete);
    // how to test?
    req.addEventListener("error", () => alert("Request failed!\nPlease reload the page"));
    console.log(params);
    req.send(params); // does this need to be params.toString()?

    // TODO:
    // hide form
    // hide event element
    // hide errors?
}

// this is triggered by approve/reject buttons and form buttons
function handleSubmit(e) {
    // TODO:
    // if only edited, update the event(last_change) that was edited so it can be approved
    // or simply refresh all events and DOM after a successful edit?
    // should buttons be disabled while waiting for edit request to finish?
    // can I use the event's "submitter" property?
    e.preventDefault();


    const params = new URLSearchParams();
    let eventid;

    if (e.target.classList.contains("button-approve") || e.target.classList.contains("button-reject")) {
        const action = e.target.classList.contains("button-approve") ? "approve" : "reject";
        params.set("action", action);
        eventid = e.target.parentNode.parentNode.dataset.eventid;
    } else { // should I check here if it is really the form's button?
        params.set("action", e.target.id);
        const formParams = new URLSearchParams(new FormData(e.target.form));
        const formattedFormParams = makeUtcParams(formParams);
        for (const [key, val] of formattedFormParams.entries()) {
            params.set(key, val);
        }
        eventid = e.target.form.dataset.eventid;
    }
    params.set("eventid", eventid);
    const event = helper.getEventById(Number(eventid));
    params.set("last_change", event.last_change.toISOString());
    hideErrors();

    postRequest(params);
}

function makeProcessButton(type) {
    const button = document.createElement("button");
    button.addEventListener("click", handleSubmit)
    let text;
    let classes;
    if (type === "approve") {
        classes = ["button-yes", "button-approve"];
        text = "Approve";
    }
    else {
        classes = ["button-no", "button-reject"];
        text = "Reject";
    }
    button.textContent = text;
    button.classList.add(...classes);
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

/**
 * @param {URLSearchParams} urlParams
 */
function makeUtcParams(urlParams) {
    const startUtc = helper.getUtcString(urlParams.get("date_start"), urlParams.get("time_start"));
    urlParams.set("datetime_start", startUtc);
    const endUtc = helper.getUtcString(urlParams.get("date_end"), urlParams.get("time_end"));
    urlParams.set("datetime_end", endUtc);
    urlParams.delete("date_start");
    urlParams.delete("time_start");
    urlParams.delete("date_end");
    urlParams.delete("time_end");
    return urlParams
}

function getDateTimeValuesForInput(event) {
    const locale = "sv"; // for ISO 8601 format
    const options = { timeStyle: "short" };
    const formatted = {
        date_start: event.datetime_start.toLocaleDateString(locale),
        time_start: event.datetime_start.toLocaleTimeString(locale, options),
        date_end: event.datetime_end.toLocaleDateString(locale),
        time_end: event.datetime_end.toLocaleTimeString(locale, options)
    }
    return formatted;
}

function fillForm(event) {
    document.querySelector("#name").value = event.name;
    document.querySelector("#location").value = event.location;

    const formattedValues = getDateTimeValuesForInput(event);
    document.querySelector("#date-start").value = formattedValues.date_start;
    document.querySelector("#time-start").value = formattedValues.time_start;
    document.querySelector("#date-end").value = formattedValues.date_end;
    document.querySelector("#time-end").value = formattedValues.time_end;

    document.querySelector("#description").value = event.description;
    document.querySelector("#series").value = event.event_series ?? "";
}

function showForm() {
    // TODO:
    // if moderator but not if approver? or disable

    // should the calendar event be passed to here as argument or should
    // I get it using its ID?
    const eventid = Number(this.parentNode.parentNode.dataset.eventid);
    const event = helper.getEventById(Number(eventid));
    if (event === undefined) {
        return;
    }
    fillForm(event);
    const form = document.querySelector("#form");
    form.dataset.eventid = eventid;
    form.querySelectorAll("[type=submit]").forEach((button) => button.addEventListener("click", handleSubmit));
    //form.addEventListener("submit", onFormSubmit); to prevent default?

    const formWrapper = document.querySelector("#admin-form-wrapper");
}

function makeNameEl(name) {
    const nameEl = document.createElement("button");
    nameEl.classList.add("show-event-form");
    nameEl.textContent = name;
    nameEl.addEventListener("click", showForm);
    return nameEl;
}

function makeUserEl(name) {
    const userEl = document.createElement("span");
    userEl.textContent = `[by ${name}]`; // should it be in squared braces?
    return userEl;
}

function makeEventDataEl(event) {
    const eventDataContainerEl = document.createElement("div");
    eventDataContainerEl.classList.add("unprocessed-event-data");
    // IMPORTANT INFO
    // location
    // description (careful, HTML!, but I might want to show as normal text)
    // "and link to?"
    eventDataContainerEl.appendChild(makeDateEl(event.datetime_start, event.datetime_end));
    eventDataContainerEl.appendChild(makeNameEl(event.name));
    // can I use flexbox to prevent using br?
    eventDataContainerEl.appendChild(document.createElement("br"));

    // more here
    const placeholderEl = document.createElement("span");
    placeholderEl.textContent = "placeholder";
    eventDataContainerEl.appendChild(placeholderEl);
    eventDataContainerEl.appendChild(makeUserEl(event.username));
    return eventDataContainerEl;
}

function makeButtonsContainerEl() {
    const buttonsContainerEl = document.createElement("span");
    buttonsContainerEl.classList.add("state-buttons");
    buttonsContainerEl.appendChild(makeProcessButton("approve"));
    buttonsContainerEl.appendChild(makeProcessButton("reject"));
    return buttonsContainerEl;
}

function start() {
    const listEl = document.querySelector("#unprocessed-container");
    const events = helper.getEvents();

    events.forEach(event => {
        const eventEl = document.createElement("div");
        eventEl.setAttribute("data-eventid", event.eventid);
        eventEl.classList.add(["unprocessed-event"]);

        // event data
        const eventDataEl = makeEventDataEl(event);
        eventEl.appendChild(eventDataEl);

        // buttons
        const buttonsEl = makeButtonsContainerEl();
        eventEl.appendChild(buttonsEl);

        listEl.appendChild(eventEl);
    });
}

