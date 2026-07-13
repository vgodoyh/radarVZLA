{{--
  ============================================================
  VISTA: resources/views/x-feeds.blade.php
  Consume /dashboard-x-feeds (las 4 cuentas, cacheadas 30 min)
  y renderiza las tarjetas con JS puro (fetch).

  Agregar ruta en routes/web.php:
  Route::view('/x-feeds', 'x-feeds');

  Abrir en:
  http://localhost/dashboard-ong/public/x-feeds
  ============================================================
--}}
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Monitoreo X — Dashboard ONG</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      background: #0a0c10;
      color: #d1d5db;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
      padding: 32px 24px;
    }

    .page-header {
      margin-bottom: 24px;
    }

    .page-header h1 {
      font-size: 20px;
      font-weight: 600;
      color: #e6e8eb;
      margin-bottom: 4px;
    }

    .page-header p {
      font-size: 13px;
      color: #6b7280;
    }

    .x-cards-wrapper {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
    }

    @media (max-width: 1300px) {
      .x-cards-wrapper { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 700px) {
      .x-cards-wrapper { grid-template-columns: 1fr; }
    }

    .x-cards-col {
      background: #15171c;
      border: 1px solid #2a2d35;
      border-radius: 12px;
      overflow: hidden;
      display: flex;
      flex-direction: column;
    }

    .x-cards-header {
      padding: 12px 16px;
      background: #0d0f14;
      border-bottom: 1px solid #2a2d35;
      font-size: 14px;
      font-weight: 600;
      color: #e6e8eb;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .x-cards-header svg {
      width: 16px;
      height: 16px;
      fill: #e6e8eb;
      flex-shrink: 0;
    }

    .x-cards-header .handle {
      font-weight: 400;
      color: #8b8f99;
      font-size: 11px;
      margin-left: auto;
    }

    .x-cards-body {
      flex: 1;
      padding: 10px;
      display: flex;
      flex-direction: column;
      gap: 10px;
      overflow-y: auto;
      max-height: 700px;
    }

    .x-tweet-card {
      background: #1c1f26;
      border: 1px solid #2a2d35;
      border-radius: 10px;
      padding: 12px 14px;
      transition: border-color 0.15s ease;
    }

    .x-tweet-card:hover { border-color: #3d4150; }

    .x-tweet-date {
      font-size: 11px;
      color: #6b7280;
      margin-bottom: 6px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .x-tweet-stat { display: flex; align-items: center; gap: 3px; }

    .x-tweet-text {
      font-size: 13px;
      line-height: 1.5;
      color: #d1d5db;
      white-space: pre-wrap;
      margin-bottom: 8px;
    }

    .x-tweet-image {
      width: 100%;
      border-radius: 8px;
      margin-bottom: 8px;
      display: block;
      border: 1px solid #2a2d35;
    }

    .x-tweet-link {
      display: inline-block;
      font-size: 12px;
      color: #4d9fec;
      text-decoration: none;
    }

    .x-tweet-link:hover { text-decoration: underline; }

    .loading-state, .error-state {
      padding: 24px 14px;
      text-align: center;
      font-size: 12px;
      color: #6b7280;
    }

    .error-state { color: #ff8080; }

    .x-cards-footer-note {
      padding: 8px 14px;
      font-size: 11px;
      color: #6b7280;
      border-top: 1px solid #2a2d35;
      background: #0d0f14;
    }
  </style>
</head>
<body>

  <div class="page-header">
    <h1>Monitoreo de Redes — X (Twitter)</h1>
    <p>Últimos 5 posts propios de cada cuenta · cache de 30 minutos</p>
  </div>

  <div class="x-cards-wrapper" id="cardsWrapper">
    <!-- Las columnas se generan por JS -->
  </div>

  <script>
    const ACCOUNTS = {
      AccesoaJusticia: 'Acceso a la Justicia',
      observatoriofn:  'Observatorio Fake News',
      jepvzla:         'Justicia, Encuentro y Perdón',
      OBUVenezuela:    'OBU - Observatorio',
    };

    const xIcon = `<svg viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>`;

    function escapeHtml(text) {
      return (text || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function formatDate(dateStr) {
      if (!dateStr) return '';
      const d = new Date(dateStr);
      if (isNaN(d)) return dateStr;
      return d.toLocaleDateString('es-VE', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }

    function buildColumn(username, label) {
      return `
        <div class="x-cards-col" id="col-${username}">
          <div class="x-cards-header">
            ${xIcon}
            ${label}
            <span class="handle">@${username}</span>
          </div>
          <div class="x-cards-body" id="body-${username}">
            <div class="loading-state">Cargando tuits…</div>
          </div>
          <div class="x-cards-footer-note" id="note-${username}"></div>
        </div>
      `;
    }

    function renderTweets(username, tweets) {
      const body = document.getElementById(`body-${username}`);

      if (!tweets || tweets.length === 0) {
        body.innerHTML = `<div class="error-state">No se encontraron tuits propios recientes.</div>`;
        return;
      }

      body.innerHTML = tweets.map(t => `
        <div class="x-tweet-card">
          <div class="x-tweet-date">
            <span>${formatDate(t.date)}</span>
            <span class="x-tweet-stat">❤ ${t.likes ?? 0}</span>
            <span class="x-tweet-stat">🔁 ${t.retweets ?? 0}</span>
          </div>
          <div class="x-tweet-text">${escapeHtml(t.text)}</div>
          ${t.image ? `<img class="x-tweet-image" src="${t.image}" alt="" loading="lazy">` : ''}
          <a class="x-tweet-link" href="${t.url}" target="_blank">Ver en X →</a>
        </div>
      `).join('');
    }

    async function loadFeeds() {
      const wrapper = document.getElementById('cardsWrapper');
      wrapper.innerHTML = Object.entries(ACCOUNTS)
        .map(([username, label]) => buildColumn(username, label))
        .join('');

      try {
        const res = await fetch('/dashboard-ong/public/dashboard-x-feeds');
        const data = await res.json();

        for (const [username] of Object.entries(ACCOUNTS)) {
          const feed = data[username];
          const note = document.getElementById(`note-${username}`);

          if (!feed || feed.error) {
            document.getElementById(`body-${username}`).innerHTML =
              `<div class="error-state">Error al cargar este feed.</div>`;
            note.textContent = 'Error en la API';
            continue;
          }

          renderTweets(username, feed.tweets);
          note.textContent = `Actualizado · cache 30 min · ${feed.tweets.length} posts`;
        }
      } catch (err) {
        wrapper.innerHTML = `<div class="error-state">Error de conexión: ${err.message}</div>`;
      }
    }

    loadFeeds();
  </script>

</body>
</html>