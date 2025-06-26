import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';
import './styles/login.css';
import './styles/register.css';
import './styles/dashboard.css';
import './styles/communication.css';
import './styles/googlecalendar.css';
import './styles/form.css';
import './styles/client.css';
import './styles/company.css';

import './js/calendar.js';
import './js/dashboard.js';
import './js/communication.js';

import zoomPlugin from 'chartjs-plugin-zoom';

// register globally for all charts
document.addEventListener('chartjs:init', function (event) {
    const Chart = event.detail.Chart;
    Chart.register(zoomPlugin);
});


console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
