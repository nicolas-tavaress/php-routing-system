<?php

namespace app\enums;

use app\middlewares\Admin;
use app\middlewares\Auth;

enum RouteMiddlewares: string {
    case auth = Auth::class;
    case admin = Admin::class;
}