<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Interfaces\QueryBusInterface;
use App\Queries\GetOrdersQuery;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
    ) {
        //
    }

    public function index(Request $request): Response
    {
        return Inertia::render('account/orders', [
            'orders' => $this->queryBus->ask(new GetOrdersQuery($request->user()->id)),
        ]);
    }
}
