import Chart from 'chart.js/auto';

const dataElement = document.getElementById('accessJusticeAnalyticsData');
const analytics = dataElement ? JSON.parse(dataElement.textContent || '{}') : {};
const lineCanvas = document.getElementById('accessJusticeAnalyticsChart');
const originCanvas = document.getElementById('accessJusticeOriginChart');

const commonFont = {
    family: "Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif",
};

if (lineCanvas && analytics.chart) {
    const chart = analytics.chart;
    const lineValues = [...(chart.portal || []), ...(chart.organization || [])]
        .map(Number)
        .filter(Number.isFinite);
    const lineMax = Math.max(0, ...lineValues);

    Chart.getChart(lineCanvas)?.destroy();

    new Chart(lineCanvas, {
        type: 'line',
        data: {
            labels: chart.labels || [],
            datasets: [
                {
                    label: 'Pulso Venezuela',
                    data: chart.portal || [],
                    borderColor: '#0b2547',
                    backgroundColor: 'rgba(11, 37, 71, .035)',
                    borderWidth: 2,
                    pointBackgroundColor: '#0b2547',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 1.5,
                    pointRadius: 2,
                    pointHoverRadius: 4,
                    tension: .35,
                    fill: true,
                },
                {
                    label: 'Acceso a la Justicia',
                    data: chart.organization || [],
                    borderColor: '#f97316',
                    backgroundColor: 'rgba(249, 115, 22, .035)',
                    borderWidth: 2,
                    pointBackgroundColor: '#f97316',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 1.5,
                    pointRadius: 2,
                    pointHoverRadius: 4,
                    tension: .35,
                    fill: true,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: { top: 10, right: 10, bottom: 5, left: 5 },
            },
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: {
                    align: 'start',
                    position: 'top',
                    labels: {
                        boxHeight: 7,
                        boxWidth: 7,
                        color: '#607086',
                        font: { ...commonFont, size: 11, weight: 500 },
                        padding: 12,
                        pointStyle: 'circle',
                        usePointStyle: true,
                    },
                },
                tooltip: {
                    backgroundColor: '#14233a',
                    bodyFont: { ...commonFont, size: 12 },
                    cornerRadius: 8,
                    padding: 10,
                    titleFont: { ...commonFont, size: 12, weight: 600 },
                },
            },
            scales: {
                x: {
                    border: { display: false },
                    grid: { display: false },
                    ticks: {
                        autoSkip: true,
                        color: '#8a98aa',
                        font: { ...commonFont, size: 10 },
                        maxRotation: 0,
                        maxTicksLimit: 10,
                    },
                },
                y: {
                    beginAtZero: true,
                    border: { display: false },
                    grid: { color: 'rgba(15, 35, 62, .055)', drawTicks: false },
                    suggestedMax: Math.max(lineMax + 1, 3),
                    ticks: { color: '#8a98aa', font: { ...commonFont, size: 10 }, padding: 9, precision: 0 },
                },
            },
        },
    });
}

if (originCanvas && analytics.origin) {
    const fromPulso = Number(analytics.origin.pulso) || 0;
    const directAccess = Number(analytics.origin.direct) || 0;
    const total = Number(analytics.origin.total) || 0;
    const percentage = total > 0 ? Math.round((fromPulso / total) * 100) : 0;
    const centerLabel = {
        id: 'accessJusticeCenterLabel',
        afterDraw(chart) {
            const { ctx, chartArea } = chart;

            if (!chartArea) return;

            const x = (chartArea.left + chartArea.right) / 2;
            const y = (chartArea.top + chartArea.bottom) / 2;

            ctx.save();
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillStyle = '#14233a';
            ctx.font = `700 27px ${commonFont.family}`;
            ctx.fillText(`${percentage}%`, x, y - 8);
            ctx.fillStyle = '#718096';
            ctx.font = `500 11px ${commonFont.family}`;
            ctx.fillText('Desde Pulso', x, y + 17);
            ctx.restore();
        },
    };

    Chart.getChart(originCanvas)?.destroy();

    new Chart(originCanvas, {
        type: 'doughnut',
        data: {
            labels: ['Desde Pulso', 'Acceso directo'],
            datasets: [{
                data: [fromPulso, directAccess],
                backgroundColor: ['#f97316', '#2563eb'],
                borderColor: '#ffffff',
                borderWidth: 1,
                hoverBorderWidth: 1,
                hoverOffset: 2,
            }],
        },
        plugins: [centerLabel],
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#14233a',
                    bodyFont: { ...commonFont, size: 12 },
                    callbacks: {
                        label: context => ` ${context.label}: ${context.raw}`,
                    },
                    cornerRadius: 8,
                    padding: 10,
                },
            },
        },
    });
}

const drawAccessJusticeSparkline = canvas => {
    let series;

    try {
        series = JSON.parse(canvas.dataset.series || '[]').map(Number);
    } catch {
        series = [];
    }

    const draw = () => {
        const rect = canvas.getBoundingClientRect();
        const width = Math.max(1, Math.round(rect.width));
        const height = Math.max(1, Math.round(rect.height));
        const ratio = window.devicePixelRatio || 1;
        const context = canvas.getContext('2d');

        if (!context) return;

        canvas.width = width * ratio;
        canvas.height = height * ratio;
        context.setTransform(ratio, 0, 0, ratio, 0, 0);
        context.clearRect(0, 0, width, height);

        if (series.length === 0) return;

        const maxValue = Math.max(1, ...series);
        const horizontalStep = series.length > 1 ? width / (series.length - 1) : width;
        const verticalPadding = 3;

        context.beginPath();
        series.forEach((value, index) => {
            const x = series.length > 1 ? index * horizontalStep : width / 2;
            const y = height - verticalPadding - ((value / maxValue) * (height - (verticalPadding * 2)));

            if (index === 0) context.moveTo(x, y);
            else context.lineTo(x, y);
        });
        context.strokeStyle = '#16a36a';
        context.lineCap = 'round';
        context.lineJoin = 'round';
        context.lineWidth = 2;
        context.stroke();
    };

    draw();

    canvas._accessJusticeSparklineResizeObserver?.disconnect();

    if (typeof ResizeObserver !== 'undefined') {
        canvas._accessJusticeSparklineResizeObserver = new ResizeObserver(draw);
        canvas._accessJusticeSparklineResizeObserver.observe(canvas);
    }
};

const drawAccessJusticeSparklines = root => {
    if (!root?.querySelectorAll) return;

    root.querySelectorAll('.access-justice-sparkline').forEach(drawAccessJusticeSparkline);
};

drawAccessJusticeSparklines(document);

const registerLivewireSparklineHook = () => {
    if (!window.Livewire) return;

    Livewire.hook('morph.updated', ({ el }) => {
        if (el?.matches?.('.access-justice-ranking') || el?.querySelector?.('.access-justice-ranking')) {
            drawAccessJusticeSparklines(el);
        }
    });
};

if (window.Livewire) registerLivewireSparklineHook();
else document.addEventListener('livewire:init', registerLivewireSparklineHook, { once: true });

const syncForm = document.getElementById('accessJusticeSyncForm');

syncForm?.addEventListener('submit', () => {
    const button = syncForm.querySelector('button[type="submit"]');
    const label = button?.querySelector('span');

    if (button) button.disabled = true;
    if (label && button?.dataset.loadingText) label.textContent = button.dataset.loadingText;
});

const syncStatusUrl = syncForm?.dataset.statusUrl;
let syncPoller = null;

const formatSyncDate = value => value
    ? new Intl.DateTimeFormat('es-VE', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
        timeZone: 'America/Caracas',
    }).format(new Date(value))
    : '';

const formatSyncRelative = value => {
    if (!value) return '';

    const minutes = Math.max(0, Math.floor((Date.now() - new Date(value).getTime()) / 60000));
    if (minutes < 1) return 'Hace menos de un minuto';
    if (minutes < 60) return `Hace ${minutes} ${minutes === 1 ? 'minuto' : 'minutos'}`;

    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `Hace ${hours} ${hours === 1 ? 'hora' : 'horas'}`;

    const days = Math.floor(hours / 24);
    return `Hace ${days} ${days === 1 ? 'día' : 'días'}`;
};

const updateSyncUi = payload => {
    const button = syncForm?.querySelector('button[type="submit"]');
    const label = button?.querySelector('span');
    const statusBadge = document.getElementById('accessJusticeSyncStatus');
    const count = document.getElementById('accessJusticeAlertsCount');
    const date = document.getElementById('accessJusticeLastSyncedAt');
    const relative = document.getElementById('accessJusticeLastSyncedRelative');

    if (count && Number.isFinite(Number(payload.alerts_count))) {
        count.textContent = new Intl.NumberFormat('es-VE').format(payload.alerts_count);
    }

    if (date && payload.last_synced_at) date.textContent = formatSyncDate(payload.last_synced_at);
    if (relative && payload.last_synced_at) relative.textContent = formatSyncRelative(payload.last_synced_at);

    if (statusBadge && payload.status) {
        const statusMap = {
            success: ['access-justice-status--success', 'Sincronización exitosa'],
            running: ['access-justice-status--running', 'Sincronizando...'],
            failed: ['access-justice-status--failed', 'Error en sincronización'],
        };
        const [className, text] = statusMap[payload.status] || statusMap.failed;
        statusBadge.className = `access-justice-status ${className}`;
        statusBadge.innerHTML = `<i class="fa-solid fa-circle"></i> ${text}`;
    }

    if (button && payload.status !== 'running') {
        button.disabled = false;
        if (label) label.textContent = 'Sincronizar ahora';
    }
};

const stopSyncPolling = () => {
    if (syncPoller) window.clearInterval(syncPoller);
    syncPoller = null;
};

const pollSyncStatus = async () => {
    if (!syncStatusUrl) return;

    try {
        const response = await fetch(syncStatusUrl, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });
        if (!response.ok) return;

        const payload = await response.json();
        updateSyncUi(payload);
        if (payload.status !== 'running') stopSyncPolling();
    } catch {
        // El siguiente ciclo reintentará mientras la sincronización siga activa.
    }
};

if (syncForm?.dataset.syncStatus === 'running') {
    syncPoller = window.setInterval(pollSyncStatus, 5000);
    pollSyncStatus();
}
