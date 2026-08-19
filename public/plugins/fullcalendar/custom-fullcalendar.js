document.addEventListener('DOMContentLoaded', function() {

    // Date variable
    var newDate = new Date();

    /** 
     * 
     * @getDynamicMonth() fn. is used to validate 2 digit number and act accordingly 
     * 
    */    
    function getDynamicMonth() {
        getMonthValue = newDate.getMonth();
        _getUpdatedMonthValue = getMonthValue + 1;
        if (_getUpdatedMonthValue < 10) {
            return `0${_getUpdatedMonthValue}`;
        } else {
            return `${_getUpdatedMonthValue}`;
        }
    }

    // Modal Elements
    var modalTitleEl = document.querySelector('#exampleModalLabel');
    var getModalTitleEl = document.querySelector('#event-title');
    var getModalStartDateEl = document.querySelector('#event-start-date');
    var getModalEndDateEl = document.querySelector('#event-end-date');

    // Calendar Elements and options
    var calendarEl = document.querySelector('.calendar');

    var checkWidowWidth = function() {
        if (window.innerWidth <= 1199) {
            return true;
        } else {
            return false;
        }
    }
    
    var calendarHeaderToolbar = {
        left: 'prev next',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
    }

    // Calendar eventClick fn.
    var calendarEventClick = function(info) {
        var eventObj = info.event;
        console.log(eventObj.extendedProps.description);

        if (eventObj.url) {
          window.open(eventObj.url);
  
          info.jsEvent.preventDefault(); // prevents browser from following link in current tab.
        } else {
            var getModalEventId = eventObj._def.publicId; 
            var getModalEventLevel = eventObj._def.extendedProps['calendar'];

            getModalTitleEl.textContent  = eventObj.extendedProps.description;
            modalTitleEl.innerHTML = eventObj.title;
            myModal.show();
        }
    }
    

    // Activate Calender    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        selectable: false,
        dayMaxEventRows: true, // for all non-TimeGrid views
        height: checkWidowWidth() ? 900 : 1052,
        initialView: checkWidowWidth() ? 'listWeek' : 'dayGridMonth',
        initialDate: `${newDate.getFullYear()}-${getDynamicMonth()}-07`,
        headerToolbar: calendarHeaderToolbar,
        eventSources: [
            {
                url: '/event/calendarEvent',
                method: 'GET',
                failure: function() {
                    alert('There was an error while fetching events!');
                },
                // modify the events to include an inclusive end date
                success: function(events) {
                    events.forEach(function(event) {
                    var endDate = new Date(event.end);
                    endDate.setDate(endDate.getDate() + 1); // add one day to the end date
                    event.end = endDate.toISOString().substring(0, 10); // format the end date as a string
                    });
                }
            },
        ],
        unselect: function() {
            console.log('unselected')
        },
        eventClick: calendarEventClick,
        windowResize: function(arg) {
            if (checkWidowWidth()) {
                calendar.changeView('listWeek');
                calendar.setOption('height', 900);
            } else {
                calendar.changeView('dayGridMonth');
                calendar.setOption('height', 1052);
            }
        }

    });
    
    // Calendar Renderation
    calendar.render();
    
    var myModal = new bootstrap.Modal(document.getElementById('exampleModal'))
    //var modalToggle = document.querySelector('.fc-addEventButton-button ')

    document.getElementById('exampleModal').addEventListener('hidden.bs.modal', function (event) {
        getModalTitleEl.value = '';
        getModalStartDateEl.value = '';
        getModalEndDateEl.value = '';
        var getModalIfCheckedRadioBtnEl = document.querySelector('input[name="event-level"]:checked');
        if (getModalIfCheckedRadioBtnEl !== null) { getModalIfCheckedRadioBtnEl.checked = false; }
    })
});