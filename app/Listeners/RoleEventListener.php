<?php

namespace App\Listeners;

use App\Enums\CacheName;
use App\Services\CacheService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Events\RoleAttached;
use Spatie\Permission\Events\RoleDetached;

class RoleEventListener
{
    /**
     * Handle the RoleAttached event.
     *
     * @param RoleAttached $event
     * @return void
     */
    public function handleRoleAttached(RoleAttached $event): void
    {
        $forgotten = (new CacheService(CacheName::IS_SUPER_ADMIN))->append($event->model->id)->forget();

        if (!$forgotten) {
            Log::warning('Failed to clear is super admin status on cache', ['user_id' => $event->model->id]);
        }
    }

    /**
     * Handle the RoleDetached event.
     *
     * @param RoleDetached $event
     * @return void
     */
    public function handleRoleDetached(RoleDetached $event): void
    {
        $forgotten = (new CacheService(CacheName::IS_SUPER_ADMIN))->append($event->model->id)->forget();

        if (!$forgotten) {
            Log::warning('Failed to clear is super admin status on cache', ['user_id' => $event->model->id]);
        }
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     * @return void
     */
    public function subscribe($events): void
    {
        $events->listen(
            RoleAttached::class,
            [RoleEventListener::class, 'handleRoleAttached']
        );

        $events->listen(
            RoleDetached::class,
            [RoleEventListener::class, 'handleRoleDetached']
        );
    }
}
