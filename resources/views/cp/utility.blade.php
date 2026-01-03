@extends('statamic::layout')
@section('title', $title)

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="flex-1">{{ $title }}</h1>
</div>

<div class="card p-4 mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="font-bold text-lg">{{ __('Status') }}</h2>
            <p class="text-sm text-gray-600 dark:text-dark-150">
                @if($isActive)
                    {{ __('Maintenance mode is currently') }} <span class="text-red-500 font-bold">{{ __('active') }}</span>.
                @else
                    {{ __('Maintenance mode is currently') }} <span class="text-green-500 font-bold">{{ __('inactive') }}</span>.
                @endif
            </p>
        </div>
        <div>
            @if($isActive)
                <button
                    class="btn-primary"
                    onclick="deactivateMaintenance()"
                >
                    {{ __('Deactivate') }}
                </button>
            @else
                <button
                    class="btn-danger"
                    onclick="activateMaintenance()"
                >
                    {{ __('Activate') }}
                </button>
            @endif
        </div>
    </div>
</div>

@if($isActive && $secretUrl)
<div class="card p-4 mb-6 bg-amber-50 dark:bg-amber-950 border-amber-200 dark:border-amber-800">
    <h2 class="font-bold text-lg mb-2">{{ __('Bypass URL') }}</h2>
    <p class="text-sm text-gray-600 dark:text-dark-150 mb-3">
        {{ __('Share this URL to grant temporary access during maintenance. Visitors who open this link will receive a cookie that bypasses maintenance mode.') }}
    </p>
    <div class="flex items-center gap-2">
        <input
            type="text"
            readonly
            value="{{ $secretUrl }}"
            class="input-text flex-1 font-mono text-sm bg-white dark:bg-dark-700"
            id="secret-url-input"
        >
        <button
            type="button"
            class="btn"
            onclick="copySecretUrl()"
        >
            {{ __('Copy') }}
        </button>
    </div>
</div>
@endif

@if($hasCollections)
<publish-form
    :title="__('Configuration')"
    action="{{ cp_route('utilities.maintenance-mode.store') }}"
    :blueprint='@json($blueprint)'
    :meta='@json($meta)'
    :values='@json($values)'
></publish-form>
@else
<div class="card p-4">
    <p class="text-gray-600 dark:text-dark-150">
        {{ __('No collections available. Create a collection to select a maintenance page or whitelisted pages.') }}
    </p>
</div>
@endif

<script>
    function activateMaintenance() {
        if (!confirm(@json(__('Are you sure you want to activate maintenance mode? Visitors will see the maintenance page.')))) {
            return;
        }

        fetch('{{ cp_route('utilities.maintenance-mode.activate') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': Statamic.$config.get('csrfToken')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            }
        });
    }

    function deactivateMaintenance() {
        fetch('{{ cp_route('utilities.maintenance-mode.deactivate') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': Statamic.$config.get('csrfToken')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            }
        });
    }

    function copySecretUrl() {
        const input = document.getElementById('secret-url-input');

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(input.value).then(() => {
                Statamic.$toast.success(@json(__('Copied to clipboard')));
            });
        } else {
            input.select();
            document.execCommand('copy');
            Statamic.$toast.success(@json(__('Copied to clipboard')));
        }
    }
</script>
@endsection
