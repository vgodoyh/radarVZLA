import './bootstrap';
import 'bootstrap';
import Chart from 'chart.js/auto';

const locale = document.documentElement.lang.startsWith('en') ? 'en' : 'es';
const t = {
    es: {protests:'Histórico de protestas', complaints:'Histórico de denuncias', complaintTypes:'Tipos de denuncia', topics:'Temas principales'},
    en: {protests:'Protest history', complaints:'Complaint history', complaintTypes:'Complaint types', topics:'Main topics'}
}[locale];

const commonLine = {responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,grid:{color:'#e8eef6'}},x:{grid:{display:false}}}};

const build = (id, config) => { const el=document.getElementById(id); if(el) new Chart(el,config); };

build('featuredChart',{type:'bar',data:{labels:['Jul 23','Aug 23','Sep 23','Oct 23','Nov 23','Dec 23','Jan 24','Feb 24','Mar 24','Apr 24','1-15 May'],datasets:[{data:[32,48,57,56,61,45,59,64,82,62,78],borderRadius:6}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true},x:{grid:{display:false}}}}});
build('protestsChart',{type:'line',data:{labels:['2020','2021','2022','2023','2024'],datasets:[{label:t.protests,data:[95,210,445,225,330],tension:.35,fill:true}]},options:commonLine});
build('complaintsChart',{type:'line',data:{labels:['2020','2021','2022','2023','2024'],datasets:[{label:t.complaints,data:[80,190,385,205,335],tension:.35,fill:true}]},options:commonLine});
build('complaintTypesChart',{type:'doughnut',data:{labels:['Economic and social rights','Civil and political rights'],datasets:[{data:[62,38]}]},options:{responsive:true,plugins:{title:{display:true,text:t.complaintTypes}}}});
build('topicsChart',{type:'bar',data:{labels:['Infrastructure','Student welfare','Salaries'],datasets:[{data:[42,28,30],borderRadius:6}]},options:{indexAxis:'y',responsive:true,plugins:{legend:{display:false},title:{display:true,text:t.topics}},scales:{x:{beginAtZero:true,max:50},y:{grid:{display:false}}}}});
