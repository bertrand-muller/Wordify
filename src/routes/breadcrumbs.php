<?php

use DaveJamesMiller\Breadcrumbs\Generator;

Breadcrumbs::register('dashboards.users', function (Generator $breadcrumbs) {
    $breadcrumbs->push(__('views.dashboards.dashboard.title'), route('dashboards.dashboard'));
    $breadcrumbs->push(__('views.dashboards.users.index.title'));
});

Breadcrumbs::register('dashboards.users.show', function (Generator $breadcrumbs, \App\Models\Auth\User\User $user) {
    $breadcrumbs->push(__('views.dashboards.dashboard.title'), route('dashboards.dashboard'));
    $breadcrumbs->push(__('views.dashboards.users.index.title'), route('dashboards.users'));
    $breadcrumbs->push(__('views.dashboards.users.show.title', ['name' => $user->name]));
});


Breadcrumbs::register('dashboards.users.edit', function (Generator $breadcrumbs, \App\Models\Auth\User\User $user) {
    $breadcrumbs->push(__('views.dashboards.dashboard.title'), route('dashboards.dashboard'));
    $breadcrumbs->push(__('views.dashboards.users.index.title'), route('dashboards.users'));
    $breadcrumbs->push(__('views.dashboards.users.edit.title', ['name' => $user->name]));
});


