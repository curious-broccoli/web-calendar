export function getUrlParams(state = null, start = null, end = null) {
    const urlParams = new URLSearchParams();
    if (state != null && state != undefined) {
        urlParams.set("state", state);
    }
    if (start != null && start != undefined) {
        urlParams.set("start", start);
    }
    if (end != null && end != undefined) {
        urlParams.set("end", end);
    }
    return urlParams.toString();
}

// make a map/dict with database index as key instead of an array
// for getting all the details for the event pop-overs
export const getEvents = () => {
    let events;
    if (sessionStorage.getItem("events") === null) {
        events = [];
    } else {
        events = JSON.parse(sessionStorage.getItem("events"), (key, value) =>
            key === "datetime_end" || key === "datetime_start"
                ? new Date(value)
                : value
        );
    }
    return events;
}