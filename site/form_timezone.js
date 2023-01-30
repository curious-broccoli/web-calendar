// JS for the creating a new event page

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", handleTimezones);
}
else {
    handleTimezones();
}

function getUtcInputElement(dateElement, timeElement, valueName) {
    const dateTime = new Date(dateElement.value + "T" + timeElement.value).toISOString();
    const dateTimeElement = document.createElement("input");
    dateTimeElement.name = valueName;
    dateTimeElement.value = dateTime;
    return dateTimeElement;
}

function roundToFullHour(hoursToAdd = 1) {
    let d = new Date();
    d.setHours(d.getHours() + hoursToAdd);
    d.setMinutes(0);
    return d;
}

function handleTimezones() {
    const form = document.querySelector('form');
    const dateStart = document.querySelector('#date_start');
    const timeStart = document.querySelector('#time_start');
    const dateEnd = document.querySelector('#date_end');
    const timeEnd = document.querySelector('#time_end');

    // if it is e.g. 15:59 and changes to 16:00 between the 1st and 2nd time
    // this function is called then it will result in the default end time
    // being 2 hours later
    const startDefault = roundToFullHour();
    const endDefault = roundToFullHour(2);
    // Swedish locale easily sets it to the ISO 8601 format
    // which is used by the "date" input element
    const locale = "sv";
    dateStart.value = startDefault.toLocaleDateString(locale);
    dateEnd.value = endDefault.toLocaleDateString(locale);
    const options = { timeStyle: "short" };
    timeStart.value = startDefault.toLocaleTimeString(locale, options);
    timeEnd.value = endDefault.toLocaleTimeString(locale, options);

    // submit event is fired only if the automatic HTML form validation was successful
    form.addEventListener('submit', () => {
        const start = getUtcInputElement(dateStart, timeStart, "datetime_start");
        const end = getUtcInputElement(dateEnd, timeEnd, "datetime_end");
        // make new elements for new timestamps
        // and prevent the local time field values from being submitted
        // because they aren't needed
        // but it flashes new input fields for a short time before being submitted (ugly)
        // maybe disable something instead? disabled will not be submitted
        form.replaceChild(start, dateStart);
        timeStart.remove();
        form.replaceChild(end, dateEnd);
        timeEnd.remove();
    });
}