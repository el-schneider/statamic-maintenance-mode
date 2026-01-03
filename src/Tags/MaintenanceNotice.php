<?php

declare(strict_types=1);

namespace ElSchneider\StatamicMaintenanceMode\Tags;

use Statamic\Tags\Tags;

class MaintenanceNotice extends Tags
{
    protected static bool $scriptInjected = false;

    public function index(): string
    {
        if (! config('statamic.maintenance-mode.show_frontend_notice', true)) {
            return '';
        }

        $content = $this->isPair
            ? $this->parseContent()
            : $this->defaultBadge();

        $html = sprintf(
            '<div data-maintenance-notice style="display:none">%s</div>',
            $content
        );

        if (! self::$scriptInjected) {
            $html .= $this->script();
            self::$scriptInjected = true;
        }

        return $html;
    }

    protected function parseContent(): string
    {
        $parsed = $this->parse();

        if (is_string($parsed)) {
            return $parsed;
        }

        return $this->content ?? '';
    }

    protected function defaultBadge(): string
    {
        $label = __('Maintenance Mode Active');

        return <<<HTML
        <div style="position:fixed;bottom:16px;right:16px;background:#f59e0b;color:#000;padding:8px 16px;border-radius:6px;font-family:system-ui,-apple-system,sans-serif;font-size:14px;font-weight:500;z-index:9999;box-shadow:0 4px 12px rgba(0,0,0,0.15);">
            {$label}
        </div>
        HTML;
    }

    protected function script(): string
    {
        $statusUrl = $this->statusUrl();

        return <<<HTML
        <script>
        (function(){
            var ric = window.requestIdleCallback || function(c){setTimeout(c,100)};
            ric(function(){
                fetch('{$statusUrl}', {credentials:'include'})
                    .then(function(r){return r.json()})
                    .then(function(d){
                        if(d.show){
                            var els = document.querySelectorAll('[data-maintenance-notice]');
                            for(var i=0;i<els.length;i++){els[i].style.display='';}
                        }
                    })
                    .catch(function(){});
            }, {timeout:2000});
        })();
        </script>
        HTML;
    }

    protected function statusUrl(): string
    {
        $actionPrefix = config('statamic.routes.action', '!/');

        return '/'.mb_trim($actionPrefix, '/').'/statamic-maintenance-mode/status';
    }
}
