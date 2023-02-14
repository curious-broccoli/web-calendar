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
req.addEventListener("load", reqListener);
const params = new URLSearchParams();
// change here later to implement event filter
params.set("state", 0);
req.open("GET", "get-events.php?" + params);
req.send();

function clearErrors() {
    const eventErrorEl = document.querySelector("#state-error");
    eventErrorEl.textContent = "";
    eventErrorEl.classList.add("hidden");
    const formErrorEl = document.querySelector("#form-error");
    formErrorEl.textContent = "";
    formErrorEl.classList.add("hidden");
}

function showError(elementId, error) {
    const errorEl = document.querySelector(elementId);
    errorEl.textContent = error;
    errorEl.classList.remove("hidden");
}

/**
 * set the disabled state of the buttons. form buttons are only enabled for moderators.
 * @param {*} newState
 */
function setButtonsDisabled(newState) {
    document.querySelectorAll(".moderator-wrapper button")
        .forEach((button) => button.disabled = newState);

    const isModerator = helper.isRole(helper.role.moderator);
    document.querySelectorAll(".moderator-wrapper input[type=submit]")
        .forEach((button) => {
            if (isModerator) {
                button.disabled = newState;
            } else {
                button.disabled = true;
            }
        });
}

function onEditTimeout() {
    setButtonsDisabled(false);
    showError("#form-error", "Request took too long. Please try again.");
    document.querySelector("#spinner").classList.add("hidden");
}

// load event
function changeRequestComplete() {
    if (this.status === 200) {
        const data = this.response;
        if (data.action === "approve" || data.action === "reject") {
            if ("error" in data) {
                showError("#state-error", data.error);
            }
        } else if (data.action.includes("edit")) {
            document.querySelector("#spinner").classList.add("hidden");
            setButtonsDisabled(false);
            if ("error" in data) {
                showError("#form-error", data.error);
            } else if (data.action === "edit-approve") {
                const eventEl = document.querySelector(`div[data-eventid="${data.eventid}"]`);
                eventEl.classList.add("hidden");
                updateForm();
            }
            // else should it do something if it was just edited?
        } else {
            console.log(data);
        }
    } else {
        alert(`Change request responded with error: ${this.status} ${this.statusText}`);
    }
}

function postRequest(params) {
    // TODO: what if reject/approve is clicked while the form is open with another event
    // to prevent the user from approving/rejecting wrong event

    const req = new XMLHttpRequest();
    req.open("POST", "process-event.php?");
    // IF PARAMS.ACTION INCLUDES EDIT -> set timeout x, add event listener
    if (params.get("action").includes("edit")) {
        req.timeout = 20000;
        req.addEventListener("timeout", onEditTimeout);
    }
    req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    req.responseType = "json";
    req.addEventListener("load", changeRequestComplete);
    // how to test?
    req.addEventListener("error", () => alert("Request failed!\nPlease reload the page"));
    req.send(params);

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

    if (e.currentTarget.classList.contains("button-approve") || e.currentTarget.classList.contains("button-reject")) {
        const action = e.currentTarget.classList.contains("button-approve") ? "approve" : "reject";
        params.set("action", action);
        eventid = e.currentTarget.parentNode.parentNode.dataset.eventid;
        e.currentTarget.parentNode.parentNode.classList.add("hidden");
        updateForm();
    } else { // should I check here if it is really the form's button?
        params.set("action", e.currentTarget.id);
        const formParams = new URLSearchParams(new FormData(e.currentTarget.form));
        const formattedFormParams = makeUtcParams(formParams);
        for (const [key, val] of formattedFormParams.entries()) {
            params.set(key, val);
        }
        eventid = e.currentTarget.form.dataset.eventid;
        document.querySelector("#spinner").classList.remove("hidden");
        setButtonsDisabled(true);
    }
    params.set("eventid", eventid);
    const event = helper.getEventById(Number(eventid));
    params.set("last_change", event.last_change.toISOString());
    clearErrors();

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

function updateForm(e) {
    // TODO:
    // if moderator but not if approver? or disable

    const eventid = (function getEventId() {
        // for when I explicitly call the function without an event
        if (e === undefined) {
            // maybe instead of showing the first not hidden event in the form
            // show the next event after the one that was processed?
            const allEvents = document.querySelectorAll(".unprocessed-event");
            const eventEl = Array.from(allEvents).find(node => !node.classList.contains("hidden"));
            // highlight the event that is now shown in the form
            return eventEl?.dataset.eventid;
        } else {
            return e.currentTarget.parentNode.dataset.eventid;
        }
    })();

    const form = document.querySelector("#form");
    if (eventid === undefined) {
        form.reset();
        form.dataset.eventid = null;
    } else {
        const event = helper.getEventById(Number(eventid));
        fillForm(event);
        form.dataset.eventid = eventid;
    }
}

function makeNameEl(name) {
    const nameEl = document.createElement("span");
    nameEl.classList.add("event-name");
    nameEl.textContent = name;
    return nameEl;
}

function makeUserEl(name) {
    const userEl = document.createElement("span");
    userEl.textContent = `[by ${name}]`; // should it be shown? in squared braces?
    return userEl;
}

function makeEventDataEl(event) {
    const eventDataContainerEl = document.createElement("button");
    eventDataContainerEl.classList.add("unprocessed-event-data");
    eventDataContainerEl.addEventListener("click", updateForm);
    // IMPORTANT INFO
    // description (careful, HTML!, but I might want to show as normal text)
    eventDataContainerEl.appendChild(makeDateEl(event.datetime_start, event.datetime_end));
    eventDataContainerEl.appendChild(makeNameEl(event.name));
    // better way than <br>?
    eventDataContainerEl.appendChild(document.createElement("br"));

    const locationEl = document.createElement("span");
    locationEl.textContent = event.location;
    eventDataContainerEl.appendChild(locationEl);
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

    document.querySelectorAll("[type=submit]").forEach((button) => button.addEventListener("click", handleSubmit));
    updateForm();
    // enable form buttons for moderators
    setButtonsDisabled(false);
}

