<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\RefundRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RefundRequestController extends Controller
{
    public function index()
    {
        $refundRequests = Auth::user()->refundRequests()
            ->with('order')
            ->latest()
            ->paginate(10);

        return view('account.refunds.index', compact('refundRequests'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $order = Auth::user()->orders()->findOrFail($request->order_id);

        RefundRequest::create([
            'user_id' => Auth::id(),
            'order_id' => $order->id,
            'amount' => $request->amount,
            'reason' => $request->reason,
            'status' => 'REQUESTED',
        ]);

        return back()->with('success', 'Refund request submitted.');
    }
}
