const radarGrid = { color: '#e3e9f1' };
const radarTicks = { color: '#6b7485', font: { size: 10 } };

new Chart(document.getElementById('featuredChart'), {
    type: 'bar',
    data: {
        labels: ['Jul 25','Ago 25','Sep 25','Oct 25','Nov 25','Dic 25','Ene 26','Feb 26','Mar 26','Abr 26','1–15 May'],
        datasets: [{ data: [32,48,56,57,61,45,59,64,81,62,78], backgroundColor: ['#8bbcf5','#8bbcf5','#8bbcf5','#8bbcf5','#8bbcf5','#8bbcf5','#8bbcf5','#8bbcf5','#8bbcf5','#8bbcf5','#0b3f8f'], borderRadius: 2 }]
    },
    options: { plugins:{legend:{display:false}}, scales:{ x:{grid:{display:false},ticks:radarTicks}, y:{beginAtZero:true,grid:radarGrid,ticks:radarTicks} } }
});

function lineChart(id, values) {
    new Chart(document.getElementById(id), {
        type: 'line',
        data: { labels:['2021','2022','2023','2024','2025','2026'], datasets:[{data:values,borderColor:'#1769d2',backgroundColor:'rgba(23,105,210,.12)',fill:true,tension:.32,pointRadius:3}] },
        options:{plugins:{legend:{display:false}},scales:{x:{grid:{display:false},ticks:radarTicks},y:{beginAtZero:true,grid:radarGrid,ticks:radarTicks}}}
    });
}
lineChart('protestsChart',[120,230,460,330,220,340]);
lineChart('complaintsChart',[80,170,210,390,300,210]);

new Chart(document.getElementById('typesChart'), {
    type:'doughnut',
    data:{labels:['Económicos y sociales','Civiles y políticos'],datasets:[{data:[62,38],backgroundColor:['#2873c7','#9163b6'],borderWidth:0}]},
    options:{plugins:{legend:{position:'bottom',labels:{boxWidth:10,font:{size:9}}}},cutout:'62%'}
});

new Chart(document.getElementById('topicsChart'), {
    type:'bar',
    data:{labels:['Infraestructura','Providencias','Salarios'],datasets:[{data:[42,28,30],backgroundColor:['#2873c7','#9163b6','#df5a91'],borderRadius:2}]},
    options:{indexAxis:'y',plugins:{legend:{display:false}},scales:{x:{display:false,max:50},y:{grid:{display:false},ticks:{font:{size:9},color:'#465164'}}}}
});

new Chart(document.getElementById('protestsComplaintsChart'), {
    type: 'line',
    data: {
        labels: ['2020', '2021', '2022', '2023', '2024'],
        datasets: [
            { label: 'Protestas', data: protestsData, borderColor: '#2a78d6', backgroundColor: 'rgba(42,120,214,0.1)', fill: true, tension: 0.35, borderWidth: 2, pointRadius: 3 },
            { label: 'Denuncias', data: complaintsData, borderColor: '#1baf7a', backgroundColor: 'rgba(27,175,122,0.1)', fill: true, tension: 0.35, borderWidth: 2, pointRadius: 3, borderDash: [5, 3] }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true },
            x: { grid: { display: false } }
        }
    }
});

new Chart(document.getElementById('complaintTypeByYearChart'), {
    type: 'bar',
    data: {
        labels: ['2020', '2021', '2022', '2023', '2024'],
        datasets: [
            { label: 'Derechos económicos y sociales', data: economicSocialData, backgroundColor: '#2a78d6', borderRadius: 4 },
            { label: 'Derechos civiles y políticos', data: civilPoliticalData, backgroundColor: '#e34948', borderRadius: 4 }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { stacked: true, grid: { display: false } },
            y: { stacked: true, beginAtZero: true }
        }
    }
});
