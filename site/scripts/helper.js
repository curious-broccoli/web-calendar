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

/**
 *
 * @param {int} eventid
 * @returns {object}
 */
export function getEventById(eventid) {
    const events = getEvents();
    return events.find((element) => element.eventid === eventid);
}

/**
 *
 * @param {string} date date in ISO 8601
 * @param {string} time hours and minutes in ISO 8601
 */
export function getUtcString(date, time) {
    // create a date with the input interpreted as local time
    const d = new Date(date + "T" + time);
    return d.toISOString();
}

// would be better if these values were taken from the database somehow
// to keep them up to date
export const role = Object.freeze({
    default: 10,
    approver: 20,
    moderator: 30,
});

/**
 *
 * @param {role} role the role you want to check for (pass one of the role's properties)
 * @returns
 */
export function isRole(role) {
    const roleId = document.body.dataset.role;
    if (roleId) {
        return role === +roleId;
    } else {
        return false;
    }
}