function reqListener () {
    sessionStorage.setItem("events", this.responseText);
}

const req = new XMLHttpRequest();
req.addEventListener("load", reqListener);
//should it be called async or not?
req.open("GET", "get-events.php", true);
req.send();

const getEvents = () => {
    let events;
    if(sessionStorage.getItem('events') === null){
        events = [];
    }else {
        events = JSON.parse(sessionStorage.getItem('events'));
    }
    return events;

}
getEvents();



  