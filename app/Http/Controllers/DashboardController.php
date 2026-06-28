<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $polls = Poll::query()
            ->withCount('voters')
            ->where('creator_user_id', Auth::id())
            ->latest()
            ->get();

        return view('dashboard', [
            'polls' => $polls,
        ]);
    }
}
