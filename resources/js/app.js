import flatpickr from "flatpickr";

import { Chart, registerables } from 'chart.js';
import 'chartjs-adapter-date-fns';

window.Chart = Chart;
Chart.register(...registerables);
