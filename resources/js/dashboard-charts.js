import {
	ArcElement,
	CategoryScale,
	Chart,
	DoughnutController,
	Filler,
	Legend,
	LineController,
	LineElement,
	LinearScale,
	PointElement,
	Tooltip,
} from 'chart.js';

Chart.register(
	ArcElement,
	CategoryScale,
	DoughnutController,
	Filler,
	Legend,
	LineController,
	LineElement,
	LinearScale,
	PointElement,
	Tooltip,
);

// Categorical hues, assigned by slot index and never cycled — a 9th series
// would repeat a hue, so the backend folds anything past slot 8 into "Other".
const SERIES = ['#2a78d6', '#eb6834', '#1baf7a', '#eda100', '#e87ba4', '#008300', '#4a3aa7', '#e34948'];

const SURFACE = '#ffffff';
const INK_SECONDARY = '#52514e';
const INK_MUTED = '#898781';
const GRIDLINE = '#e1e0d9';

Chart.defaults.font.family = 'system-ui, -apple-system, "Segoe UI", sans-serif';
Chart.defaults.color = INK_SECONDARY;

/**
 * Reads the JSON payload rendered alongside a canvas.
 * Returns null when the block is absent or carries no data points.
 */
function readChartData(name) {
	const holder = document.getElementById(`${name}-data`);
	if (!holder) return null;

	try {
		const payload = JSON.parse(holder.textContent);
		return payload?.labels?.length ? payload : null;
	} catch (error) {
		console.error(`Invalid chart payload for "${name}"`, error);
		return null;
	}
}

function renderAssetsByCategory() {
	const canvas = document.querySelector('[data-chart="assets-by-category"]');
	const payload = readChartData('assets-by-category');
	if (!canvas || !payload) return;

	const total = payload.data.reduce((sum, value) => sum + value, 0);

	new Chart(canvas, {
		type: 'doughnut',
		data: {
			labels: payload.labels,
			datasets: [{
				data: payload.data,
				backgroundColor: payload.labels.map((_, index) => SERIES[index]),
				borderColor: SURFACE,
				borderWidth: 2,
				hoverOffset: 6,
			}],
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			cutout: '62%',
			plugins: {
				legend: {
					position: 'right',
					labels: { boxWidth: 12, boxHeight: 12, usePointStyle: true, pointStyle: 'circle', padding: 16 },
				},
				tooltip: {
					callbacks: {
						label: (context) => {
							const value = context.parsed;
							const share = total ? ((value / total) * 100).toFixed(1) : '0.0';
							return ` ${context.label}: ${value} (${share}%)`;
						},
					},
				},
			},
		},
	});
}

function renderDeploymentTrend() {
	const canvas = document.querySelector('[data-chart="deployment-trend"]');
	const payload = readChartData('deployment-trend');
	if (!canvas || !payload) return;

	new Chart(canvas, {
		type: 'line',
		data: {
			labels: payload.labels,
			datasets: [{
				label: 'Deployments',
				data: payload.data,
				borderColor: SERIES[0],
				backgroundColor: 'rgba(42, 120, 214, 0.08)',
				borderWidth: 2,
				pointRadius: 4,
				pointHoverRadius: 8,
				pointBackgroundColor: SERIES[0],
				tension: 0.3,
				fill: true,
			}],
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			// Single series: the panel title names it, so no legend box.
			plugins: { legend: { display: false } },
			interaction: { mode: 'index', intersect: false },
			scales: {
				x: {
					grid: { display: false },
					border: { color: GRIDLINE },
					ticks: { color: INK_MUTED },
				},
				y: {
					beginAtZero: true,
					grid: { color: GRIDLINE },
					border: { display: false },
					ticks: { color: INK_MUTED, precision: 0 },
				},
			},
		},
	});
}

document.addEventListener('DOMContentLoaded', () => {
	renderAssetsByCategory();
	renderDeploymentTrend();
});
