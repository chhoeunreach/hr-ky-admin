<div class="card mt-card mb-4">
    <div class="card-body">
        <h5>{{ __('index.quote_playback_settings') }}</h5>
        <p class="text-muted">{{ __('index.quote_playback_hint') }}</p>
        <form method="post" id="quote-playback-form" action="{{ route('admin.mobile-tools.quotes.playback') }}">
            @csrf
            <div class="row g-3 mb-3">
                <div class="col-sm-4">
                    <label class="form-label" for="quote-playback-transition">{{ __('index.transition') }}</label>
                    <select class="form-select" id="quote-playback-transition" name="transition">
                        @foreach(\App\Support\BannerSlideshowTransitions::OPTIONS as $value => $label)
                            <option value="{{ $value }}" @selected(old('transition', $playback['transition']) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-2">
                    <label class="form-label" for="quote-playback-interval">{{ __('index.seconds') }}</label>
                    <input class="form-control" id="quote-playback-interval" name="intervalSeconds" type="number" min="2" max="30"
                        value="{{ old('intervalSeconds', $playback['intervalSeconds']) }}" required>
                </div>
                @foreach(['autoplay' => __('index.autoplay'), 'loop' => __('index.loop'), 'pauseOnTouch' => __('index.pause_on_touch')] as $key => $label)
                    <div class="col-sm-2">
                        <label class="form-label" for="quote-playback-{{ $key }}">{{ $label }}</label>
                        <select class="form-select" id="quote-playback-{{ $key }}" name="{{ $key }}">
                            <option value="1" @selected((bool) old($key, $playback[$key]))>{{ __('index.on') }}</option>
                            <option value="0" @selected(!(bool) old($key, $playback[$key]))>{{ __('index.off') }}</option>
                        </select>
                    </div>
                @endforeach
            </div>
            <div class="qb-banner mb-2" id="banner_canvas_playback">
                <div class="qb-banner-overlay" id="banner_overlay_playback"></div>
                <div class="qb-quote-box" id="quote_box_playback" style="pointer-events:none">
                    <span class="qb-quote-mark" id="quote_mark_playback">“</span>
                    <span class="qb-quote-text" id="preview_text_playback"></span>
                    <div class="qb-quote-author" id="preview_author_playback"></div>
                </div>
            </div>
            <p class="text-muted mb-2" id="quote-playback-position"></p>
            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-sm btn-outline-secondary" type="button" id="quote-playback-prev">{{ __('index.prev_quote') }}</button>
                <button class="btn btn-sm btn-outline-secondary" type="button" id="quote-playback-next">{{ __('index.next_quote') }}</button>
                <button class="btn btn-sm btn-primary" type="submit">{{ __('index.save_playback_settings') }}</button>
            </div>
        </form>
    </div>
</div>
