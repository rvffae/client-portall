import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/company.scss';
import './styles/app.scss';
import './styles/login.scss';
import './styles/register.scss';
import './styles/dashboard.scss';
import './styles/communication.scss';
import './styles/googlecalendar.scss';
import './styles/form.scss';
import './styles/client.scss';

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
