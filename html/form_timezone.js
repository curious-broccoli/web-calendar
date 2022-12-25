if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", addHandler);
}
else {
    addHandler();
}

function getUtcInputElement(dateElement, timeElement, valueName) {
    const dateTime = new Date(dateElement.value + "T" + timeElement.value).toISOString();
    const dateTimeElement = document.createElement("input");
    dateTimeElement.name = valueName;
    dateTimeElement.value = dateTime;
    //MAKE IT HIDDEN
    return dateTimeElement;
}

function addHandler() {
    const form = document.querySelector('form');
    const dateStart = document.querySelector('#date_start');
    const timeStart = document.querySelector('#time_start');
    const dateEnd = document.querySelector('#date_end');
    const timeEnd = document.querySelector('#time_end');
    // submit event is fired only if the automatic HTML form validation was successful
    form.addEventListener('submit', () => {
        const start = getUtcInputElement(dateStart, timeStart, "datetime_start");
        const end = getUtcInputElement(dateEnd, timeEnd, "datetime_end");
        // make new elements for new timestamps
        // and prevent the local time field values from being submitted
        // because it feels cleaner
        // but it shows ugly textboxes for a short time before being submitted
        form.replaceChild(start, dateStart);
        timeStart.remove();
        form.replaceChild(end, dateEnd);
        timeEnd.remove();
    });
}