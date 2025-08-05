document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('sem-calendar');
    if (!calendarEl || typeof FullCalendar === 'undefined') return;

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: sem_events.events
    });
    calendar.render();
});
