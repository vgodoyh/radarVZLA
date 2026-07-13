<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Radar Venezuela — Panel de Análisis</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,600;8..60,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
<style>
  :root{
    --bg:#0E1017; --surface:#161923; --surface-2:#1D2130; --line:#262B3A;
    --ink:#EDEEF3; --ink-dim:#9BA1B4; --ink-faint:#666D80;

    --jep:#C0687A;  --jep-soft:rgba(192,104,122,.16);
    --aj:#4FB3A9;   --aj-soft:rgba(79,179,169,.16);
    --ovfn:#E0A458; --ovfn-soft:rgba(224,164,88,.16);
    --obu:#5C8FE0;  --obu-soft:rgba(92,143,224,.16);

    --blue:#3B6FE0;   --blue-soft:rgba(59,111,224,.16);
    --red:#D6564D;    --red-soft:rgba(214,86,77,.16);
    --green:#3FA66E;  --green-soft:rgba(63,166,110,.16);
    --gold:#D1A63E;   --gold-soft:rgba(209,166,62,.16);
    --purple:#9868C9; --purple-soft:rgba(152,104,201,.16);
    --teal:#2FA294;   --teal-soft:rgba(47,162,148,.16);
    --indigo:#6C7FE0;  --indigo-soft:rgba(108,127,224,.16);

    --up:#5FBF87; --crit:#D6564D; --alto:#DE8B3E; --medio:#D1A63E; --bajo:#4A9F63; --nodata:#4C5266;
  }
  *{box-sizing:border-box;}
  body{margin:0;background:var(--bg);color:var(--ink);font-family:'Inter',sans-serif;-webkit-font-smoothing:antialiased;}
  h1,h2,h3{font-family:'Source Serif 4', serif;}
  .mono{font-family:'IBM Plex Mono', monospace;}
  .wrap{max-width:1400px;margin:0 auto;padding:0 24px;}
  .card{background:var(--surface);border:1px solid var(--line);border-radius:14px;}
  svg.icon{width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round;}

  /* ============ NAVBAR ============ */
  .navbar{border-bottom:1px solid var(--line);background:linear-gradient(180deg, rgba(22,25,35,.95), rgba(22,25,35,.75));backdrop-filter:blur(8px);}
  .nav-inner{display:flex;align-items:center;gap:28px;padding:14px 24px;}
  .nav-brand{font-size:12px;line-height:1.25;color:var(--ink-dim);font-weight:600;white-space:nowrap;border-right:1px solid var(--line);padding-right:22px;}
  .nav-brand b{display:block;color:var(--ink);font-size:13px;}
  .org-logos{display:flex;align-items:center;gap:22px;flex:1;overflow-x:auto;}
  .org-logo{display:flex;align-items:center;gap:9px;white-space:nowrap;}
  .org-logo .mark{width:34px;height:34px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;flex:0 0 auto;}
  .org-logo .name{font-size:13px;font-weight:700;line-height:1.1;}
  .org-logo .sub{font-size:9.5px;color:var(--ink-faint);font-weight:600;letter-spacing:.3px;}
  .nav-right{display:flex;align-items:center;gap:14px;flex:0 0 auto;}
  .date-range{display:flex;align-items:center;gap:8px;background:var(--surface-2);border:1px solid var(--line);padding:8px 12px;border-radius:9px;font-size:12.5px;color:var(--ink-dim);white-space:nowrap;}
  .bell{position:relative;width:36px;height:36px;border-radius:9px;background:var(--surface-2);border:1px solid var(--line);display:flex;align-items:center;justify-content:center;color:var(--ink-dim);}
  .bell .badge{position:absolute;top:-5px;right:-5px;background:var(--red);color:#fff;font-size:9.5px;font-weight:700;padding:1.5px 5px;border-radius:100px;}
  .user-chip{display:flex;align-items:center;gap:9px;background:var(--surface-2);border:1px solid var(--line);padding:6px 12px 6px 6px;border-radius:9px;}
  .user-avatar{width:26px;height:26px;border-radius:50%;background:var(--indigo-soft);color:var(--indigo);display:flex;align-items:center;justify-content:center;}
  .user-chip .name{font-size:12px;font-weight:600;line-height:1.15;}
  .user-chip .role{font-size:10px;color:var(--ink-faint);}

  main{padding:24px 0 48px;}

  /* ============ KPI ROW ============ */
  .kpi-row{display:grid;grid-template-columns:repeat(7,1fr);gap:12px;margin-bottom:16px;}
  .kpi{padding:16px 16px;display:flex;align-items:center;gap:12px;}
  .kpi-icon{width:38px;height:38px;border-radius:10px;flex:0 0 auto;display:flex;align-items:center;justify-content:center;}
  .kpi-label{font-size:11.5px;color:var(--ink-dim);line-height:1.25;}
  .kpi-value{font-size:20px;font-weight:700;margin-top:2px;}
  .kpi-delta{font-size:10.5px;color:var(--up);margin-top:2px;font-weight:600;}
  .kpi-sparkline{width:100%;height:34px;}

  /* ============ ROW 2 ============ */
  .row2{display:grid;grid-template-columns:1.15fr 1.15fr .95fr;gap:14px;margin-bottom:14px;align-items:stretch;}
  .panel-head{display:flex;align-items:center;justify-content:space-between;padding:18px 20px 6px;}
  .panel-head h3{margin:0;font-size:14.5px;font-weight:600;}

  /* map */
  .map-wrap{padding:0 0 16px;}
  .map-body{position:relative;padding:6px 20px 0;}
  .map-controls{position:absolute;left:24px;top:8px;display:flex;flex-direction:column;gap:5px;z-index:2;}
  .map-btn{width:26px;height:26px;border-radius:6px;background:var(--surface-2);border:1px solid var(--line);color:var(--ink-dim);display:flex;align-items:center;justify-content:center;font-size:13px;cursor:default;}
  .map-legend{display:flex;flex-direction:column;gap:6px;position:absolute;right:24px;bottom:14px;font-size:11px;color:var(--ink-dim);background:rgba(14,16,23,.55);padding:10px 12px;border-radius:9px;border:1px solid var(--line);}
  .map-legend .row{display:flex;align-items:center;gap:7px;}
  .map-legend .row i{width:8px;height:8px;border-radius:50%;display:inline-block;}
  .map-filter{display:flex;align-items:center;justify-content:space-between;padding:12px 20px 18px;font-size:12px;color:var(--ink-dim);}
  .map-filter select{background:var(--surface-2);border:1px solid var(--line);color:var(--ink);border-radius:8px;padding:6px 10px;font-size:12px;}
  .link-arrow{font-size:12px;color:var(--gold);text-decoration:none;font-weight:500;white-space:nowrap;}

  /* noticias */
  .news-item{display:flex;gap:12px;padding:13px 20px;border-top:1px solid var(--line);}
  .news-item:first-of-type{border-top:none;}
  .news-thumb{width:44px;height:44px;border-radius:9px;flex:0 0 auto;object-fit:cover;background:var(--surface-2);}
  .news-body{flex:1;min-width:0;}
  .news-meta{display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;gap:8px;}
  .news-tag{font-size:9.5px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;padding:2px 7px;border-radius:5px;}
  .news-time{font-size:10.5px;color:var(--ink-faint);white-space:nowrap;}
  .news-title{font-size:12.5px;line-height:1.4;color:var(--ink);}
  .news-foot{padding:12px 20px 18px;text-align:right;}

  /* ============ ROW 3 ============ */
  .row3{display:grid;grid-template-columns:.85fr 1.3fr .95fr;gap:14px;}

  /* alertas */
  .alert-item{display:flex;gap:11px;padding:13px 20px;border-top:1px solid var(--line);align-items:flex-start;}
  .alert-item:first-of-type{border-top:none;}
  .alert-dot{width:9px;height:9px;border-radius:50%;margin-top:5px;flex:0 0 auto;}
  .alert-body{flex:1;font-size:12.5px;line-height:1.4;}
  .alert-badge{font-size:9px;font-weight:700;padding:2px 7px;border-radius:5px;margin-left:8px;white-space:nowrap;}
  .alert-time{font-size:10.5px;color:var(--ink-faint);margin-top:3px;}
  .alert-foot{padding:12px 20px 18px;text-align:right;}

  /* indicadores por ONG */
  .ind-grid{display:grid;grid-template-columns:repeat(4,1fr);}
  .ind-col{padding:16px 14px;border-left:1px solid var(--line);}
  .ind-col:first-child{border-left:none;}
  .ind-col-head{display:flex;align-items:center;gap:8px;margin-bottom:12px;}
  .ind-icon{width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:10.5px;font-weight:700;flex:0 0 auto;}
  .ind-org-name{font-size:11.5px;font-weight:700;line-height:1.15;}
  .ind-row{display:flex;justify-content:space-between;padding:5px 0;font-size:11px;border-top:1px solid rgba(255,255,255,.04);}
  .ind-row:first-of-type{border-top:none;}
  .ind-row .k{color:var(--ink-dim);}
  .ind-row .v{font-family:'IBM Plex Mono',monospace;font-weight:600;}
  .ind-foot{padding:10px 14px 4px;}
  .ind-foot a{font-size:11px;color:var(--gold);text-decoration:none;font-weight:500;}

  /* redes sociales */
  .social-icons{display:flex;gap:10px;padding:2px 20px 14px;}
  .social-icons .si{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;}
  .social-chart{padding:0 20px 18px;}
  .social-foot{padding:0 20px 18px;text-align:right;}

  footer{padding:28px 24px 44px;text-align:center;font-size:11.5px;color:var(--ink-faint);}

  @media (max-width:1200px){
    .kpi-row{grid-template-columns:repeat(4,1fr);}
    .row2, .row3{grid-template-columns:1fr;}
  }
  @media (max-width:640px){
    .kpi-row{grid-template-columns:repeat(2,1fr);}
    .ind-grid{grid-template-columns:repeat(2,1fr);}
    .ind-col:nth-child(odd){border-left:none;}
  }
</style>
</head>
<body>

<div class="navbar">
  <div class="wrap nav-inner">
    <div class="nav-brand">OBSERVATORIO<br><b>DE ANÁLISIS</b><span style="display:block;color:var(--ink-faint);font-weight:500;">venezolanas</span></div>

    <div class="org-logos">
      <div class="org-logo">
        <div class="mark" style="background:var(--jep-soft);color:var(--jep);">JEP</div>
        <div><div class="name">JEP</div><div class="sub">VENEZUELA</div></div>
      </div>
      <div class="org-logo">
        <div class="mark" style="background:var(--aj-soft);color:var(--aj);">
          <svg class="icon" viewBox="0 0 24 24"><path d="M12 3l8 4-8 4-8-4 8-4z"/><path d="M4 11v6c0 1 3.5 2 8 2s8-1 8-2v-6"/></svg>
        </div>
        <div><div class="name">Acceso</div><div class="sub">A LA JUSTICIA</div></div>
      </div>
      <div class="org-logo">
        <div class="mark" style="background:var(--ovfn-soft);color:var(--ovfn);">
          <svg class="icon" viewBox="0 0 24 24"><circle cx="10" cy="10" r="6"/><path d="M20 20l-5.5-5.5"/></svg>
        </div>
        <div><div class="name">Medianálisis</div></div>
      </div>
      <div class="org-logo">
        <div class="mark" style="background:var(--obu-soft);color:var(--obu);">OBU</div>
        <div><div class="name">Observatorio de</div><div class="sub">UNIVERSIDADES</div></div>
      </div>
    </div>

    <div class="nav-right">
      <div class="date-range">
        <svg class="icon" style="width:15px;height:15px;" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        01/05/2026 - 31/05/2026
      </div>
      <div class="bell">
        <svg class="icon" viewBox="0 0 24 24"><path d="M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 01-3.4 0"/></svg>
        <span class="badge">12</span>
      </div>
      <div class="user-chip">
        <div class="user-avatar"><svg class="icon" style="width:14px;height:14px;" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 4-6 8-6s8 2 8 6"/></svg></div>
        <div><div class="name">Analista</div><div class="role">Administrador</div></div>
      </div>
    </div>
  </div>
</div>

<main class="wrap">

  <!-- ============ KPI ROW ============ -->
  <div class="kpi-row">
    <div class="card kpi">
      <div class="kpi-icon" style="background:var(--blue-soft);color:var(--blue);">
        <svg class="icon" viewBox="0 0 24 24"><path d="M6 3h9l5 5v13H6z"/><path d="M14 3v5h5"/></svg>
      </div>
      <div><div class="kpi-label">Publicaciones<br>este mes</div><div class="kpi-value">248</div><div class="kpi-delta">+15% vs. Abril</div></div>
    </div>
    <div class="card kpi">
      <div class="kpi-icon" style="background:var(--red-soft);color:var(--red);">
        <svg class="icon" viewBox="0 0 24 24"><path d="M12 2L2 20h20L12 2z"/><path d="M12 10v4M12 17h.01"/></svg>
      </div>
      <div><div class="kpi-label">Alertas<br>activas</div><div class="kpi-value">17</div><div class="kpi-delta" style="color:var(--red);">+31% vs. Abril</div></div>
    </div>
    <div class="card kpi">
      <div class="kpi-icon" style="background:var(--green-soft);color:var(--green);">
        <svg class="icon" viewBox="0 0 24 24"><path d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
      </div>
      <div><div class="kpi-label">Casos / Documentos<br>registrados</div><div class="kpi-value">5.321</div><div class="kpi-delta">+8% vs. Abril</div></div>
    </div>
    <div class="card kpi">
      <div class="kpi-icon" style="background:var(--gold-soft);color:var(--gold);">
        <svg class="icon" viewBox="0 0 24 24"><path d="M12 21s7-6.2 7-11a7 7 0 10-14 0c0 4.8 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
      </div>
      <div><div class="kpi-label">Estados con<br>incidencias</div><div class="kpi-value">24 / 24</div></div>
    </div>
    <div class="card kpi">
      <div class="kpi-icon" style="background:var(--purple-soft);color:var(--purple);">
        <svg class="icon" viewBox="0 0 24 24"><path d="M4 20V10M11 20V4M18 20v-7"/></svg>
      </div>
      <div><div class="kpi-label">Temas más<br>críticos</div><div class="kpi-value">6</div></div>
    </div>
    <div class="card kpi">
      <div class="kpi-icon" style="background:var(--teal-soft);color:var(--teal);">
        <svg class="icon" viewBox="0 0 24 24"><path d="M3 17l5-5 4 4 8-9"/></svg>
      </div>
      <div><div class="kpi-label">Tendencia<br>semanal</div><canvas class="kpi-sparkline" id="sparkline"></canvas></div>
    </div>
    <div class="card kpi">
      <div class="kpi-icon" style="background:var(--indigo-soft);color:var(--indigo);">
        <svg class="icon" viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"/><circle cx="17" cy="9" r="2.5"/><path d="M3 20c0-3.3 2.7-5.5 6-5.5s6 2.2 6 5.5M15.5 14.8c2.5.3 4.5 2.3 4.5 5.2"/></svg>
      </div>
      <div><div class="kpi-label">Alcance digital<br>este mes</div><div class="kpi-value">2.1M</div><div class="kpi-delta">+22% vs. Abril</div></div>
    </div>
  </div>

  <!-- ============ ROW 2: MAPA / TENDENCIAS / NOTICIAS ============ -->
  <div class="row2">

    <!-- mapa -->
    <div class="card map-wrap">
      <div class="panel-head"><h3>Mapa de incidencia por estado</h3></div>
      <div class="map-body">
        <div class="map-controls">
          <div class="map-btn">+</div>
          <div class="map-btn">–</div>
          <div class="map-btn">⌂</div>
        </div>
        <svg viewBox="0 0 360 300" style="width:100%;height:220px;">
          <polygon points="40,60 95,45 120,70 100,95 55,100" fill="var(--crit)" opacity=".85"/>
          <polygon points="95,45 160,40 175,70 120,70" fill="var(--alto)" opacity=".85"/>
          <polygon points="160,40 230,45 225,80 175,70" fill="var(--medio)" opacity=".85"/>
          <polygon points="230,45 300,55 290,90 225,80" fill="var(--alto)" opacity=".85"/>
          <polygon points="120,70 175,70 180,110 130,120 100,95" fill="var(--alto)" opacity=".85"/>
          <polygon points="175,70 225,80 220,120 180,110" fill="var(--crit)" opacity=".85"/>
          <polygon points="225,80 290,90 280,130 220,120" fill="var(--medio)" opacity=".85"/>
          <polygon points="55,100 100,95 130,120 110,150 65,140" fill="var(--bajo)" opacity=".85"/>
          <polygon points="130,120 180,110 220,120 210,160 150,165" fill="var(--medio)" opacity=".85"/>
          <polygon points="220,120 280,130 270,170 210,160" fill="var(--nodata)" opacity=".85"/>
          <polygon points="65,140 110,150 120,190 80,200" fill="var(--bajo)" opacity=".85"/>
          <polygon points="110,150 150,165 160,205 120,190" fill="var(--medio)" opacity=".85"/>
          <polygon points="150,165 210,160 200,210 160,205" fill="var(--nodata)" opacity=".85"/>
        </svg>
        <div class="map-legend">
          <div class="row"><i style="background:var(--crit)"></i>Crítico</div>
          <div class="row"><i style="background:var(--alto)"></i>Alto</div>
          <div class="row"><i style="background:var(--medio)"></i>Medio</div>
          <div class="row"><i style="background:var(--bajo)"></i>Bajo</div>
          <div class="row"><i style="background:var(--nodata)"></i>Sin datos</div>
        </div>
      </div>
      <div class="map-filter">
        <span>Filtrar por categoría: <select><option>Todas</option><option>Derechos humanos</option><option>Justicia</option><option>Medios</option><option>Universidades</option></select></span>
        <a class="link-arrow" href="#">Ver detalle por estado →</a>
      </div>
    </div>

    <!-- tendencias -->
    <div class="card">
      <div class="panel-head"><h3>Tendencias de temas</h3></div>
      <div style="padding:6px 20px 10px;">
        <canvas id="chartTrends" height="185"></canvas>
      </div>
      <div style="display:flex;flex-wrap:wrap;gap:14px;padding:0 20px 20px;font-size:11.5px;color:var(--ink-dim);">
        <span><i style="display:inline-block;width:8px;height:8px;border-radius:50%;background:var(--jep);margin-right:5px;"></i>Derechos Humanos</span>
        <span><i style="display:inline-block;width:8px;height:8px;border-radius:50%;background:var(--aj);margin-right:5px;"></i>Justicia</span>
        <span><i style="display:inline-block;width:8px;height:8px;border-radius:50%;background:var(--bajo);margin-right:5px;"></i>Medios</span>
        <span><i style="display:inline-block;width:8px;height:8px;border-radius:50%;background:var(--purple);margin-right:5px;"></i>Universidades</span>
        <span><i style="display:inline-block;width:8px;height:8px;border-radius:50%;background:var(--gold);margin-right:5px;"></i>Desinformación</span>
      </div>
    </div>

    <!-- noticias -->
    <div class="card">
      <div class="panel-head"><h3>Noticias destacadas</h3></div>

      <div class="news-item">
        <div class="news-thumb" style="background:var(--jep-soft);"></div>
        <div class="news-body">
          <div class="news-meta"><span class="news-tag" style="background:var(--jep-soft);color:var(--jep);">Derechos Humanos</span><span class="news-time">Hace 2 horas</span></div>
          <div class="news-title">Documentan 27 casos de violaciones de DDHH en el estado Zulia</div>
        </div>
      </div>
      <div class="news-item">
        <div class="news-thumb" style="background:var(--aj-soft);"></div>
        <div class="news-body">
          <div class="news-meta"><span class="news-tag" style="background:var(--aj-soft);color:var(--aj);">Justicia</span><span class="news-time">Hace 4 horas</span></div>
          <div class="news-title">AN analizó reforma de la Ley del TSJ: alertan sobre independencia judicial</div>
        </div>
      </div>
      <div class="news-item">
        <div class="news-thumb" style="background:var(--ovfn-soft);"></div>
        <div class="news-body">
          <div class="news-meta"><span class="news-tag" style="background:var(--ovfn-soft);color:var(--ovfn);">Medios</span><span class="news-time">Hace 6 horas</span></div>
          <div class="news-title">Detectan campaña de desinformación sobre elecciones en redes sociales</div>
        </div>
      </div>
      <div class="news-item">
        <div class="news-thumb" style="background:var(--obu-soft);"></div>
        <div class="news-body">
          <div class="news-meta"><span class="news-tag" style="background:var(--obu-soft);color:var(--obu);">Universidades</span><span class="news-time">Hace 6 horas</span></div>
          <div class="news-title">Protestas en la UCV por fallas en servicios y presupuesto</div>
        </div>
      </div>

      <div class="news-foot"><a class="link-arrow" href="#">Ver todas las noticias →</a></div>
    </div>

  </div>

  <!-- ============ ROW 3: ALERTAS / INDICADORES / REDES ============ -->
  <div class="row3">

    <!-- alertas recientes -->
    <div class="card">
      <div class="panel-head"><h3>Alertas recientes</h3></div>

      <div class="alert-item">
        <div class="alert-dot" style="background:var(--crit);"></div>
        <div class="alert-body">
          Aumento de ataques a periodistas en el estado Lara
          <span class="alert-badge" style="background:var(--red-soft);color:var(--red);">CRÍTICO</span>
          <div class="alert-time">Hace 30 min</div>
        </div>
      </div>
      <div class="alert-item">
        <div class="alert-dot" style="background:var(--alto);"></div>
        <div class="alert-body">
          Incremento de protestas universitarias en 5 estados del país
          <span class="alert-badge" style="background:var(--ovfn-soft);color:var(--alto);">ALTO</span>
          <div class="alert-time">Hace 1 hora</div>
        </div>
      </div>
      <div class="alert-item">
        <div class="alert-dot" style="background:var(--medio);"></div>
        <div class="alert-body">
          Nueva narrativa de desinformación sobre ayuda humanitaria
          <span class="alert-badge" style="background:var(--gold-soft);color:var(--medio);">MEDIO</span>
          <div class="alert-time">Hace 3 horas</div>
        </div>
      </div>
      <div class="alert-item">
        <div class="alert-dot" style="background:var(--crit);"></div>
        <div class="alert-body">
          Violaciones graves de DDHH en centros de detención
          <span class="alert-badge" style="background:var(--red-soft);color:var(--red);">CRÍTICO</span>
          <div class="alert-time">Hace 5 horas</div>
        </div>
      </div>

      <div class="alert-foot"><a class="link-arrow" href="#">Ver todas las alertas →</a></div>
    </div>

    <!-- indicadores por ONG -->
    <div class="card">
      <div class="panel-head"><h3>Indicadores por ONG</h3></div>
      <div class="ind-grid">

        <div class="ind-col">
          <div class="ind-col-head"><div class="ind-icon" style="background:var(--jep-soft);color:var(--jep);">JEP</div><div class="ind-org-name">JEP<br><span style="font-weight:500;color:var(--ink-faint);font-size:9.5px;">VENEZUELA</span></div></div>
          <div class="ind-row"><span class="k">Casos documentados</span><span class="v">1.254</span></div>
          <div class="ind-row"><span class="k">Víctimas registradas</span><span class="v">2.153</span></div>
          <div class="ind-row"><span class="k">Estados afectados</span><span class="v">23</span></div>
          <div class="ind-row"><span class="k">Eventos este mes</span><span class="v">87</span></div>
          <div class="ind-foot"><a href="#">Ver dashboard →</a></div>
        </div>

        <div class="ind-col">
          <div class="ind-col-head"><div class="ind-icon" style="background:var(--aj-soft);color:var(--aj);">⚖</div><div class="ind-org-name">Acceso<br><span style="font-weight:500;color:var(--ink-faint);font-size:9.5px;">A LA JUSTICIA</span></div></div>
          <div class="ind-row"><span class="k">Leyes analizadas</span><span class="v">18</span></div>
          <div class="ind-row"><span class="k">Decisiones judiciales</span><span class="v">142</span></div>
          <div class="ind-row"><span class="k">Informes publicados</span><span class="v">12</span></div>
          <div class="ind-row"><span class="k">Alertas institucionales</span><span class="v">9</span></div>
          <div class="ind-foot"><a href="#">Ver dashboard →</a></div>
        </div>

        <div class="ind-col">
          <div class="ind-col-head"><div class="ind-icon" style="background:var(--ovfn-soft);color:var(--ovfn);">M</div><div class="ind-org-name">Medianálisis</div></div>
          <div class="ind-row"><span class="k">Noticias monitoreadas</span><span class="v">4.892</span></div>
          <div class="ind-row"><span class="k">Desinformaciones</span><span class="v">312</span></div>
          <div class="ind-row"><span class="k">Medios monitoreados</span><span class="v">120</span></div>
          <div class="ind-row"><span class="k">Ataques a periodistas</span><span class="v">35</span></div>
          <div class="ind-foot"><a href="#">Ver dashboard →</a></div>
        </div>

        <div class="ind-col">
          <div class="ind-col-head"><div class="ind-icon" style="background:var(--obu-soft);color:var(--obu);">OBU</div><div class="ind-org-name">Observatorio de<br><span style="font-weight:500;color:var(--ink-faint);font-size:9.5px;">UNIVERSIDADES</span></div></div>
          <div class="ind-row"><span class="k">Universidades monitoreadas</span><span class="v">63</span></div>
          <div class="ind-row"><span class="k">Incidentes registrados</span><span class="v">198</span></div>
          <div class="ind-row"><span class="k">Protestas universitarias</span><span class="v">27</span></div>
          <div class="ind-row"><span class="k">Índice de crisis (prom.)</span><span class="v">7.3/10</span></div>
          <div class="ind-foot"><a href="#">Ver dashboard →</a></div>
        </div>

      </div>
    </div>

    <!-- redes sociales -->
    <div class="card">
      <div class="panel-head"><h3>Actividad en redes sociales</h3></div>
      <div class="social-icons">
        <div class="si" style="background:#1DA1F2;"><svg class="icon" style="width:16px;height:16px;stroke:#fff;" viewBox="0 0 24 24"><path d="M22 4s-.7 2-2 3c1.3 7-4 12-11 12-3 0-5-1-7-3 3 0 5-1 6-2-3 0-4-2-4-4 1 0 2 0 3-.5-3-1-4-3-4-6 1 .5 2 1 3 1-2-2-2-5 0-7 3 4 7 6 11 6-1-4 4-7 7-4-1 1-1 2-1 3 1 0 2-.5 3-1.5z"/></svg></div>
        <div class="si" style="background:#1877F2;"><svg class="icon" style="width:16px;height:16px;stroke:#fff;" viewBox="0 0 24 24"><path d="M14 9h3V6h-3c-2 0-3 1-3 3v2H9v3h2v7h3v-7h3l1-3h-4V9c0-.6.4-1 1-1z"/></svg></div>
        <div class="si" style="background:linear-gradient(135deg,#F58529,#DD2A7B,#8134AF);"><svg class="icon" style="width:16px;height:16px;stroke:#fff;" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1"/></svg></div>
        <div class="si" style="background:#FF0000;"><svg class="icon" style="width:16px;height:16px;stroke:#fff;" viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="12" rx="3"/><path d="M11 10l5 2-5 2z" fill="#fff" stroke="none"/></svg></div>
        <div class="si" style="background:#26A5E4;"><svg class="icon" style="width:16px;height:16px;stroke:#fff;" viewBox="0 0 24 24"><path d="M22 3L2 11l7 2m13-10l-5 18-8-6m13-12l-13 12"/></svg></div>
      </div>
      <div class="social-chart">
        <canvas id="chartSocial" height="150"></canvas>
      </div>
      <div class="social-foot"><a class="link-arrow" href="#">Ver análisis completo →</a></div>
    </div>

  </div>

</main>

<footer>Observatorio Venezuela · Panel de análisis · Datos recopilados de fuentes públicas de cada organización</footer>

<script>
Chart.defaults.color = '#9BA1B4';
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.font.size = 11;
function grid(){ return { color:'rgba(255,255,255,.05)' }; }

new Chart(document.getElementById('sparkline'), {
  type:'line',
  data:{ labels:['','','','','',''], datasets:[{ data:[3,5,4,7,6,9], borderColor:'#2FA294', borderWidth:2, pointRadius:0, tension:.4, fill:false }] },
  options:{ plugins:{legend:{display:false}}, scales:{ x:{display:false}, y:{display:false} }, elements:{ line:{ borderJoinStyle:'round' } } }
});

new Chart(document.getElementById('chartTrends'), {
  type:'line',
  data:{
    labels:['1 May','8 May','15 May','22 May','31 May'],
    datasets:[
      { label:'Derechos Humanos', data:[62,80,58,88,66], borderColor:'#C0687A', tension:.35, pointRadius:0 },
      { label:'Justicia', data:[45,52,40,60,52], borderColor:'#4FB3A9', tension:.35, pointRadius:0 },
      { label:'Medios', data:[20,28,22,35,30], borderColor:'#3FA66E', tension:.35, pointRadius:0 },
      { label:'Universidades', data:[30,40,33,48,44], borderColor:'#9868C9', tension:.35, pointRadius:0 },
      { label:'Desinformación', data:[10,15,12,20,16], borderColor:'#D1A63E', tension:.35, pointRadius:0 }
    ]
  },
  options:{ plugins:{legend:{display:false}}, scales:{ x:{grid:grid()}, y:{grid:grid(), beginAtZero:true} } }
});

new Chart(document.getElementById('chartSocial'), {
  type:'bar',
  data:{
    labels:['Twitter','Facebook','Instagram','YouTube','Telegram'],
    datasets:[{ data:[820,780,460,340,210], backgroundColor:['#1DA1F2','#1877F2','#DD2A7B','#FF0000','#26A5E4'], borderRadius:5, barThickness:34 }]
  },
  options:{ plugins:{legend:{display:false}}, scales:{ x:{grid:{display:false}}, y:{grid:grid(), beginAtZero:true} } }
});
</script>

</body>
</html>