<aside class="jep-section jep-detention-ranking" aria-labelledby="jep-centers-title">
    <header class="jep-section__header"><span></span><h2 id="jep-centers-title">{{ __('dashboard.jep_page.centers.title') }}</h2></header>
    <div class="jep-detention-ranking__list">
        @foreach ($detentionCenters as $center)
            <article>
                <span class="jep-detention-ranking__number">{{ $loop->iteration }}</span>
                <i class="bi bi-building" aria-hidden="true"></i>
                <div>
                    <div><strong>{{ $center['name'] }}</strong><span>{{ $center['value'] }} {{ __('dashboard.jep_page.centers.prisoners') }}</span></div>
                    <span class="jep-detention-ranking__track"><span style="width: {{ ($center['value'] / 231) * 100 }}%"></span></span>
                </div>
            </article>
        @endforeach
    </div>
    <a href="#" class="jep-detention-ranking__link">{{ __('dashboard.jep_page.centers.view_all') }} <i class="bi bi-arrow-right"></i></a>
</aside>
