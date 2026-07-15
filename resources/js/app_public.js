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

const years = ['2020', '2021', '2022', '2023', '2024'];

build('featuredChart', { type: 'bar', data: { labels: ['Jul 23', 'Aug 23', 'Sep 23', 'Oct 23', 'Nov 23', 'Dec 23', 'Jan 24', 'Feb 24', 'Mar 24', 'Apr 24', '1-15 May'], datasets: [{ data: [32, 48, 57, 56, 61, 45, 59, 64, 82, 62, 78], borderRadius: 6 }] }, options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true }, x: { grid: { display: false } } } } });

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