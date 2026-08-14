@extends('layouts.admin.app')

@section('title', 'Settings')
@section('page-title', 'Settings')
@section('page-subtitle', 'Configure hotel-wide settings used across the system and frontend')

@section('content')
<form id="settingsForm" enctype="multipart/form-data">
    @csrf

    {{-- General --}}
    <div class="table-wrap" style="margin-bottom:1.5rem;">
        <div class="table-toolbar"><div class="table-toolbar-title">General</div></div>
        <div class="modal-body" style="max-height:none;padding:1.4rem;">
            <div class="form-row">
                <div class="form-group">
                    <label>Application Name</label>
                    <input type="text" name="app_name" value="{{ $settings['app_name']->value ?? '' }}" required>
                </div>
                <div class="form-group">
                    <label>Contact Email</label>
                    <input type="email" name="contact_email" value="{{ $settings['contact_email']->value ?? '' }}" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Contact Phone</label>
                    <input type="text" name="contact_phone" value="{{ $settings['contact_phone']->value ?? '' }}" required>
                </div>
                <div></div>
            </div>
            <div class="form-row">
                @include('admin.settings.partials.image-field', ['key' => 'app_logo', 'label' => 'Application Logo'])
                @include('admin.settings.partials.image-field', ['key' => 'app_favicon', 'label' => 'Favicon'])
            </div>
        </div>
    </div>

    {{-- Branding --}}
    <div class="table-wrap" style="margin-bottom:1.5rem;">
        <div class="table-toolbar"><div class="table-toolbar-title">Branding</div></div>
        <div class="modal-body" style="max-height:none;padding:1.4rem;">
            <div class="form-row">
                @include('admin.settings.partials.image-field', ['key' => 'hero_banner_image', 'label' => 'Hero Banner Image'])
                <div class="form-group">
                    <label>Hero Tagline</label>
                    <input type="text" name="hero_tagline" value="{{ $settings['hero_tagline']->value ?? '' }}">
                </div>
            </div>
        </div>
    </div>

    {{-- Financial --}}
    <div class="table-wrap" style="margin-bottom:1.5rem;">
        <div class="table-toolbar"><div class="table-toolbar-title">Financial</div></div>
        <div class="modal-body" style="max-height:none;padding:1.4rem;">
            <div class="form-row">
                <div class="form-group">
                    <label>Currency</label>
                    <input type="text" name="currency" value="{{ $settings['currency']->value ?? '' }}" required>
                </div>
                <div class="form-group">
                    <label>VAT (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="vat_percentage" value="{{ $settings['vat_percentage']->value ?? '' }}" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Service Charge (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="service_charge_percentage" value="{{ $settings['service_charge_percentage']->value ?? '' }}" required>
                </div>
                <div class="form-group">
                    <label>Default Deposit (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="deposit_percentage" value="{{ $settings['deposit_percentage']->value ?? '' }}" required>
                </div>
            </div>
        </div>
    </div>

    {{-- Booking Policy --}}
    <div class="table-wrap" style="margin-bottom:1.5rem;">
        <div class="table-toolbar"><div class="table-toolbar-title">Booking Policy</div></div>
        <div class="modal-body" style="max-height:none;padding:1.4rem;">
            <div class="form-row">
                <div class="form-group">
                    <label>Free Cancellation Window (hours)</label>
                    <input type="number" min="0" name="free_cancellation_hours" value="{{ $settings['free_cancellation_hours']->value ?? '' }}" required>
                </div>
                <div class="form-group">
                    <label>Partial Refund (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="partial_refund_percentage" value="{{ $settings['partial_refund_percentage']->value ?? '' }}" required>
                </div>
            </div>
        </div>
    </div>

    {{-- Payment (display-only) --}}
    <div class="table-wrap" style="margin-bottom:1.5rem;">
        <div class="table-toolbar"><div class="table-toolbar-title">Payment Gateway (SSLCommerz)</div></div>
        <div class="modal-body" style="max-height:none;padding:1.4rem;">
            <p class="hint" style="margin-bottom:0.8rem;">Credentials are configured in the server's <code>.env</code> file, not editable here.</p>
            <span class="badge {{ config('services.sslcommerz.sandbox', true) ? 'badge-gold' : 'badge-green' }}">
                {{ config('services.sslcommerz.sandbox', true) ? 'Sandbox Mode' : 'Live Mode' }}
            </span>
        </div>
    </div>

    <div style="display:flex; justify-content:flex-end;">
        <button type="button" class="btn btn-primary" id="saveSettingsBtn" onclick="SettingsPage.submit()">Save Settings</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
const SettingsPage = (() => {
    async function submit() {
        const form = document.getElementById('settingsForm');
        const btn = document.getElementById('saveSettingsBtn');
        const formData = new FormData(form);

        Modal.setSubmitting(btn, true, 'Saving...');
        try {
            const res = await fetch('{{ route('admin.settings.update') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: formData,
            });
            const json = await res.json();
            if (!res.ok) throw new Error(json.message || Object.values(json.errors || {})[0]?.[0] || 'Something went wrong.');
            Toast.show(json.message, 'success');
            setTimeout(() => window.location.reload(), 600);
        } catch (err) {
            Toast.show(err.message, 'error');
        } finally {
            Modal.setSubmitting(btn, false, 'Save Settings');
        }
    }

    return { submit };
})();
</script>
@endpush