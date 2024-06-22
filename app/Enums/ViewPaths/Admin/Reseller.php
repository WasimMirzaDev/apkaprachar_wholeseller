<?php

namespace App\Enums\ViewPaths\Admin;

enum Reseller
{
    const LIST = [
        URI => 'list',
        VIEW => 'admin-views.reseller.list'
    ];

    const VIEW = [
        URI => 'view',
        VIEW => 'admin-views.reseller.view'
    ];

    const UPDATE = [
        URI => 'status-update',
        VIEW => 'admin-views.category.category-edit'
    ];

    const DELETE = [
        URI => 'delete/{id}',
        VIEW => ''
    ];

}