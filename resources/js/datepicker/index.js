import * as $ from 'jquery';
import 'bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js';
import 'bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css';

export default (function() {
    $('.start-date').datepicker();
    $('.end-date').datepicker();
    $('.year').datepicker({
        minViewMode: 2,
        format: 'yyyy',
    });
}());