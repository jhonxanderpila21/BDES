import {
	ArcElement,
	CategoryScale,
	Chart,
	DoughnutController,
	Legend,
	Tooltip,
} from 'chart.js';

Chart.register(
	ArcElement,
	CategoryScale,
	DoughnutController,
	Legend,
	Tooltip,
);

// Categorical hues, assigned by slot index and never cycled — a 9th series
// would repeat a hue, so the backend folds anything past slot 8 into "Other".
const SERIES = ['#2a78d6', '#eb6834', '#1baf7a', '#eda100', '#e87ba4', '#008300', '#4a3aa7', '#e34948'];

const SURFACE = '#ffffff';
const INK_SECONDARY = '#52514e';

Chart.defaults.font.family = 'system-ui, -apple-system, "Segoe UI", sans-serif';
Chart.defaults.color = INK_SECONDARY;

/**
 * Custom Chart.js plugin to draw total count & descriptor right inside the center cutout circle.
 */
const centerTotalPlugin = {
	id: 'centerTotal',
	beforeDraw(chart) {
		const { ctx } = chart;
		ctx.save();
		const dataset = chart.data.datasets[0];
		const total = dataset?.data?.reduce((sum, value) => sum + value, 0) || 0;

		const meta = chart.getDatasetMeta(0);
		if (!meta || !meta.data || !meta.data[0]) {
			ctx.restore();
			return;
		}

		const centerX = meta.data[0].x;
		const centerY = meta.data[0].y;

		ctx.textAlign = 'center';
		ctx.textBaseline = 'middle';

		// Equipment Total
		ctx.font = 'bold 26px system-ui, -apple-system, sans-serif';
		ctx.fillStyle = '#0f172a';
		ctx.fillText(total.toLocaleString(), centerX, centerY - 10);

		// Units Tagline
		ctx.font = '700 10px system-ui, -apple-system, sans-serif';
		ctx.fillStyle = '#64748b';
		ctx.fillText('TOTAL ASSETS', centerX, centerY + 14);

		ctx.restore();
	},
};

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
				hoverOffset: 8,
			}],
		},
		plugins: [centerTotalPlugin],
		options: {
			responsive: true,
			maintainAspectRatio: false,
			cutout: '66%',
			plugins: {
				legend: {
					position: 'right',
					labels: {
						boxWidth: 12,
						boxHeight: 12,
						usePointStyle: true,
						pointStyle: 'circle',
						padding: 12,
						font: {
							size: 11,
							weight: '500',
						},
						generateLabels: (chart) => {
							const data = chart.data;
							if (data.labels.length && data.datasets.length) {
								const dataset = data.datasets[0];
								return data.labels.map((label, i) => {
									const value = dataset.data[i] || 0;
									const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : '0.0';
									return {
										text: `${label}: ${value} (${percentage}%)`,
										fillStyle: dataset.backgroundColor[i],
										strokeStyle: dataset.borderColor,
										lineWidth: dataset.borderWidth,
										hidden: chart.getDatasetMeta(0).data[i]?.hidden || false,
										index: i,
									};
								});
							}
							return [];
						},
					},
				},
				tooltip: {
					callbacks: {
						label: (context) => {
							const value = context.parsed;
							const share = total ? ((value / total) * 100).toFixed(1) : '0.0';
							return ` ${context.label}: ${value} units (${share}%)`;
						},
					},
				},
			},
		},
	});
}

document.addEventListener('DOMContentLoaded', () => {
	renderAssetsByCategory();
});
