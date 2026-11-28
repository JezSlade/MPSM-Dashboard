const colors = {
    black: { label: 'Black', stroke: '#c8d0df' },
    cyan: { label: 'Cyan', stroke: '#38d8ef' },
    magenta: { label: 'Magenta', stroke: '#f368c3' },
    yellow: { label: 'Yellow', stroke: '#f3d65c' },
};
const lowTonerLevel = 25;
const maximumGap = 3 * 24 * 60 * 60 * 1000;
const dateLabel = new Intl.DateTimeFormat(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
const shortDateLabel = new Intl.DateTimeFormat(undefined, { month: 'short', day: 'numeric', hour: 'numeric' });
const readingLabel = new Intl.DateTimeFormat(undefined, { dateStyle: 'medium', timeStyle: 'short' });
let chartLibrary;

function loadChartLibrary() {
    chartLibrary ??= new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
        script.onload = () => typeof window.Chart === 'function' ? resolve(window.Chart) : reject(new Error('Chart unavailable'));
        script.onerror = () => {
            script.remove();
            reject(new Error('Chart unavailable'));
        };
        document.head.appendChild(script);
    }).catch(error => {
        chartLibrary = undefined;
        throw error;
    });
    return chartLibrary;
}

function makeDatasets(readings, theme) {
    const points = [];
    for (const reading of readings) {
        const x = Date.parse(reading.at);
        if (!Number.isFinite(x)) continue;
        const previous = points.at(-1);
        if (previous && x - previous.x > maximumGap) {
            points.push({ x: previous.x + 1 });
        }
        points.push({ ...reading, x });
    }

    return Object.entries(colors).flatMap(([color, { label, stroke }]) => {
        const data = points.map(point => ({ x: point.x, y: point[color] ?? null }));
        if (!data.some(point => point.y !== null)) return [];
        const lineColor = theme.getPropertyValue(`--ta-chart-${color}`).trim() || stroke;
        return [{
            label,
            data,
            borderColor: lineColor,
            backgroundColor: lineColor,
            borderWidth: 2,
            pointRadius: data.length <= 45 ? 2 : 0,
            pointHoverRadius: 5,
            spanGaps: false,
        }];
    });
}

const lowTonerReference = {
    id: 'lowTonerReference',
    beforeDatasetsDraw({ ctx, chartArea, scales }, _args, options) {
        const y = scales.y.getPixelForValue(lowTonerLevel);
        ctx.save();
        ctx.strokeStyle = options.color;
        ctx.setLineDash([4, 5]);
        ctx.beginPath();
        ctx.moveTo(chartArea.left, y);
        ctx.lineTo(chartArea.right, y);
        ctx.stroke();
        ctx.restore();
    },
};

function drawHistory(Chart, canvas, datasets, theme) {
    const points = datasets[0].data;
    const first = points[0].x;
    const last = points.at(-1).x;
    const padding = first === last ? 6 * 60 * 60 * 1000 : 0;
    const tickLabel = last - first < 48 * 60 * 60 * 1000 ? shortDateLabel : dateLabel;
    const tickColor = theme.getPropertyValue('--ta-text-dim').trim() || '#9a9aa2';
    const gridColor = theme.getPropertyValue('--ta-border').trim() || '#ffffff15';
    const legendColor = theme.getPropertyValue('--ta-text').trim() || '#e8e8ea';
    return new Chart(canvas, {
        type: 'line',
        data: { datasets },
        plugins: [lowTonerReference],
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: false,
            parsing: false,
            interaction: { mode: 'index', axis: 'x', intersect: false },
            scales: {
                x: {
                    type: 'linear',
                    min: first - padding,
                    max: last + padding,
                    grid: { display: false },
                    ticks: {
                        color: tickColor,
                        maxTicksLimit: 4,
                        maxRotation: 0,
                        callback: value => tickLabel.format(value),
                    },
                },
                y: {
                    min: 0,
                    max: 100,
                    grid: { color: gridColor },
                    ticks: { color: tickColor, stepSize: 25, callback: value => `${value}%` },
                },
            },
            plugins: {
                lowTonerReference: { color: tickColor },
                legend: { labels: { color: legendColor, usePointStyle: true, boxWidth: 8 } },
                tooltip: {
                    callbacks: {
                        title: items => items.length ? readingLabel.format(items[0].parsed.x) : '',
                        label: item => `${item.dataset.label} ${item.parsed.y}%`,
                    },
                },
            },
        },
    });
}

export function mountTonerHistory(section, deviceParams, fetchTonerJson) {
    const content = section.querySelector('.ta-history-body');
    content.innerHTML = `
        <label>History range
            <select>
                <option value="30">Last 30 days</option>
                <option value="90" selected>Last 90 days</option>
                <option value="365">Last year</option>
                <option value="all">All available</option>
            </select>
        </label>
        <p class="ta-history-status" role="status"></p>
        <button type="button" hidden>Retry</button>
        <figure hidden>
            <div class="ta-history-chart"><canvas role="img" aria-label="Recorded toner levels by date"></canvas></div>
            <figcaption>Dashed line shows the 25% low-toner reference. Gaps mark missing readings or more than three days between samples.</figcaption>
        </figure>
    `;

    const range = content.querySelector('select');
    const status = content.querySelector('.ta-history-status');
    const retry = content.querySelector('button');
    const figure = content.querySelector('figure');
    const canvas = content.querySelector('canvas');
    const cache = new Map();
    let chart;
    let request = 0;

    async function refresh() {
        const current = ++request;
        const selection = range.value;
        chart?.destroy();
        chart = undefined;
        figure.hidden = true;
        retry.hidden = true;
        status.textContent = 'Loading toner history…';

        try {
            let readings = cache.get(selection);
            if (!readings) {
                const params = new URLSearchParams(deviceParams);
                params.set('range', selection);
                const data = await fetchTonerJson('api/get-toner-history.php?' + params.toString(), { timeoutMs: 22000 });
                readings = data.readings;
                cache.set(selection, readings);
            }
            if (current !== request) return;
            const theme = getComputedStyle(canvas);
            const datasets = makeDatasets(readings, theme);
            if (!datasets.length) {
                status.textContent = 'No toner readings in this range.';
                return;
            }
            const Chart = await loadChartLibrary();
            if (current !== request) return;
            figure.hidden = false;
            chart = drawHistory(Chart, canvas, datasets, theme);
            status.textContent = '';
        } catch {
            if (current !== request) return;
            figure.hidden = true;
            status.textContent = 'Toner history could not load.';
            retry.hidden = false;
        }
    }

    range.addEventListener('change', refresh);
    retry.addEventListener('click', refresh);
    refresh();
    return () => {
        request++;
        chart?.destroy();
        range.removeEventListener('change', refresh);
        retry.removeEventListener('click', refresh);
    };
}

/*
CHANGELOG
2026-11-28 Codex
- Add an on-demand CMYK history chart with date ranges, actual readings, and explicit gaps.
- Use the selected theme's colors for the history lines, axes, and reference line.
*/
