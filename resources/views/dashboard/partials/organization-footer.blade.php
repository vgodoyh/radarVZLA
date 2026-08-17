@php
    $footerLinks = $footerLinks ?? [];
    $footerWebsite = data_get($footerLinks, 'website') ?? data_get($footerOrganization, 'website_url') ?? data_get($footerOrganization, 'url');
    $footerContact = data_get($footerLinks, 'contact') ?? data_get($footerOrganization, 'contact_url');
    $footerInfo = data_get($footerLinks, 'info') ?? data_get($footerOrganization, 'info_url');
    $footerUsername = data_get($footerOrganization, 'username') ?? data_get($footerOrganization, 'x_username');
    $footerLogo = data_get($footerOrganization, 'logo_x') ?? data_get($footerOrganization, 'logo');
    $footerContactIsExternal = filled($footerContact) && str_starts_with($footerContact, 'http');

    $footerSocials = collect([
        ['key' => 'facebook', 'label' => 'Facebook', 'icon' => 'fa-brands fa-facebook-f', 'url' => data_get($footerLinks, 'facebook') ?? data_get($footerOrganization, 'social_urls.facebook') ?? data_get($footerOrganization, 'facebook_url')],
        ['key' => 'x', 'label' => 'X / Twitter', 'icon' => 'fa-brands fa-x-twitter', 'url' => data_get($footerLinks, 'x') ?? data_get($footerLinks, 'twitter') ?? data_get($footerOrganization, 'social_urls.x') ?? data_get($footerOrganization, 'x_url') ?? (filled($footerUsername) ? 'https://x.com/'.$footerUsername : null)],
        ['key' => 'instagram', 'label' => 'Instagram', 'icon' => 'fa-brands fa-instagram', 'url' => data_get($footerLinks, 'instagram') ?? data_get($footerOrganization, 'social_urls.instagram') ?? data_get($footerOrganization, 'instagram_url')],
        ['key' => 'youtube', 'label' => 'YouTube', 'icon' => 'fa-brands fa-youtube', 'url' => data_get($footerLinks, 'youtube') ?? data_get($footerOrganization, 'social_urls.youtube') ?? data_get($footerOrganization, 'youtube_url')],
        ['key' => 'tiktok', 'label' => 'TikTok', 'icon' => 'fa-brands fa-tiktok', 'url' => data_get($footerLinks, 'tiktok') ?? data_get($footerOrganization, 'social_urls.tiktok') ?? data_get($footerOrganization, 'tiktok_url')],
        ['key' => 'telegram', 'label' => 'Telegram', 'icon' => 'fa-brands fa-telegram', 'url' => data_get($footerLinks, 'telegram') ?? data_get($footerOrganization, 'social_urls.telegram') ?? data_get($footerOrganization, 'telegram_url')],
    ])->filter(fn (array $social): bool => filled($social['url']));
@endphp

<footer class="organization-footer" style="--organization-footer-accent: {{ $footerAccent }};">
    <div class="organization-footer__container">
        <div class="organization-footer__main">
            <section class="organization-footer__identity">
                <span class="organization-footer__logo">
                    @if (filled($footerLogo))
                        <img src="{{ $footerLogo }}" alt="{{ data_get($footerOrganization, 'name') }}">
                    @endif
                </span>
                <div>
                    <strong>{{ data_get($footerOrganization, 'name') }}</strong>
                    <small>{{ $footerCategory }}</small>
                </div>
            </section>

            @if (filled($footerWebsite) || filled($footerContact) || filled($footerInfo))
                <nav class="organization-footer__access" aria-label="{{ __('dashboard.organization_footer.main_links') }}">
                    @if (filled($footerWebsite))
                        <a href="{{ $footerWebsite }}" target="_blank" rel="noopener noreferrer" aria-label="{{ __('dashboard.organization_footer.website') }}">
                            <i class="bi bi-globe2" aria-hidden="true"></i>
                            <span>{{ __('dashboard.organization_footer.website') }}</span>
                        </a>
                    @endif

                    @if (filled($footerContact))
                        <a
                            href="{{ $footerContact }}"
                            @if ($footerContactIsExternal) target="_blank" rel="noopener noreferrer" @endif
                            aria-label="{{ __('dashboard.organization_footer.contact') }}"
                        >
                            <i class="bi bi-envelope" aria-hidden="true"></i>
                            <span>{{ __('dashboard.organization_footer.contact') }}</span>
                        </a>
                    @endif

                    @if (filled($footerInfo))
                        <a href="{{ $footerInfo }}" target="_blank" rel="noopener noreferrer" aria-label="{{ __('dashboard.organization_footer.information') }}">
                            <i class="bi bi-info-circle" aria-hidden="true"></i>
                            <span>{{ __('dashboard.organization_footer.information') }}</span>
                        </a>
                    @endif
                </nav>
            @endif

            @if ($footerSocials->isNotEmpty())
                <section class="organization-footer__socials" aria-label="{{ __('dashboard.social_media') }}">
                    @foreach ($footerSocials as $social)
                        <a
                            href="{{ $social['url'] }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label="{{ $social['label'].' - '.data_get($footerOrganization, 'name') }}"
                            title="{{ $social['label'] }}"
                        >
                            <i class="{{ $social['icon'] }}" aria-hidden="true"></i>
                        </a>
                    @endforeach
                </section>
            @endif
        </div>

        <div class="organization-footer__bottom">
            <p>&copy; {{ date('Y') }} Pulso Venezuela</p>
        </div>
    </div>
</footer>
