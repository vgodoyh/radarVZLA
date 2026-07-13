<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Radar Venezuela</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('assets/js/radar-dashboard.js') }}"></script>
<link rel="stylesheet" href="{{ asset('assets/css/radar-dashboard.css') }}">
<body>
  <div class="radar-dashboard">
      <section class="ong-hero">
          <div class="container-fluid px-4 px-xl-5">
              <div class="row g-0">
                  @php
                      $ongs = [
                          ['sigla' => 'AJ', 'nombre' => 'Acceso a la Justicia', 'texto' => 'Información sobre el traslado de causas vinculadas a delitos de terrorismo desde los juzgados competentes hacia los tribunales ordinarios penales.'],
                          ['sigla' => 'OVFN', 'nombre' => 'Observatorio Venezolano de Fake News', 'texto' => 'Detección, análisis y alfabetización digital para incentivar la comunicación para la democracia.'],
                          ['sigla' => 'JEP', 'nombre' => 'Justicia, Encuentro y Perdón', 'texto' => 'Registro, documentación y seguimiento de detenciones arbitrarias y asesinatos por persecución política en Venezuela.'],
                          ['sigla' => 'OBU', 'nombre' => 'Observatorio de Universidades', 'texto' => 'Noticias sobre educación universitaria, protestas, denuncias y derechos de la comunidad universitaria.'],
                      ];
                  @endphp

                  @foreach($ongs as $ong)
                      <div class="col-12 col-md-6 col-xl-3 ong-column">
                          <div class="ong-card-hero">
                              <div class="d-flex align-items-center gap-3 mb-3">
                                  <div class="ong-logo-placeholder">{{ $ong['sigla'] }}</div>
                                  <h2>{{ $ong['nombre'] }}</h2>
                              </div>
                              <p>{{ $ong['texto'] }}</p>
                          </div>
                      </div>
                  @endforeach
              </div>
          </div>
      </section>

      <main class="container-fluid px-4 px-xl-5 py-4">
          <section class="dashboard-section period-section">
              <div>
                  <span class="section-kicker">Cabecera de quincena</span>
                  <div class="period-card mt-2">
                      <div class="period-icon"><i class="bi bi-calendar3"></i></div>
                      <div>
                          <strong>1 – 15 de mayo de 2026</strong>
                          <small>Quincena en curso</small>
                      </div>
                  </div>
              </div>
              <a href="#" class="btn btn-radar">
                  Publicar informe de quincena
                  <i class="bi bi-cloud-arrow-up"></i>
              </a>
          </section>

          <section class="dashboard-section border-top pt-4">
              <div class="section-heading">
                  <h3>Cifras clave</h3>
                  <span>Comparación con la quincena anterior</span>
              </div>

              @php
                  $metrics = [
                      ['icon' => 'bi-people-fill', 'label' => 'Total de presos políticos', 'value' => '1.875', 'change' => '5,2%', 'delta' => '+93', 'tone' => 'blue', 'up' => true],
                      ['icon' => 'bi-diagram-3-fill', 'label' => 'Nuevas detenciones', 'value' => '142', 'change' => '18,3%', 'delta' => '+22', 'tone' => 'purple', 'up' => false],
                      ['icon' => 'bi-person-standing-dress', 'label' => 'Mujeres', 'value' => '234', 'change' => '6,4%', 'delta' => '+14', 'tone' => 'pink', 'up' => true],
                      ['icon' => 'bi-droplet-fill', 'label' => 'Asesinatos', 'value' => '23', 'change' => '21,1%', 'delta' => '+4', 'tone' => 'red', 'up' => false],
                      ['icon' => 'bi-unlock-fill', 'label' => 'Liberaciones', 'value' => '87', 'change' => '12,9%', 'delta' => '+10', 'tone' => 'green', 'up' => true],
                  ];
              @endphp

              <div class="row g-3">
                  @foreach($metrics as $metric)
                      <div class="col-12 col-sm-6 col-xl">
                          <article class="metric-card tone-{{ $metric['tone'] }}">
                              <div class="metric-top">
                                  <i class="bi {{ $metric['icon'] }} metric-icon"></i>
                                  <span>{{ $metric['label'] }}</span>
                              </div>
                              <strong class="metric-value">{{ $metric['value'] }}</strong>
                              <div class="metric-change {{ $metric['up'] ? 'positive' : 'negative' }}">
                                  <i class="bi {{ $metric['up'] ? 'bi-arrow-up' : 'bi-arrow-up' }}"></i>
                                  {{ $metric['change'] }} <span>({{ $metric['delta'] }})</span>
                              </div>
                          </article>
                      </div>
                  @endforeach
              </div>
          </section>

          <section class="dashboard-section featured-panel">
              <div class="section-heading mb-3"><h3>Indicador destacado del mes</h3></div>
              <div class="row g-4 align-items-stretch">
                  <div class="col-12 col-xl-6">
                      <div class="chart-card h-100">
                          <h4>Traslados de causas por terrorismo a tribunales ordinarios</h4>
                          <p>Número de causas trasladadas por quincena</p>
                          <canvas id="featuredChart" height="135"></canvas>
                      </div>
                  </div>
                  <div class="col-12 col-xl-6">
                      <div class="analysis-card h-100">
                          <p>Durante la primera quincena se registraron 78 traslados de causas vinculadas a delitos de terrorismo desde juzgados especializados hacia tribunales ordinarios penales.</p>
                          <p>La cifra representa un aumento respecto al período anterior y plantea alertas sobre transparencia, competencia judicial y debido proceso.</p>
                          <div class="slim-links mt-auto">
                              <a href="#"><i class="bi bi-file-earmark-text"></i> Nota de prensa <i class="bi bi-box-arrow-up-right"></i></a>
                              <a href="#"><i class="bi bi-twitter-x"></i> Hilo en Twitter / X <i class="bi bi-box-arrow-up-right"></i></a>
                              <a href="#"><i class="bi bi-globe2"></i> Ver más en la web <i class="bi bi-box-arrow-up-right"></i></a>
                          </div>
                      </div>
                  </div>
              </div>
          </section>

          <section class="dashboard-section">
              <div class="section-heading"><h3>Grupos de indicadores</h3></div>
              @php
                  $groups = [
                      ['icon'=>'bi-people-fill','title'=>'Perfil sociodemográfico','items'=>['Edad','Género','Grupo social']],
                      ['icon'=>'bi-bank','title'=>'Situación jurídica','items'=>['Delitos imputados','Abusos procesales','Acceso a abogado']],
                      ['icon'=>'bi-heart-pulse-fill','title'=>'Indicadores críticos de salud','items'=>['Condiciones de salud','Atención médica','Enfermedades crónicas']],
                      ['icon'=>'bi-shield-fill','title'=>'Contexto represivo','items'=>['Responsables','Torturas y tratos crueles','Aislamiento']],
                      ['icon'=>'bi-person-hearts','title'=>'Grupos vulnerables y nacionalidad','items'=>['Grupos vulnerables','Nacionalidad','Pueblos indígenas']],
                      ['icon'=>'bi-geo-alt-fill','title'=>'Distribución geográfica','items'=>['Por estado','Centros clandestinos','Traslados de detenidos']],
                      ['icon'=>'bi-graph-up-arrow','title'=>'Evolución temporal','items'=>['Tendencias','Pico histórico','Base de datos acumulada']],
                      ['icon'=>'bi-megaphone-fill','title'=>'Visibilidad e incidencia','items'=>['Cobertura mediática','Alertas a relatores ONU','Comunicados emitidos']],
                  ];
              @endphp

              <div class="row g-3">
                  @foreach($groups as $index => $group)
                      <div class="col-12 col-md-6 col-xl-3">
                          <article class="indicator-card h-100">
                              <i class="bi {{ $group['icon'] }} indicator-icon"></i>
                              <div>
                                  <h4>{{ $index + 1 }}. {{ $group['title'] }}</h4>
                                  <ul>
                                      @foreach($group['items'] as $item)<li>{{ $item }}</li>@endforeach
                                  </ul>
                                  <a href="#">Ver indicadores <i class="bi bi-arrow-right"></i></a>
                              </div>
                          </article>
                      </div>
                  @endforeach
              </div>
          </section>

          <section class="dashboard-section university-panel">
              <div class="section-heading align-items-start">
                  <div>
                      <h3>Observatorio de Universidades</h3>
                      <p>Protestas, denuncias, tipos de denuncia y temas principales vinculados con la educación universitaria en Venezuela.</p>
                  </div>
              </div>

              <ul class="nav radar-tabs" id="universityTabs" role="tablist">
                  <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#protestas">Protestas</button></li>
                  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#denuncias">Denuncias</button></li>
                  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tipos">Tipos de denuncia</button></li>
                  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#temas">Temas</button></li>
              </ul>

              <div class="tab-content pt-3">
                  <div class="tab-pane fade show active" id="protestas">
                      <div class="row g-3">
                          <div class="col-12 col-xl-4"><div class="chart-card"><h4>Histórico de protestas</h4><canvas id="protestsChart"></canvas></div></div>
                          <div class="col-12 col-xl-4"><div class="chart-card"><h4>Histórico de denuncias</h4><canvas id="complaintsChart"></canvas></div></div>
                          <div class="col-12 col-md-6 col-xl-2"><div class="chart-card"><h4>Tipos de denuncia</h4><canvas id="typesChart"></canvas></div></div>
                          <div class="col-12 col-md-6 col-xl-2"><div class="chart-card"><h4>Temas principales</h4><canvas id="topicsChart"></canvas></div></div>
                      </div>
                  </div>
                  <div class="tab-pane fade" id="denuncias"><div class="empty-tab">Contenido detallado de denuncias.</div></div>
                  <div class="tab-pane fade" id="tipos"><div class="empty-tab">Derechos económicos y sociales / Derechos civiles y políticos.</div></div>
                  <div class="tab-pane fade" id="temas"><div class="empty-tab">Infraestructura, providencias estudiantiles y salarios.</div></div>
              </div>
          </section>

          <section class="dashboard-section x-section">
              <div class="section-heading"><h3>Últimos posts en X</h3></div>
              <div class="row g-3">
                  @foreach($ongs as $ong)
                      <div class="col-12 col-md-6 col-xl-3">
                          <article class="x-card h-100">
                              <div class="x-card-head">
                                  <div class="ong-logo-placeholder small">{{ $ong['sigla'] }}</div>
                                  <div><strong>{{ $ong['nombre'] }}</strong><small>@organizacion</small></div>
                                  <i class="bi bi-twitter-x ms-auto"></i>
                              </div>
                              <p>Publicación reciente de la organización con información destacada, cifras y enlaces relacionados con su área de trabajo.</p>
                              <div class="x-card-footer"><span>15 mayo 2026</span><a href="#">Ver en X <i class="bi bi-box-arrow-up-right"></i></a></div>
                          </article>
                      </div>
                  @endforeach
              </div>
          </section>
      </main>
  </div>
</body>


    

