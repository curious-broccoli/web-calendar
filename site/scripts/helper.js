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