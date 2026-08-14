@php
    $setting = $settings[$key] ?? null;
    $url = $setting && $setting->value ? asset('storage/' . $setting->value) : null;
@endphp

<div class="form-group">
    <label>{{ $label }}</label>
    @if ($url)
        <div style="margin-bottom:0.5rem;">
            <img src="{{ $url }}" alt="" style="max-width:140px;max-height:80px;border-radius:8px;border:1px solid var(--border);display:block;margin-bottom:0.4rem;">
            <div class="form-check">
                <input type="checkbox" id="remove_{{ $key }}" name="remove_{{ $key }}" value="1">
                <label for="remove_{{ $key }}">Remove current image</label>
            </div>
        </div>
    @endif
    <input type="file" name="{{ $key }}" accept="image/*">
</div>