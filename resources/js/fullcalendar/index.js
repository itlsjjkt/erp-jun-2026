import * as $ from 'jquery';
import 'fullcalendar';
import 'fullcalendar/dist/fullcalendar.min.css';

export default (function() {

    $('#calendar').fullCalendar({
        weekends: false
    });

}());