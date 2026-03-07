<?php

declare(strict_types=1);

namespace App\Http\Controllers\Feedback;

use App\Http\Controllers\Controller;
use App\Http\Requests\Feedback\StoreDeletionFeedbackRequest;
use App\Models\AccountDeletionFeedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class DeletionFeedbackController extends Controller
{
    public function create(Request $request): Response
    {
        return Inertia::render('feedback/account-deletion', [
            'email' => $request->query('email'),
        ]);
    }

    public function store(StoreDeletionFeedbackRequest $request): RedirectResponse
    {
        AccountDeletionFeedback::create($request->validated());

        Inertia::flash('success', 'Thank you for your feedback.');

        return redirect()->route('login');
    }
}
