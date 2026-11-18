<div class="mt-preview" aria-label="Mobile Home banner preview">
    <img src="{{ asset('assets/images/angkor_wat_banner.png') }}"
         data-fallback="{{ asset('assets/images/angkor_wat_banner.png') }}"
         onerror="if (this.getAttribute('src') !== this.dataset.fallback) this.src = this.dataset.fallback;" alt="">
    <div class="mt-preview-copy">
        <span class="mt-preview-title" hidden></span>
        <strong style="font-size:16px">សូមស្វាគមន៍!</strong>
        <span>Good Morning!</span>
        <span class="mt-preview-clock">08:30<small>:15</small></span>
        <span style="font-size:10px">{{ now()->format('l, F j, Y') }}</span>
    </div>
    <div class="mt-preview-dots" hidden aria-hidden="true"></div>
</div>
