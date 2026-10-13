<?php

namespace App\Http\Middleware;

use App\Interfaces\QueryBusInterface;
use App\Models\User;
use App\Queries\GetCartQuantitiesQuery;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
    ) {
        //
    }

    /**
     * Cart contents resolved once per request and shared by both cart props.
     *
     * @var array<string, int>|null
     */
    private ?array $cartQuantities = null;

    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'cartQuantities' => fn (): array => $this->cartQuantities($user),
            'cartCount' => fn (): int => array_sum($this->cartQuantities($user)),
            'currentTeam' => fn () => $user?->currentTeam ? $user->toUserTeam($user->currentTeam) : null,
            'teams' => fn () => $user?->toUserTeams(includeCurrent: true) ?? [],
        ];
    }

    /**
     * Map of furniture id to its quantity in the visitor's cart.
     *
     * Guests and empty carts never hit the database.
     *
     * @return array<string, int>
     */
    private function cartQuantities(?User $user): array
    {
        if ($this->cartQuantities !== null) {
            return $this->cartQuantities;
        }

        return $this->cartQuantities = $user === null
            ? []
            : $this->queryBus->ask(new GetCartQuantitiesQuery($user->id));
    }
}
