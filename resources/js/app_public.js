import './bootstrap';
import 'bootstrap';
import Chart from 'chart.js/auto';

const locale = document.documentElement.lang.startsWith('en') ? 'en' : 'es';
const t = {
    es: {
        protestsComplaints: 'Protestas y denuncias',
        protests: 'Protestas',
        complaints: 'Denuncias',
        complaintTypeByYear: 'Tipo de denuncia por año',
        economicSocial: 'Derechos económicos y sociales',
        civilPolitical: 'Derechos civiles y políticos',
        topics: 'Temas principales',
    },
    en: {
        protestsComplaints: 'Protests and complaints',
        protests: 'Protests',
        complaints: 'Complaints',
        complaintTypeByYear: 'Complaint type by year',
        economicSocial: 'Economic and social rights',
        civilPolitical: 'Civil and political rights',
        topics: 'Main topics',
    },
}[locale];

const commonLine = { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: '#e8eef6' } }, x: { grid: { display: false } } } };

const build = (id, config) => { const el = document.getElementById(id); if (el) new Chart(el, config); };

document.querySelectorAll('.fake-news-verification-counter[data-target]').forEach((counter) => {
    const digitsContainer = counter.querySelector('.fake-news-verification-counter__digits');
    const target = Math.max(0, Number.parseInt(counter.dataset.target || '0', 10) || 0);
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const renderValue = (value) => {
        if (!digitsContainer) return;

        digitsContainer.replaceChildren(...String(value).split('').map((digit) => {
            const block = document.createElement('span');
            block.className = 'fake-news-verification-counter__digit';
            block.textContent = digit;

            return block;
        }));
    };

    if (reducedMotion) {
        renderValue(target);
        return;
    }

    renderValue(0);

    const animateCounter = () => {
        if (counter.dataset.animated === 'true') return;

        counter.dataset.animated = 'true';
        const duration = 1500;
        const start = performance.now();

        const tick = (now) => {
            const progress = Math.min((now - start) / duration, 1);
            const easedProgress = 1 - Math.pow(1 - progress, 3);
            renderValue(Math.round(target * easedProgress));

            if (progress < 1) requestAnimationFrame(tick);
        };

        requestAnimationFrame(tick);
    };

    if (!('IntersectionObserver' in window)) {
        animateCounter();
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        if (!entries.some((entry) => entry.isIntersecting)) return;

        observer.disconnect();
        animateCounter();
    }, { threshold: .35 });

    observer.observe(counter);
});

const years = ['2020', '2021', '2022', '2023', '2024'];

build('featuredChart', { type: 'bar', data: { labels: ['Ene 26', 'Feb 26', 'Mar 26', 'Abr 26', 'May 26', 'Jun 26', 'Jul 26'], datasets: [{ data: [32, 48, 57, 56, 61, 45, 59], borderRadius: 6 }] }, options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true }, x: { grid: { display: false } } } } });

const jepWomenDetentionCanvas = document.getElementById('jepWomenDetentionChart');
if (jepWomenDetentionCanvas) {
    const labels = JSON.parse(jepWomenDetentionCanvas.dataset.labels || '[]');
    const values = JSON.parse(jepWomenDetentionCanvas.dataset.values || '[]');
    const featuredMonthIndex = 4;

    const barValueLabels = {
        id: 'jepWomenBarValueLabels',
        afterDatasetsDraw(chart) {
            const { ctx, chartArea } = chart;
            const bars = chart.getDatasetMeta(0).data;

            ctx.save();
            ctx.fillStyle = '#10213f';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'bottom';

            bars.forEach((bar, index) => {
                ctx.font = `${index === featuredMonthIndex ? 700 : 600} 10px sans-serif`;
                ctx.fillText(String(values[index]), bar.x, Math.max(bar.y - 7, chartArea.top + 11));
            });

            ctx.restore();
        },
    };

    new Chart(jepWomenDetentionCanvas, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: jepWomenDetentionCanvas.dataset.datasetLabel || '',
                data: values,
                backgroundColor: ['#dcebff', '#cde1ff', '#bdd7ff', '#98c2ff', '#1769f6', '#8bb8fa', '#a9cafa'],
                borderRadius: 7,
                borderSkipped: false,
            }],
        },
        plugins: [barValueLabels],
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: { padding: { top: 18 } },
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 10 } } },
                y: { beginAtZero: true, grid: { color: '#e8eef7' }, ticks: { color: '#64748b', precision: 0, font: { size: 10 } } },
            },
        },
    });
}

document.querySelectorAll('.jep-donut__canvas').forEach((canvas) => {
    const values = JSON.parse(canvas.dataset.values || '[]');
    const colors = JSON.parse(canvas.dataset.colors || '[]');

    new Chart(canvas, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: values,
                backgroundColor: colors,
                borderColor: '#ffffff',
                borderWidth: 2,
                hoverOffset: 2,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { display: false },
                tooltip: { enabled: true },
            },
        },
    });
});

// Protestas y denuncias combinadas en un solo gráfico de línea
build('protestsComplaintsChart', {
    type: 'line',
    data: {
        labels: years,
        datasets: [
            { label: t.protests, data: [95, 210, 445, 225, 330], borderColor: '#0b3769', backgroundColor: 'rgba(11,55,105,0.1)', cubicInterpolationMode: 'monotone', tension: .35, fill: true, borderWidth: 2, pointRadius: 3 },
            { label: t.complaints, data: [80, 190, 385, 205, 335], borderColor: '#FFD23F', backgroundColor: 'rgba(255,210,63,0.1)', cubicInterpolationMode: 'monotone', tension: .35, fill: true, borderWidth: 2, pointRadius: 3, borderDash: [5, 3] },
        ],
    },
    options: commonLine,
});

// Tipo de denuncia por año, en barras apiladas en vez de dona
build('complaintTypeByYearChart', {
    type: 'bar',
    data: {
        labels: years,
        datasets: [
            { label: t.economicSocial, data: [60, 110, 190, 120, 150], backgroundColor: '#1f66d1', borderRadius: 4 },
            { label: t.civilPolitical, data: [35, 90, 190, 90, 180], backgroundColor: '#00B89C', borderRadius: 4 },
        ],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { stacked: true, grid: { display: false } },
            y: { stacked: true, beginAtZero: true, grid: { color: '#e8eef6' } },
        },
    },
});

build('topicsChart', { type: 'bar', data: { labels: ['Infrastructure', 'Student welfare', 'Salaries'], datasets: [{ data: [42, 28, 30], borderRadius: 6 }] }, options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false }, title: { display: true, text: t.topics } }, scales: { x: { beginAtZero: true, max: 50 }, y: { grid: { display: false } } } } });
