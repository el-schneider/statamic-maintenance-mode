@extends('statamic::layout')
@section('title', $title)

@section('content')
<ui-header title="{{ $title }}">
    @if($isActive)
        <ui-button style="background: #16a34a; border-color: #15803d; color: white;" onclick="window.deactivateMaintenance()">
            {{ __('Deactivate') }}
        </ui-button>
    @else
        <ui-button variant="danger" onclick="window.activateMaintenance()">
            {{ __('Activate') }}
        </ui-button>
    @endif
</ui-header>

<div class="space-y-6">
    <ui-panel>
        <ui-panel-header>
            <ui-heading text="{{ __('Status') }}" />
        </ui-panel-header>
        <ui-card>
            <ui-description>
                @if($isActive)
                    {{ __('Maintenance mode is currently') }} <ui-badge color="red">{{ __('active') }}</ui-badge>.
                @else
                    {{ __('Maintenance mode is currently') }} <ui-badge color="green">{{ __('inactive') }}</ui-badge>.
                @endif
            </ui-description>
        </ui-card>
    </ui-panel>

    @if($isActive && $secretUrl)
    <ui-panel>
        <ui-panel-header>
            <ui-heading text="{{ __('Bypass URL') }}" />
        </ui-panel-header>
        <ui-card class="bg-amber-50 dark:bg-amber-950/30">
            <ui-description class="mb-3">
                {{ __('Share this URL to grant temporary access during maintenance. Visitors who open this link will receive a cookie that bypasses maintenance mode.') }}
            </ui-description>
            <div class="flex items-center gap-2">
                <input
                    type="text"
                    readonly
                    value="{{ $secretUrl }}"
                    id="secret-url-input"
                    class="flex-1 font-mono text-sm px-3 h-10 rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700"
                >
                <ui-button onclick="window.copySecretUrl()">
                    {{ __('Copy') }}
                </ui-button>
            </div>
        </ui-card>
    </ui-panel>
    @endif

    @if($hasCollections)
    <ui-publish-form
        title="{{ __('Configuration') }}"
        submit-url="{{ cp_route('utilities.maintenance-mode.store') }}"
        :blueprint='@json($blueprint)'
        :initial-meta='@json($meta)'
        :initial-values='@json($values)'
    />
    @else
    <ui-card>
        <ui-description>
            {{ __('No collections available. Create a collection to select a maintenance page or whitelisted pages.') }}
        </ui-description>
    </ui-card>
    @endif
</div>
@endsection

@section('scripts')
<script>
    window.activateMaintenance = function() {
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
    };

    window.deactivateMaintenance = function() {
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
    };

    window.copySecretUrl = function() {
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
    };
</script>
@endsection
