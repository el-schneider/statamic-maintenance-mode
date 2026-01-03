@extends('statamic::layout')
@section('title', $title)

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="flex-1">{{ $title }}</h1>
</div>

<div class="card p-4 mb-16">
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
</script>
@endsection
