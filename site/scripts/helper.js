// make a map/dict with database index as key instead of an array
// for getting all the details for the event pop-overs
export const getEvents = () => {
    let events;
    if (sessionStorage.getItem("events") === null) {
        events = [];
    } else {
        events = JSON.parse(sessionStorage.getItem("events"), (key, value) => {
            const dates = ["datetime_end", "datetime_start", "last_change", "datetime_creation"];
            return dates.includes(key) ? new Date(value) : value;
        });
    }
    return events;
}

export function getEventById(eventid) {
    const events = getEvents();
    return events.find((element) => element.eventid === eventid);
}