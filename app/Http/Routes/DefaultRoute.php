<?php

namespace App\Http\Routes;

use Dentro\Yalr\BaseRoute;

class DefaultRoute extends BaseRoute
{
    protected string $prefix = '';

    protected string $name = '';
    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register(): void
    {
        $this->router->get('/', function () {
            return view('welcome');
        });
    }

    public function afterRegister(): void
    {
        menus()
            ->setGroup(
                name: 'app_main',
                group: 'app_main',
                icon: 'ki-outline ki-home-2 fs-2',
                menus: function ($menu) {
                    return $this->headerTab($menu);
                }
            )->setGroup(
                name: 'sidebar_tab',
                group: 'sidebar_tab',
                icon: 'ki-outline ki-home-2 fs-2',
                menus: function ($menu) {
                    return $this->sidebarTab($menu);
                }
            )->setGroup(
                name: 'profiles_tab',
                group: 'profiles_tab',
                menus: function ($menu) {
                    return $this->menuProfile($menu);
                }
            );
    }

    protected function headerTab($menu)
    {
        return $menu
            ->addMenu(
                title: __('Dashboard'),
                routeName: 'app.dashboard.index',
                activeRouteName: 'app.dashboard.index',
            )->addMenu(
                title: __('Signals'),
                routeName: 'app.dashboard.index',
                activeRouteName: 'app.dashboard.index',
            )->addMenu(
                title: 'Trading',
                routeName: 'app.dashboard.index',
                activeRouteName: 'app.dashboard.index',
            );
    }

    protected function sidebarTab($menu)
    {

//        dd($menu->getMenu());
        return $menu
           /* ->addMenu(
                title: __('Account'),
                icon: 'ki-profile-user',
                routeName: 'app.me.account',
                activeRouteName: 'app.me.account',
            )*/->addMenu(
                title: __('Market Pairs'),
                icon: 'ki-book-open',
                routeName: 'app.dashboard.index',
                activeRouteName: 'app.dashboard.index',
            )/*->addMenu(
                title: __('Trading'),
                icon: 'ki-chart-line-star',
                routeName: 'app.trading.index',
                activeRouteName: 'app.trading.index',
            )*/->addMenu(
                title: __('Histories'),
                icon: 'ki-graph-up',
                routeName: 'app.dashboard.index',
                activeRouteName: 'app.dashboard.index',
            );
    }

    protected function menuProfile($menu)
    {
        return $menu
            ->addMenu(
                title: __('Overview'),
                routeName: 'app.me.overview',
                activeRouteName: 'app.me.overview',
            )/*->addMenu(
                title: __('Account'),
                routeName: 'app.me.account',
                activeRouteName: 'app.me.account*',
            )->addMenu(
                title: __('Token'),
                routeName: 'app.me.token',
                activeRouteName: 'app.me.token*',
            )*/->addMenu(
                title: __('Billing'),
                routeName: 'app.me.billing',
                activeRouteName: 'app.me.billing*',
            )->addMenu(
                title: __('Security'),
                routeName: 'app.me.security',
                activeRouteName: 'app.me.security*',
            )->addMenu(
                title: __('Setting'),
                routeName: 'app.me.setting',
                activeRouteName: 'app.me.setting*',
            );
    }
}
