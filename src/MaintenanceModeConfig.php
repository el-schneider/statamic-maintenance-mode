<?php

declare(strict_types=1);

namespace ElSchneider\StatamicMaintenanceMode;

use Illuminate\Support\Facades\File;
use Statamic\Entries\Entry;
use Statamic\Facades\Entry as EntryFacade;
use Statamic\Facades\YAML;
use Throwable;

class MaintenanceModeConfig
{
    protected array $data = [];

    protected bool $loaded = false;

    public function __construct()
    {
        $this->load();
    }

    public function save(): void
    {
        $directory = dirname($this->path());

        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        File::put($this->path(), YAML::dump($this->data));
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return data_get($this->data, $key, $default);
    }

    public function set(string $key, mixed $value): static
    {
        data_set($this->data, $key, $value);

        return $this;
    }

    public function all(): array
    {
        return $this->data;
    }

    public function maintenanceEntryId(): ?string
    {
        return $this->get('maintenance_entry');
    }

    public function maintenanceEntry(): ?Entry
    {
        $id = $this->maintenanceEntryId();

        if (! $id) {
            return null;
        }

        return EntryFacade::find($id);
    }

    public function whitelistEntryIds(): array
    {
        return $this->get('whitelist_entries', []);
    }

    public function whitelistEntries(): array
    {
        $ids = $this->whitelistEntryIds();

        if (empty($ids)) {
            return [];
        }

        return EntryFacade::query()
            ->whereIn('id', $ids)
            ->get()
            ->all();
    }

    public function whitelistUris(): array
    {
        return collect($this->whitelistEntries())
            ->map(fn (Entry $entry) => $entry->uri())
            ->filter()
            ->values()
            ->all();
    }

    public function setMaintenanceEntry(?string $entryId): static
    {
        return $this->set('maintenance_entry', $entryId);
    }

    public function setWhitelistEntries(array $entryIds): static
    {
        return $this->set('whitelist_entries', $entryIds);
    }

    protected function path(): string
    {
        return base_path('content/maintenance-mode.yaml');
    }

    protected function load(): void
    {
        if ($this->loaded) {
            return;
        }

        if (File::exists($this->path())) {
            try {
                $parsed = YAML::file($this->path())->parse();
                $this->data = is_array($parsed) ? $parsed : [];
            } catch (Throwable) {
                $this->data = [];
            }
        }

        $this->loaded = true;
    }
}
