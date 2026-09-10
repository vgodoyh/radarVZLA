import Chart from 'chart.js/auto';

const dataElement = document.getElementById('obuDashboardData');

const legendMarginPlugin = {
    id: 'obuLegendMargin',
    beforeInit(chart) {
        const legend = chart.legend;
        if (!legend || legend.__obuMarginApplied) return;

        const originalFit = legend.fit;
        legend.fit = function fit() {
            originalFit.call(this);
            this.height += 18;
        };
        legend.__obuMarginApplied = true;
    },
};

const sourceLegendPlugin = {
    id: 'obuSourceLegendVisibility',
    beforeInit(chart) {
        chart.options.plugins.legend.display = false;
    },
};

if (dataElement) {
    const payload = JSON.parse(dataElement.textContent || '{}');
    const historical = payload.historical || [];
    const universityProtests = payload.universityProtests || [];
    const sources = payload.sources || [];
    const news = payload.news || [];
    const sourceChart = payload.sourceChart || {};
    const complaintsRightsChart = payload.complaintsRightsChart || {};
    const findValue = (rows, predicate) => Number(rows.find(predicate)?.value || 0);

    const historicalCanvas = document.getElementById('obuHistoricalComplaintsChart');
    if (historicalCanvas) {
        const years = [...new Set(historical.map(item => Number(item.year)))].sort((a, b) => a - b);
        const valuesFor = category => years.map(year => findValue(historical, item => Number(item.year) === year && item.category === category));
        new Chart(historicalCanvas, {
            type: 'line',
            data: { labels: years, datasets: [
                { label: complaintsRightsChart.economicSocial || '', data: valuesFor('economic_social'), borderColor: '#2373c8', pointBackgroundColor: '#2373c8', borderWidth: 2, tension: .25, pointRadius: 4, pointHoverRadius: 6, fill: false },
                { label: complaintsRightsChart.civilPolitical || '', data: valuesFor('civil_political'), borderColor: '#fd8700', pointBackgroundColor: '#fd8700', borderWidth: 2, tension: .25, pointRadius: 4, pointHoverRadius: 6, fill: false },
            ] },
            plugins: [legendMarginPlugin],
            options: { responsive: true, maintainAspectRatio: false, interaction: { intersect: false, mode: 'index' }, plugins: { legend: { display: true, position: 'top', labels: { boxWidth: 8, usePointStyle: true, padding: 8 } }, tooltip: { mode: 'index', intersect: false } }, scales: { x: { grid: { display: false } }, y: { beginAtZero: true, grid: { color: '#edf1f6' }, ticks: { precision: 0 } } } },
        });
    }

    const universityProtestsCanvas = document.getElementById('obuUniversityProtestsChart');
    if (universityProtestsCanvas) {
        const years = [...new Set(universityProtests.map(item => Number(item.year)))].sort((a, b) => a - b);
        const values = years.map(year => findValue(universityProtests, item => Number(item.year) === year));
        const colors = years.map((year, index) => index % 2 === 0 ? '#2373c8' : '#fd8700');
        const valueLabels = { id: 'obuUniversityProtestValues', afterDatasetsDraw(chart) {
            const { ctx } = chart;
            ctx.save();
            ctx.font = '700 10px Inter, sans-serif';
            ctx.textAlign = 'center';
            chart.getDatasetMeta(0).data.forEach((bar, index) => {
                ctx.fillStyle = colors[index];
                ctx.fillText(String(values[index]), bar.x, bar.y - 7);
            });
            ctx.restore();
        } };
        new Chart(universityProtestsCanvas, {
            type: 'bar',
            plugins: [valueLabels],
            data: { labels: years, datasets: [{ label: payload.protestsChart?.axis || '', data: values, backgroundColor: colors, borderRadius: 4, barPercentage: .68, categoryPercentage: .78 }] },
            options: { responsive: true, maintainAspectRatio: false, layout: { padding: { top: 18, right: 8, bottom: 0, left: 0 } }, plugins: { legend: { display: false }, tooltip: { callbacks: { label: item => `${item.dataset.label}: ${item.raw}` } } }, scales: { x: { grid: { display: false }, ticks: { font: { size: 10 } } }, y: { title: { display: true, text: payload.protestsChart?.axis || '', color: '#52647c', font: { size: 10 } }, beginAtZero: true, max: 120, grid: { color: '#e8eef6' }, ticks: { precision: 0, font: { size: 10 } } } } },
        });
    }

    const sourceCanvas = document.getElementById('obuComplaintSourcesChart');
    if (sourceCanvas) {
        const years = [...new Set(sources.map(item => Number(item.year)))].sort((a, b) => a - b);
        const series = sourceChart.series || [];
        const labels = series.map(item => item.label);
        const colors = ['#2373c8', '#4cbe92', '#7667c7', '#fd8700', '#25a7a0', '#8291a5'];
        const valueFor = key => years.map(year => findValue(sources, item => Number(item.year) === year && item.label === key));
        const chartWrap = sourceCanvas.closest('.obu-chart-wrap');
        const manualLegend = document.createElement('div');
        manualLegend.className = 'obu-source-chart-legend';
        labels.forEach((label, index) => {
            const item = document.createElement('span');
            item.className = 'obu-source-chart-legend__item';
            item.innerHTML = `<i style="background:${colors[index]}" aria-hidden="true"></i><span>${label}</span>`;
            manualLegend.appendChild(item);
        });
        chartWrap?.parentElement.insertBefore(manualLegend, chartWrap);
        const valueLabels = { id: 'obuComplaintSourceValues', afterDatasetsDraw(chart) {
            const { ctx } = chart;
            ctx.save();
            ctx.font = '600 9px Inter, sans-serif';
            ctx.textAlign = 'center';
            chart.data.datasets.forEach((dataset, datasetIndex) => chart.getDatasetMeta(datasetIndex).data.forEach((point, index) => {
                const values = dataset.data.map(Number);
                const maximum = Math.max(...values);
                if (index !== 0 && index !== values.length - 1 && values[index] !== maximum) return;
                ctx.fillStyle = dataset.borderColor;
                const offset = index % 2 === 0 ? 8 : -8;
                ctx.fillText(String(dataset.data[index]), point.x, point.y - offset);
            }));
            ctx.restore();
        } };
        new Chart(sourceCanvas, {
            type: 'line',
            data: { labels: years, datasets: series.map((item, index) => ({ label: item.label, data: valueFor(item.key), borderColor: colors[index], backgroundColor: colors[index], borderWidth: 2, tension: .22, pointRadius: 3, pointHoverRadius: 5, pointBackgroundColor: '#fff', pointBorderWidth: 2, fill: false })) },
            plugins: [valueLabels, legendMarginPlugin, sourceLegendPlugin],
            options: { responsive: true, maintainAspectRatio: false, interaction: { intersect: false, mode: 'index' }, layout: { padding: { top: 10, right: 8, bottom: 0, left: 0 } }, plugins: { legend: { display: true, position: 'top', align: 'start', labels: { boxWidth: 8, boxHeight: 8, usePointStyle: true, pointStyle: 'circle', padding: 6, color: '#52647c', font: { size: 10 } } }, tooltip: { mode: 'index', intersect: false } }, scales: { x: { title: { display: true, text: sourceChart.axisX || '', color: '#52647c', font: { size: 10 } }, grid: { display: false }, ticks: { font: { size: 10 } } }, y: { title: { display: true, text: sourceChart.axisY || '', color: '#52647c', font: { size: 10 } }, beginAtZero: true, grid: { color: '#e8eef6' }, ticks: { precision: 0, font: { size: 10 } } } } },
        });
    }

    const newsCanvas = document.getElementById('obuNewsTypeChart');
    if (newsCanvas) {
        const chartCopy = payload.newsChart || {};
        const subgroups = [
            { key: 'Experimentales y aut\u00f3nomas', label: chartCopy.no_controlled || 'No controladas' },
            { key: 'Controladas', label: chartCopy.controlled || 'Controladas' },
        ];
        const newsComplaints = news.filter(item => item.category === 'DENUNCIA' && subgroups.some(subgroup => item.subgroup === subgroup.key));
        const years = [...new Set(newsComplaints.map(item => Number(item.year)))].sort((a, b) => a - b);
        const chartData = subgroup => years.map(year => findValue(newsComplaints, item => Number(item.year) === year && item.subgroup === subgroup.key));
        const segmentLabels = { id: 'obuNewsTypeValues', afterDatasetsDraw(chart) {
            const { ctx } = chart;
            ctx.save();
            ctx.font = '600 10px Inter, sans-serif';
            chart.data.datasets.forEach((dataset, datasetIndex) => chart.getDatasetMeta(datasetIndex).data.forEach((bar, index) => {
                const value = Number(dataset.data[index] || 0);
                if (!value) return;

                if (datasetIndex === 0) {
                    ctx.fillStyle = '#fff';
                    ctx.textAlign = 'right';
                    ctx.fillText(String(value), bar.x - 6, bar.y + 4);
                } else {
                    ctx.fillStyle = '#0b2447';
                    ctx.textAlign = 'left';
                    ctx.fillText(String(value), Math.min(bar.x + 6, chart.chartArea.right - 16), bar.y + 4);
                }
            }));
            ctx.restore();
        } };
        new Chart(newsCanvas, {
            type: 'bar',
            plugins: [segmentLabels, legendMarginPlugin],
            data: { labels: years, datasets: [
                { label: subgroups[0].label, data: chartData(subgroups[0]), backgroundColor: '#2373c8', borderRadius: 4, barPercentage: .72, categoryPercentage: .78, stack: 'complaints' },
                { label: subgroups[1].label, data: chartData(subgroups[1]), backgroundColor: '#8bb9e8', borderRadius: 4, barPercentage: .72, categoryPercentage: .78, stack: 'complaints' },
            ] },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, interaction: { intersect: false, mode: 'index' }, plugins: { legend: { position: 'top', labels: { boxWidth: 12, usePointStyle: true, padding: 14 } }, tooltip: { callbacks: { title: items => items[0]?.label || '', label: item => `${item.dataset.label}: ${item.raw}` } } }, scales: { x: { title: { display: true, text: chartCopy.quantity || 'Cantidad de denuncias', color: '#52647c' }, beginAtZero: true, max: 300, stacked: true, grid: { color: '#e8eef6' }, ticks: { stepSize: 50, precision: 0 } }, y: { stacked: true, grid: { display: false }, ticks: { autoSkip: false } } } },
        });
    }
}
