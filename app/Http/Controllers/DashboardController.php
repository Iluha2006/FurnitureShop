<?php

namespace App\Http\Controllers;

use App\Data\OrderData;
use App\Interfaces\QueryBusInterface;
use App\Models\Favorite;
use App\Models\TeamInvitation;
use App\Queries\GetCartQuery;
use App\Queries\GetOrdersQuery;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
    ) {
        //
    }

    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $email = strtolower($user->email);

        $pendingInvitations = TeamInvitation::query()
            ->with(['inviter', 'team'])
            ->whereRaw('LOWER(email) = ?', [$email])
            ->whereNull('accepted_at')
            ->where(fn ($query) => $query
                ->whereNull('expires_at')
                ->orWhere('expires_at', '>=', now()))
            ->latest()
            ->get()
            ->map(fn (TeamInvitation $invitation) => [
                'code' => $invitation->code,
                'inviterName' => $invitation->inviter->name,
                'team' => [
                    'name' => $invitation->team->name,
                    'slug' => $invitation->team->slug,
                ],
            ]);

        /** @var array<int, OrderData> $orders */
        $orders = $this->queryBus->ask(new GetOrdersQuery($user->id));

        return Inertia::render('dashboard', [
            'pendingInvitations' => $pendingInvitations,
            'orders' => array_slice($orders, 0, 3),
            'ordersCount' => count($orders),
            'favoritesCount' => Favorite::query()
                ->where('user_id', $user->id)
                ->count(),
            'cart' => $this->queryBus->ask(new GetCartQuery($user->id)),
        ]);
    }
}
