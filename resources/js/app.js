import flatpickr from "flatpickr";
import { German } from "flatpickr/dist/l10n/de.js";
window.German = German;

import { Chart, registerables } from 'chart.js';
import 'chartjs-adapter-date-fns';

window.Chart = Chart;
Chart.register(...registerables);
